import os
import duckdb
from pathlib import Path
from django.conf import settings

BASE_DIR = Path(settings.BASE_DIR)
DUCKDB_PATH = BASE_DIR / 'indopos.duckdb'
SEMBAKO_CSV = BASE_DIR / 'data_sembako.csv'
SEMBAKO_LENGKAP_CSV = BASE_DIR / 'data_sembako_lengkap.csv'


class DuckDBService:
    _instance = None

    @classmethod
    def get_connection(cls, read_only=False):
        """Returns a DuckDB connection to the local database file."""
        return duckdb.connect(str(DUCKDB_PATH), read_only=read_only)

    @classmethod
    def initialize_db(cls):
        """Initialize DuckDB tables, indexes, and load CSV sembako datasets."""
        con = cls.get_connection()
        try:
            # 1. Initialize Sembako Catalog table from CSV if exists
            if SEMBAKO_CSV.exists():
                con.execute("""
                    CREATE TABLE IF NOT EXISTS sembako_catalog (
                        barcode VARCHAR,
                        name VARCHAR
                    )
                """)
                count = con.execute("SELECT count(*) FROM sembako_catalog").fetchone()[0]
                if count == 0:
                    csv_path_str = str(SEMBAKO_CSV).replace('\\', '/')
                    con.execute(f"""
                        INSERT INTO sembako_catalog
                        SELECT column0 AS barcode, column1 AS name
                        FROM read_csv('{csv_path_str}', header=False, names=['column0', 'column1'])
                    """)
                    con.execute("CREATE INDEX IF NOT EXISTS idx_sembako_barcode ON sembako_catalog(barcode)")

            # 2. Initialize Sembako Lengkap table if exists
            if SEMBAKO_LENGKAP_CSV.exists():
                con.execute("""
                    CREATE TABLE IF NOT EXISTS sembako_lengkap (
                        barcode VARCHAR,
                        name VARCHAR,
                        category VARCHAR
                    )
                """)
                count = con.execute("SELECT count(*) FROM sembako_lengkap").fetchone()[0]
                if count == 0:
                    csv_path_str = str(SEMBAKO_LENGKAP_CSV).replace('\\', '/')
                    con.execute(f"""
                        INSERT INTO sembako_lengkap
                        SELECT barcode, name, category
                        FROM read_csv('{csv_path_str}', header=True)
                    """)
                    con.execute("CREATE INDEX IF NOT EXISTS idx_sembako_lengkap_barcode ON sembako_lengkap(barcode)")

            # 3. Create DuckDB view/table for quick analytical snapshots
            con.execute("""
                CREATE TABLE IF NOT EXISTS analytics_events (
                    id BIGINT,
                    transaction_number VARCHAR,
                    branch_id BIGINT,
                    grand_total DOUBLE,
                    net_profit DOUBLE,
                    payment_method VARCHAR,
                    created_at TIMESTAMP
                )
            """)

        finally:
            con.close()

    @classmethod
    def search_barcode_catalog(cls, barcode_str):
        """Fast lookup in DuckDB sembako tables (<1ms)."""
        barcode_clean = str(barcode_str).strip()
        if not barcode_clean:
            return None

        con = cls.get_connection(read_only=True)
        try:
            # Check sembako lengkap first for category info
            res = con.execute("""
                SELECT barcode, name, category 
                FROM sembako_lengkap 
                WHERE barcode = ? 
                LIMIT 1
            """, [barcode_clean]).fetchone()

            if res:
                return {'barcode': res[0], 'name': res[1], 'category': res[2], 'source': 'DuckDB Sembako Lengkap'}

            # Fallback to sembako catalog
            res = con.execute("""
                SELECT barcode, name 
                FROM sembako_catalog 
                WHERE barcode = ? 
                LIMIT 1
            """, [barcode_clean]).fetchone()

            if res:
                return {'barcode': res[0], 'name': res[1], 'category': 'Kebutuhan Pokok / Sembako', 'source': 'DuckDB Sembako Catalog'}

            return None
        except Exception as e:
            return None
        finally:
            con.close()

    @classmethod
    def get_analytics_summary(cls, branch_id=None):
        """Ultra-fast OLAP summary of sales, revenue, profit, and trends."""
        from pos_app.models import Transaction, TransactionItem, Product
        from django.db.models import Sum, Count, Avg
        from django.utils import timezone
        import datetime

        today = timezone.localdate()
        today_start = timezone.make_aware(datetime.datetime.combine(today, datetime.time.min))
        today_end = timezone.make_aware(datetime.datetime.combine(today, datetime.time.max))

        tx_qs = Transaction.objects.filter(created_at__range=(today_start, today_end))
        if branch_id:
            tx_qs = tx_qs.filter(branch_id=branch_id)

        today_metrics = tx_qs.aggregate(
            total_sales=Sum('grand_total'),
            total_profit=Sum('net_profit'),
            total_count=Count('id'),
            avg_ticket=Avg('grand_total')
        )

        all_time_qs = Transaction.objects.all()
        if branch_id:
            all_time_qs = all_time_qs.filter(branch_id=branch_id)

        all_time_metrics = all_time_qs.aggregate(
            total_sales=Sum('grand_total'),
            total_profit=Sum('net_profit'),
            total_count=Count('id')
        )

        # Payment method distribution
        payment_methods = list(tx_qs.values('payment_method').annotate(
            count=Count('id'),
            total=Sum('grand_total')
        ).order_by('-total'))

        # Top 5 products sold today
        items_qs = TransactionItem.objects.filter(transaction__created_at__range=(today_start, today_end))
        if branch_id:
            items_qs = items_qs.filter(transaction__branch_id=branch_id)

        top_products = list(items_qs.values('product__name', 'unit_name').annotate(
            qty=Sum('quantity'),
            revenue=Sum('subtotal'),
            profit=Sum('item_profit')
        ).order_by('-qty')[:8])

        return {
            'today': {
                'sales': float(today_metrics['total_sales'] or 0),
                'profit': float(today_metrics['total_profit'] or 0),
                'count': int(today_metrics['total_count'] or 0),
                'avg_ticket': float(today_metrics['avg_ticket'] or 0),
            },
            'all_time': {
                'sales': float(all_time_metrics['total_sales'] or 0),
                'profit': float(all_time_metrics['total_profit'] or 0),
                'count': int(all_time_metrics['total_count'] or 0),
            },
            'payment_methods': payment_methods,
            'top_products': top_products,
        }
