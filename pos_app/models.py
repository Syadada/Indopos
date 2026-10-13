from django.db import models
from django.utils import timezone


class Branch(models.Model):
    id = models.BigAutoField(primary_key=True)
    name = models.CharField(max_length=255)
    code = models.CharField(unique=True, max_length=255)
    phone = models.CharField(max_length=255, blank=True, null=True)
    address = models.TextField(blank=True, null=True)
    subtitle = models.CharField(max_length=255, blank=True, null=True)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'branches'
        verbose_name = 'Cabang'
        verbose_name_plural = 'Cabang Toko'

    def __str__(self):
        return f"{self.name} ({self.code})"


class Category(models.Model):
    id = models.BigAutoField(primary_key=True)
    name = models.CharField(max_length=255)
    slug = models.CharField(unique=True, max_length=255)
    description = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'categories'
        verbose_name = 'Kategori'
        verbose_name_plural = 'Kategori Produk'

    def __str__(self):
        return self.name


class Unit(models.Model):
    id = models.BigAutoField(primary_key=True)
    name = models.CharField(max_length=255)
    short_name = models.CharField(max_length=255)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'units'
        verbose_name = 'Satuan'
        verbose_name_plural = 'Satuan Barang'

    def __str__(self):
        return f"{self.name} ({self.short_name})"


class Product(models.Model):
    id = models.BigAutoField(primary_key=True)
    category = models.ForeignKey(Category, on_delete=models.SET_NULL, null=True, related_name='products')
    name = models.CharField(max_length=255)
    sku = models.CharField(unique=True, max_length=255, blank=True, null=True)
    barcode = models.CharField(unique=True, max_length=255)
    description = models.TextField(blank=True, null=True)
    base_unit = models.ForeignKey(Unit, on_delete=models.PROTECT, related_name='base_products')
    buy_price = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    sell_price = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'products'
        verbose_name = 'Produk'
        verbose_name_plural = 'Daftar Produk'

    def __str__(self):
        return f"{self.name} [{self.barcode}]"


class ProductUnitConversion(models.Model):
    id = models.BigAutoField(primary_key=True)
    product = models.ForeignKey(Product, on_delete=models.CASCADE, related_name='conversions')
    unit = models.ForeignKey(Unit, on_delete=models.CASCADE)
    conversion_factor = models.IntegerField(default=1)
    sell_price = models.DecimalField(max_digits=15, decimal_places=2, blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'product_unit_conversions'
        verbose_name = 'Konversi Satuan'
        verbose_name_plural = 'Konversi Satuan'

    def __str__(self):
        return f"{self.product.name} - 1 {self.unit.short_name} = {self.conversion_factor} {self.product.base_unit.short_name}"


class BranchStock(models.Model):
    id = models.BigAutoField(primary_key=True)
    branch = models.ForeignKey(Branch, on_delete=models.CASCADE, related_name='stocks')
    product = models.ForeignKey(Product, on_delete=models.CASCADE, related_name='stocks')
    store_stock = models.IntegerField(default=0)
    warehouse_stock = models.IntegerField(default=0)
    min_store_stock_alert = models.IntegerField(default=5)
    min_warehouse_stock_alert = models.IntegerField(default=10)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'branch_stocks'
        unique_together = (('branch', 'product'),)
        verbose_name = 'Stok Cabang'
        verbose_name_plural = 'Stok Cabang'

    def __str__(self):
        return f"{self.product.name} @ {self.branch.name} (Toko: {self.store_stock})"


class Transaction(models.Model):
    id = models.BigAutoField(primary_key=True)
    transaction_number = models.CharField(unique=True, max_length=255)
    branch = models.ForeignKey(Branch, on_delete=models.PROTECT, related_name='transactions')
    user = models.ForeignKey('LegacyUser', on_delete=models.SET_NULL, blank=True, null=True, db_column='user_id')
    customer_name = models.CharField(max_length=255, blank=True, null=True, default='Umum')
    total_amount = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    discount_amount = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    tax_amount = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    grand_total = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    paid_amount = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    change_amount = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    total_cost_price = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    net_profit = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    payment_method = models.CharField(max_length=255, default='Cash')
    status = models.CharField(max_length=255, default='Completed')
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'transactions'
        verbose_name = 'Transaksi'
        verbose_name_plural = 'Riwayat Transaksi'

    def __str__(self):
        return f"Nota #{self.transaction_number} - Rp {self.grand_total:,.0f}"


class TransactionItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    transaction = models.ForeignKey(Transaction, on_delete=models.CASCADE, related_name='items')
    product = models.ForeignKey(Product, on_delete=models.PROTECT)
    unit = models.ForeignKey(Unit, on_delete=models.PROTECT)
    unit_name = models.CharField(max_length=255)
    quantity = models.IntegerField(default=1)
    conversion_factor = models.IntegerField(default=1)
    total_base_quantity = models.IntegerField(default=1)
    price_per_unit = models.DecimalField(max_digits=15, decimal_places=2)
    cost_price_per_unit = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    subtotal = models.DecimalField(max_digits=15, decimal_places=2)
    item_profit = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'transaction_items'
        verbose_name = 'Item Transaksi'
        verbose_name_plural = 'Item Transaksi'

    def __str__(self):
        return f"{self.quantity} {self.unit_name} x {self.product.name}"


class DailyPosting(models.Model):
    id = models.BigAutoField(primary_key=True)
    posting_code = models.CharField(unique=True, max_length=255)
    posting_date = models.DateField()
    branch = models.ForeignKey(Branch, on_delete=models.PROTECT, related_name='daily_postings')
    total_transactions_count = models.IntegerField(default=0)
    total_items_sold = models.IntegerField(default=0)
    total_money_in = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    total_money_out = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    net_profit = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    status = models.CharField(max_length=255, default='Closed')
    notes = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'daily_postings'
        unique_together = (('posting_date', 'branch'),)
        verbose_name = 'Posting Harian (Tutup Buku)'
        verbose_name_plural = 'Posting Harian (Tutup Buku)'

    def __str__(self):
        return f"{self.posting_code} - {self.posting_date} ({self.branch.name})"


class DailyPostingItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    daily_posting = models.ForeignKey(DailyPosting, on_delete=models.CASCADE, related_name='items')
    product = models.ForeignKey(Product, on_delete=models.PROTECT)
    product_name = models.CharField(max_length=255)
    total_qty_sold = models.IntegerField(default=0)
    total_base_qty_sold = models.IntegerField(default=0)
    total_revenue = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    total_cost = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    net_profit = models.DecimalField(max_digits=15, decimal_places=2, default=0)
    transaction_times = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'daily_posting_items'
        verbose_name = 'Detail Barang Posting'
        verbose_name_plural = 'Detail Barang Posting'

    def __str__(self):
        return f"{self.product_name} - {self.total_qty_sold} terjual"


class BarcodeCatalog(models.Model):
    id = models.BigAutoField(primary_key=True)
    barcode = models.CharField(unique=True, max_length=255)
    name = models.CharField(max_length=255)
    category = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True, null=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'barcode_catalogs'
        verbose_name = 'Master Katalog Barcode'
        verbose_name_plural = 'Master Katalog Barcode'

    def __str__(self):
        return f"{self.barcode} - {self.name}"


class LegacyUser(models.Model):
    id = models.BigAutoField(primary_key=True)
    name = models.CharField(max_length=255)
    email = models.CharField(unique=True, max_length=255)
    password = models.CharField(max_length=255)
    branch = models.ForeignKey(Branch, on_delete=models.SET_NULL, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'users'
        verbose_name = 'Pengguna Toko'
        verbose_name_plural = 'Pengguna Toko'

    def __str__(self):
        return f"{self.name} ({self.email})"
