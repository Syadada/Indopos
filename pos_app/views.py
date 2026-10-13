import json
import uuid
import datetime
from decimal import Decimal
from django.shortcuts import render, get_object_or_404, redirect
from django.http import JsonResponse, HttpResponse
from django.views.decorators.csrf import csrf_exempt
from django.utils import timezone
from django.db import transaction

from pos_app.models import (
    Branch, Product, BranchStock, Transaction, TransactionItem,
    Unit, Category, BarcodeCatalog
)
from pos_app.barcode_service import BarcodeService
from pos_app.duckdb_service import DuckDBService

def write_pos_log(filename, data):
    """Write structured JSON log line to logs directory."""
    try:
        from pathlib import Path
        from django.conf import settings
        log_dir = Path(settings.BASE_DIR) / 'logs'
        log_dir.mkdir(parents=True, exist_ok=True)
        log_file = log_dir / filename
        timestamp = timezone.now().strftime('%Y-%m-%d %H:%M:%S')
        with open(log_file, 'a', encoding='utf-8') as f:
            f.write(f"[{timestamp}] {json.dumps(data, ensure_ascii=False)}\n")
    except Exception:
        pass


def pos_view(request):
    """Render the Main Cashier / POS interface."""
    branches = Branch.objects.filter(is_active=True)
    first_branch = branches.first()
    selected_branch_id = request.GET.get('branch_id', first_branch.id if first_branch else 1)

    categories = Category.objects.all()

    context = {
        'branches': branches,
        'selected_branch_id': int(selected_branch_id),
        'categories': categories,
    }
    return render(request, 'pos/index.html', context)


def analytics_view(request):
    """DuckDB-Powered Local Business Analytics Dashboard."""
    branch_id = request.GET.get('branch_id')
    branch_id = int(branch_id) if branch_id else None

    branches = Branch.objects.filter(is_active=True)
    summary = DuckDBService.get_analytics_summary(branch_id)

    context = {
        'branches': branches,
        'selected_branch_id': branch_id,
        'summary': summary,
    }
    return render(request, 'pos/analytics.html', context)


def print_receipt_view(request, transaction_id):
    """Thermal printer receipt view (58mm / 80mm format)."""
    tx = get_object_or_404(
        Transaction.objects.prefetch_related('items__product', 'items__unit', 'branch'),
        id=transaction_id
    )
    return render(request, 'pos/receipt.html', {'transaction': tx})


# ==========================================
# REST API ENDPOINTS
# ==========================================

@csrf_exempt
def api_scan(request):
    """Scan barcode or search term via hierarchical lookup."""
    if request.method != 'POST':
        return JsonResponse({'error': 'Method not allowed'}, status=405)

    try:
        data = json.loads(request.body)
        term = data.get('term', '').strip()
        branch_id = data.get('branch_id')

        if not term:
            return JsonResponse({'found': False, 'message': 'Term pencarian kosong.'})

        result = BarcodeService.lookup(term, branch_id=branch_id)
        write_pos_log('pos_scans.log', {
            'event': 'BARCODE_SCAN',
            'term': term,
            'branch_id': branch_id,
            'found': result.get('found', False) if result else False,
            'name': result.get('name') if result else None,
            'source': result.get('source') if result else None,
        })
        return JsonResponse(result or {'found': False, 'message': 'Data tidak ditemukan.'})
    except Exception as e:
        write_pos_log('system_errors.log', {'event': 'SCAN_ERROR', 'error': str(e), 'term': term if 'term' in locals() else None})
        return JsonResponse({'error': str(e)}, status=500)


@csrf_exempt
def api_search_products(request):
    """Instant search for products in catalog."""
    query = request.GET.get('q', '').strip()
    branch_id = request.GET.get('branch_id')

    qs = Product.objects.filter(is_active=True)
    if query:
        qs = qs.filter(name__icontains=query) | qs.filter(barcode__icontains=query)

    products = []
    for p in qs[:20]:
        products.append({
            'id': p.id,
            'name': p.name,
            'barcode': p.barcode,
            'sell_price': float(p.sell_price),
            'buy_price': float(p.buy_price),
            'unit_name': p.base_unit.short_name if p.base_unit else 'Pcs',
        })

    return JsonResponse({'products': products})


@csrf_exempt
def api_checkout(request):
    """Process POS checkout, persist transaction, update branch stock."""
    if request.method != 'POST':
        return JsonResponse({'error': 'Method not allowed'}, status=405)

    try:
        data = json.loads(request.body)
        branch_id = data.get('branch_id')
        items = data.get('items', [])
        customer_name = data.get('customer_name', 'Umum').strip() or 'Umum'
        payment_method = data.get('payment_method', 'Cash')
        paid_amount = Decimal(str(data.get('paid_amount', 0)))
        discount_amount = Decimal(str(data.get('discount_amount', 0)))

        if not items:
            return JsonResponse({'success': False, 'message': 'Keranjang belanja kosong.'}, status=400)

        branch = Branch.objects.filter(id=branch_id).first()
        if not branch:
            branch = Branch.objects.first()

        with transaction.atomic():
            # Generate invoice number: INV-YYYYMMDD-XXXX
            today_str = timezone.now().strftime('%Y%m%d')
            unique_suffix = str(uuid.uuid4().hex[:5]).upper()
            invoice_num = f"INV-{today_str}-{unique_suffix}"

            total_amount = Decimal('0')
            total_cost_price = Decimal('0')

            created_items_data = []

            for it in items:
                prod = Product.objects.select_related('base_unit').get(id=it['product_id'])
                qty = int(it.get('quantity', 1))
                unit_id = it.get('unit_id', prod.base_unit.id)
                unit_obj = Unit.objects.filter(id=unit_id).first() or prod.base_unit

                price_per_unit = Decimal(str(it.get('price', prod.sell_price)))
                cost_price = Decimal(str(prod.buy_price))
                conv_factor = int(it.get('conversion_factor', 1))

                subtotal = price_per_unit * qty
                total_base_qty = qty * conv_factor
                item_cost_total = (cost_price * conv_factor) * qty
                item_profit = subtotal - item_cost_total

                total_amount += subtotal
                total_cost_price += item_cost_total

                created_items_data.append({
                    'product': prod,
                    'unit': unit_obj,
                    'unit_name': unit_obj.short_name,
                    'quantity': qty,
                    'conversion_factor': conv_factor,
                    'total_base_quantity': total_base_qty,
                    'price_per_unit': price_per_unit,
                    'cost_price_per_unit': cost_price,
                    'subtotal': subtotal,
                    'item_profit': item_profit,
                })

                # Deduct stock if branch stock exists
                b_stock, _ = BranchStock.objects.get_or_create(
                    branch=branch,
                    product=prod,
                    defaults={'store_stock': 100, 'warehouse_stock': 100}
                )
                b_stock.store_stock = max(0, b_stock.store_stock - total_base_qty)
                b_stock.save()

            grand_total = max(Decimal('0'), total_amount - discount_amount)
            change_amount = max(Decimal('0'), paid_amount - grand_total)
            net_profit = grand_total - total_cost_price

            # Create Transaction record
            tx = Transaction.objects.create(
                transaction_number=invoice_num,
                branch=branch,
                customer_name=customer_name,
                total_amount=total_amount,
                discount_amount=discount_amount,
                tax_amount=Decimal('0'),
                grand_total=grand_total,
                paid_amount=paid_amount if paid_amount > 0 else grand_total,
                change_amount=change_amount,
                total_cost_price=total_cost_price,
                net_profit=net_profit,
                payment_method=payment_method,
                status='Completed',
            )

            # Insert all transaction items
            for item_kwargs in created_items_data:
                TransactionItem.objects.create(transaction=tx, **item_kwargs)

        write_pos_log('pos_transactions.log', {
            'event': 'CHECKOUT_SUCCESS',
            'invoice': tx.transaction_number,
            'branch_id': tx.branch.id,
            'customer': tx.customer_name,
            'grand_total': float(tx.grand_total),
            'payment_method': tx.payment_method,
            'items_count': len(created_items_data),
        })

        return JsonResponse({
            'success': True,
            'transaction_id': tx.id,
            'invoice_number': tx.transaction_number,
            'grand_total': float(tx.grand_total),
            'paid_amount': float(tx.paid_amount),
            'change_amount': float(tx.change_amount),
            'print_url': f"/transactions/{tx.id}/print/",
        })

    except Exception as e:
        write_pos_log('system_errors.log', {'event': 'CHECKOUT_ERROR', 'error': str(e)})
        return JsonResponse({'success': False, 'message': str(e)}, status=500)


def api_duckdb_summary(request):
    """API for real-time DuckDB analytical metrics."""
    branch_id = request.GET.get('branch_id')
    branch_id = int(branch_id) if branch_id else None
    summary = DuckDBService.get_analytics_summary(branch_id)
    return JsonResponse(summary)
