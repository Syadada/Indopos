<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #{{ $transaction->transaction_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt-card {
            background-color: #fff;
            width: 80mm;
            max-width: 100%;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 4px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .store-name {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .store-info {
            font-size: 10px;
            color: #4b5563;
            line-height: 1.3;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .double-divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .item-row {
            margin-bottom: 6px;
        }

        .item-name {
            font-weight: bold;
            font-size: 11px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .grand-total {
            font-size: 14px;
            font-weight: bold;
        }

        .footer {
            margin-top: 12px;
            font-size: 10px;
            line-height: 1.4;
        }

        .barcode {
            margin-top: 10px;
            letter-spacing: 2px;
            font-size: 14px;
            font-weight: bold;
        }

        .action-buttons {
            margin-top: 15px;
            display: flex;
            gap: 8px;
        }

        .btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }

        .btn-primary {
            background-color: #10b981;
            color: white;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        /* MEDIA PRINT SETTINGS */
        @media print {
            body {
                background-color: transparent;
                padding: 0;
            }

            .receipt-card {
                width: 100%;
                box-shadow: none;
                padding: 0;
                border-radius: 0;
            }

            .action-buttons {
                display: none !important;
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        <!-- HEADER Cabang / Toko & Customer (Kiri & Kanan) -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px; margin-bottom: 4px;">
            <!-- Kiri: Info Toko / Cabang -->
            <div style="text-align: left; width: 58%;">
                <div class="store-name uppercase">{{ $transaction->branch->name ?? 'INDO POS SUPERMARKET' }}</div>
                @if(!empty($transaction->branch->subtitle))
                    <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">{{ $transaction->branch->subtitle }}</div>
                @endif
                @if(!empty($transaction->branch->address))
                    <div class="store-info">{{ $transaction->branch->address }}</div>
                @endif
                @if(!empty($transaction->branch->phone))
                    <div class="store-info">Telp: {{ $transaction->branch->phone }}</div>
                @endif
            </div>

            <!-- Kanan: Kepada Yth Pelanggan -->
            <div style="text-align: right; width: 40%; padding-top: 2px;">
                <div style="font-size: 10px; font-weight: bold;">Kepada Yth:</div>
                @if(!empty($transaction->customer_name))
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; word-break: break-word;">
                        {{ $transaction->customer_name }}
                    </div>
                @endif
            </div>
        </div>

        <div class="double-divider"></div>

        <!-- INFO TRANSAKSI -->
        <div class="info-row">
            <span>No: {{ $transaction->transaction_number }}</span>
            <span>{{ $transaction->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span>Kasir: {{ $transaction->user->name ?? 'Admin' }}</span>
            <span>Bayar: {{ $transaction->payment_method }}</span>
        </div>

        <div class="divider"></div>

        <!-- LIST ITEMS -->
        @foreach($transaction->items as $item)
            <div class="item-row">
                <div class="item-name">{{ $item->product->name ?? 'Barang' }}</div>
                <div class="item-detail">
                    <span>{{ $item->quantity }} {{ $item->unit_name }} x {{ number_format($item->price_per_unit, 0, ',', '.') }}</span>
                    <span class="font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach

        <div class="divider"></div>

        <!-- TOTAL & PEMBAYARAN -->
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
        </div>

        @if($transaction->discount_amount > 0)
            <div class="total-row">
                <span>Diskon:</span>
                <span>-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
            </div>
        @endif

        <div class="total-row grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>

        <div class="total-row">
            <span>BAYAR:</span>
            <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row font-bold">
            <span>KEMBALI:</span>
            <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
        </div>

        <div class="double-divider"></div>

        <!-- FOOTER -->
        <div class="text-center footer">
            <p class="font-bold">*** TERIMA KASIH ***</p>
            <p>Barang yang sudah dibeli</p>
            <p>tidak dapat ditukar / dikembalikan</p>

            <div class="barcode">*{{ $transaction->transaction_number }}*</div>
        </div>

        <!-- TOMBOL AKSI CETAK / TUTUP -->
        <div class="action-buttons">
            <button class="btn btn-primary" onclick="window.print()">🖨️ Cetak Struk</button>
            <button class="btn btn-secondary" onclick="window.close()">❌ Tutup</button>
        </div>
    </div>

    <script>
        // Auto trigger print dialog on page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
