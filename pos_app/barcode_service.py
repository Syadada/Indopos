import requests
from pos_app.models import Product, BarcodeCatalog, ProductUnitConversion
from pos_app.duckdb_service import DuckDBService


class BarcodeService:
    @staticmethod
    def lookup(term, branch_id=None):
        """
        Hierarchical barcode lookup:
        1. Store inventory (Product)
        2. Local DuckDB Sembako dataset (data_sembako.csv & lengkap)
        3. Local BarcodeCatalog database cache
        4. Open Food Facts Online API fallback (auto-cached)
        """
        code = str(term).strip()
        if not code:
            return None

        # Level 1: Check store products
        product = Product.objects.filter(is_active=True).filter(
            models_q(code)
        ).select_related('base_unit', 'category').first()

        if product:
            conversions = []
            for conv in product.conversions.select_related('unit').all():
                conversions.append({
                    'unit_id': conv.unit.id,
                    'unit_name': conv.unit.name,
                    'short_name': conv.unit.short_name,
                    'factor': conv.conversion_factor,
                    'sell_price': float(conv.sell_price) if conv.sell_price else float(product.sell_price) * conv.conversion_factor,
                })

            # Check branch stock if branch_id provided
            stock_qty = 0
            if branch_id:
                bs = product.stocks.filter(branch_id=branch_id).first()
                if bs:
                    stock_qty = bs.store_stock

            return {
                'found': True,
                'type': 'product',
                'id': product.id,
                'name': product.name,
                'barcode': product.barcode,
                'sku': product.sku,
                'category': product.category.name if product.category else 'Umum',
                'base_unit_id': product.base_unit.id,
                'base_unit_name': product.base_unit.name,
                'base_unit_short': product.base_unit.short_name,
                'buy_price': float(product.buy_price),
                'sell_price': float(product.sell_price),
                'conversions': conversions,
                'stock': stock_qty,
            }

        # Level 2: Check DuckDB Local Sembako Dataset
        duckdb_match = DuckDBService.search_barcode_catalog(code)
        if duckdb_match:
            return {
                'found': True,
                'type': 'catalog_unregistered',
                'barcode': duckdb_match['barcode'],
                'name': duckdb_match['name'],
                'category': duckdb_match.get('category', 'Sembako'),
                'source': duckdb_match.get('source', 'DuckDB Local'),
                'message': f"Barang dikenali di Master DuckDB: '{duckdb_match['name']}'. Silakan daftarkan harga jual di menu Produk."
            }

        # Level 3: Check BarcodeCatalog database cache
        cat_match = BarcodeCatalog.objects.filter(barcode=code).first()
        if cat_match:
            return {
                'found': True,
                'type': 'catalog_unregistered',
                'barcode': cat_match.barcode,
                'name': cat_match.name,
                'category': cat_match.category or 'Umum',
                'source': 'Master Barcode Toko',
                'message': f"Barang dikenali di Master Barcode: '{cat_match.name}'. Silakan daftarkan harga jual di menu Produk."
            }

        # Level 4: Online Open Food Facts API fallback (hanya untuk format angka barcode valid)
        if code.isdigit() and 8 <= len(code) <= 14:
            try:
                resp = requests.get(
                    f"https://world.openfoodfacts.net/api/v2/product/{code}?fields=product_name,product_name_id,brands",
                    headers={'User-Agent': 'IndoPOS-Python/1.0'},
                    timeout=1.5
                )
                if resp.status_code == 200:
                    data = resp.json()
                    if data.get('status') == 1 and 'product' in data:
                        p = data['product']
                        name = p.get('product_name_id') or p.get('product_name')
                        if name:
                            brand = p.get('brands') or ''
                            full_name = f"{name} ({brand})" if brand else name
                            full_name = full_name[:250]
                        
                            # Cache to BarcodeCatalog
                            BarcodeCatalog.objects.get_or_create(
                                barcode=code,
                                defaults={'name': full_name, 'category': 'Online Auto-Fetch'}
                            )

                            return {
                                'found': True,
                                'type': 'catalog_unregistered',
                                'barcode': code,
                                'name': full_name,
                                'category': 'Online Auto-Fetch',
                                'source': 'OpenFoodFacts API',
                                'message': f"Barang terdeteksi dari Cloud: '{full_name}'. Silakan daftarkan harga jual di menu Produk."
                            }
            except Exception:
                pass

        return {
            'found': False,
            'barcode': code,
            'message': f"Barcode '{code}' belum terdaftar di database toko maupun Master Barcode."
        }


def models_q(term):
    from django.db.models import Q
    return Q(barcode__iexact=term) | Q(sku__iexact=term) | Q(name__icontains=term)
