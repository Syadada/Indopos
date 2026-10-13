from django.contrib import admin
from django.utils.html import format_html
from pos_app.models import (
    Branch, Category, Unit, Product, ProductUnitConversion,
    BranchStock, Transaction, TransactionItem, DailyPosting,
    DailyPostingItem, BarcodeCatalog, LegacyUser
)

# Admin Site Customization
admin.site.site_header = "IndoPOS Admin - Python & DuckDB Engine"
admin.site.site_title = "IndoPOS Management"
admin.site.index_title = "Dasbor Manajemen Retail & Kasir POS"


class ProductUnitConversionInline(admin.TabularInline):
    model = ProductUnitConversion
    extra = 1
    fields = ('unit', 'conversion_factor', 'sell_price')


class BranchStockInline(admin.TabularInline):
    model = BranchStock
    extra = 0
    fields = ('branch', 'store_stock', 'warehouse_stock', 'min_store_stock_alert')


@admin.register(Product)
class ProductAdmin(admin.ModelAdmin):
    list_display = ('barcode_badge', 'name', 'category', 'base_unit', 'formatted_buy_price', 'formatted_sell_price', 'active_badge')
    list_filter = ('category', 'is_active', 'base_unit')
    search_fields = ('name', 'barcode', 'sku')
    list_per_page = 25
    inlines = [ProductUnitConversionInline, BranchStockInline]

    def barcode_badge(self, obj):
        return format_html(
            '<span style="font-family: monospace; background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-weight: 600;">{}</span>',
            obj.barcode
        )
    barcode_badge.short_description = "Barcode"

    def formatted_buy_price(self, obj):
        return f"Rp {obj.buy_price:,.0f}"
    formatted_buy_price.short_description = "Harga Beli"

    def formatted_sell_price(self, obj):
        return format_html(
            '<span style="font-weight: 600; color: #047857;">Rp {:,.0f}</span>',
            obj.sell_price
        )
    formatted_sell_price.short_description = "Harga Jual"

    def active_badge(self, obj):
        if obj.is_active:
            return format_html('<span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">Aktif</span>')
        return format_html('<span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">Nonaktif</span>')
    active_badge.short_description = "Status"


class TransactionItemInline(admin.TabularInline):
    model = TransactionItem
    extra = 0
    readonly_fields = ('product', 'unit_name', 'quantity', 'price_per_unit', 'subtotal', 'item_profit')
    can_delete = False


@admin.register(Transaction)
class TransactionAdmin(admin.ModelAdmin):
    list_display = ('invoice_badge', 'created_at', 'branch', 'customer_name', 'formatted_total', 'payment_badge', 'profit_badge')
    list_filter = ('payment_method', 'status', 'branch', 'created_at')
    search_fields = ('transaction_number', 'customer_name')
    date_hierarchy = 'created_at'
    list_per_page = 20
    inlines = [TransactionItemInline]
    readonly_fields = ('transaction_number', 'total_amount', 'discount_amount', 'grand_total', 'paid_amount', 'change_amount', 'net_profit', 'created_at')

    def invoice_badge(self, obj):
        return format_html(
            '<a href="/transactions/{}/print" target="_blank" style="font-weight: 700; color: #2563eb; text-decoration: none;">📄 {}</a>',
            obj.id, obj.transaction_number
        )
    invoice_badge.short_description = "No. Transaksi"

    def formatted_total(self, obj):
        return format_html('<strong>Rp {:,.0f}</strong>', obj.grand_total)
    formatted_total.short_description = "Total"

    def payment_badge(self, obj):
        colors = {
            'Cash': '#10b981',
            'QRIS': '#8b5cf6',
            'Transfer': '#3b82f6',
            'Debit': '#f59e0b',
        }
        bg = colors.get(obj.payment_method, '#6b7280')
        return format_html(
            '<span style="background: {}; color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">{}</span>',
            bg, obj.payment_method
        )
    payment_badge.short_description = "Metode"

    def profit_badge(self, obj):
        return format_html('<span style="color: #059669; font-weight: 600;">+Rp {:,.0f}</span>', obj.net_profit)
    profit_badge.short_description = "Laba Bersih"


@admin.register(Branch)
class BranchAdmin(admin.ModelAdmin):
    list_display = ('name', 'code', 'subtitle', 'phone', 'is_active')
    search_fields = ('name', 'code')


@admin.register(Category)
class CategoryAdmin(admin.ModelAdmin):
    list_display = ('name', 'slug', 'description')
    search_fields = ('name', 'slug')


@admin.register(Unit)
class UnitAdmin(admin.ModelAdmin):
    list_display = ('name', 'short_name')
    search_fields = ('name', 'short_name')


@admin.register(BranchStock)
class BranchStockAdmin(admin.ModelAdmin):
    list_display = ('product', 'branch', 'store_stock', 'warehouse_stock', 'stock_alert_badge')
    list_filter = ('branch',)
    search_fields = ('product__name', 'product__barcode')

    def stock_alert_badge(self, obj):
        if obj.store_stock <= obj.min_store_stock_alert:
            return format_html('<span style="color: #dc2626; font-weight: 700;">⚠️ Menipis ({})</span>', obj.store_stock)
        return format_html('<span style="color: #16a34a;">Aman ({})</span>', obj.store_stock)
    stock_alert_badge.short_description = "Status Stok"


class DailyPostingItemInline(admin.TabularInline):
    model = DailyPostingItem
    extra = 0
    readonly_fields = ('product_name', 'total_qty_sold', 'total_revenue', 'net_profit')
    can_delete = False


@admin.register(DailyPosting)
class DailyPostingAdmin(admin.ModelAdmin):
    list_display = ('posting_code', 'posting_date', 'branch', 'total_transactions_count', 'formatted_money_in', 'formatted_profit', 'status')
    list_filter = ('branch', 'status', 'posting_date')
    search_fields = ('posting_code',)
    inlines = [DailyPostingItemInline]

    def formatted_money_in(self, obj):
        return f"Rp {obj.total_money_in:,.0f}"
    formatted_money_in.short_description = "Omzet (Uang Masuk)"

    def formatted_profit(self, obj):
        return format_html('<span style="color: #16a34a; font-weight: 700;">Rp {:,.0f}</span>', obj.net_profit)
    formatted_profit.short_description = "Laba Bersih"


@admin.register(BarcodeCatalog)
class BarcodeCatalogAdmin(admin.ModelAdmin):
    list_display = ('barcode', 'name', 'category', 'created_at')
    search_fields = ('barcode', 'name')
    list_filter = ('category',)
    list_per_page = 30
