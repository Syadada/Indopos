import os
import sys
import json
import time

# Set Django settings
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'indopos_project.settings')

import django
django.setup()

from django.test import Client
from pos_app.models import Product, Branch, Transaction, BranchStock
from pos_app.barcode_service import BarcodeService
from pos_app.duckdb_service import DuckDBService

def print_banner(text):
    print("=" * 65)
    print(f" {text}")
    print("=" * 65)

def run_tests():
    print_banner("SIMULATOR PENGUJIAN SCANNER BARCODE (OFFLINE TEST SUITE)")
    client = Client()
    
    # 1. Test Hardware Scan Emulation on Known Store Products
    print("\n[TEST 1] Simulasi Scan Produk Toko (Tingkat 1 - Database Toko)...")
    test_barcodes = [
        ("8998866200011", "INDOMIE GORENG SPESIAL 85G"),
        ("8998866200019", "MIE SEDAAP GORENG SPESIAL 90G"),
        ("8998866200017", "POP MIE RASA AYAM BAWANG 75G")
    ]
    
    scanned_items = []
    
    for barcode, expected_name in test_barcodes:
        start_time = time.time()
        res = client.post(
            '/api/pos/scan/',
            data=json.dumps({'term': barcode, 'branch_id': 1}),
            content_type='application/json'
        )
        latency = (time.time() - start_time) * 1000
        
        data = res.json()
        assert res.status_code == 200, f"Gagal scan {barcode}: Status {res.status_code}"
        assert data.get('found') is True, f"Barcode {barcode} tidak ditemukan"
        
        name = data.get('name', '')
        price = data.get('sell_price', 0)
        print(f"  [OK] SCAN BERHASIL: [{barcode}] -> {name} | Rp {price:,.0f} ({latency:.1f} ms)")
        scanned_items.append({
            'product_id': data['id'],
            'name': name,
            'price': price,
            'quantity': 2,
            'conversion_factor': 1
        })

    # 2. Test Offline Sembako Catalog via DuckDB (Tingkat 2)
    print("\n[TEST 2] Simulasi Scan Sembako Offline (Tingkat 2 - DuckDB Engine)...")
    duckdb_barcodes = [
        ("8999908000101", "NEO RHEUMACYL 20 TABLET")
    ]
    
    for barcode, expected_name in duckdb_barcodes:
        start_time = time.time()
        res = client.post(
            '/api/pos/scan/',
            data=json.dumps({'term': barcode, 'branch_id': 1}),
            content_type='application/json'
        )
        latency = (time.time() - start_time) * 1000
        data = res.json()
        
        assert res.status_code == 200
        assert data.get('found') is True
        assert data.get('type') == 'catalog_unregistered'
        print(f"  [OK] MASTER CATALOG DITEMUKAN: [{barcode}] -> {data['name']} [Sumber: {data['source']}] ({latency:.1f} ms)")

    # 3. Test Barcode Unregistered
    print("\n[TEST 3] Simulasi Scan Barcode Tidak Dikenal...")
    res = client.post(
        '/api/pos/scan/',
        data=json.dumps({'term': '9999999999999', 'branch_id': 1}),
        content_type='application/json'
    )
    data = res.json()
    assert data.get('found') is False
    print(f"  [OK] RESPON TEPAT: {data.get('message')}")

    # 4. Test Complete POS Checkout with Scanned Items
    print("\n[TEST 4] Simulasi Checkout Keranjang Hasil Scan Barcode...")
    subtotal = sum(it['price'] * it['quantity'] for it in scanned_items)
    checkout_payload = {
        'branch_id': 1,
        'customer_name': 'Pelanggan Uji Coba Scan',
        'payment_method': 'Cash',
        'paid_amount': subtotal + 50000,
        'discount_amount': 0,
        'items': scanned_items
    }
    
    start_time = time.time()
    res = client.post(
        '/api/pos/checkout/',
        data=json.dumps(checkout_payload),
        content_type='application/json'
    )
    latency = (time.time() - start_time) * 1000
    c_data = res.json()
    
    assert res.status_code == 200, f"Checkout gagal: {c_data}"
    assert c_data.get('success') is True
    
    print(f"  [OK] TRANSAKSI SUKSES: Nota #{c_data['invoice_number']}")
    print(f"  [OK] TOTAL BELANJA  : Rp {c_data['grand_total']:,.0f}")
    print(f"  [OK] DIBAYAR (TUNAI): Rp {c_data['paid_amount']:,.0f}")
    print(f"  [OK] KEMBALIAN      : Rp {c_data['change_amount']:,.0f}")
    print(f"  [OK] CETAK STRUK URL: {c_data['print_url']} ({latency:.1f} ms)")

    # 5. Verify Thermal Receipt Rendering
    print("\n[TEST 5] Pengujian Render Struk Thermal...")
    res = client.get(c_data['print_url'])
    assert res.status_code == 200
    assert "STRUK PEMBAYARAN" in res.content.decode('utf-8').upper()
    print("  [OK] Struk Thermal 80mm Berhasil Diterbitkan dan Siap Cetak!")

    print_banner("SEMUA 5 PENGUJIAN SIMULATOR SCANNER BARCODE LOLOS 100%!")

if __name__ == '__main__':
    run_tests()
