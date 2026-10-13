from django.core.management.base import BaseCommand
from pos_app.duckdb_service import DuckDBService


class Command(BaseCommand):
    help = 'Inisialisasi DuckDB lokal dan muat katalog CSV sembako'

    def handle(self, *args, **options):
        self.stdout.write(self.style.NOTICE("Memulai inisialisasi DuckDB lokal..."))
        DuckDBService.initialize_db()
        self.stdout.write(self.style.SUCCESS("DuckDB berhasil diinisialisasi dan siap digunakan!"))
