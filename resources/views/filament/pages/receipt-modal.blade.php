<div id="printable-receipt" class="p-4 bg-white text-black font-mono text-xs max-w-sm mx-auto shadow-md rounded border border-gray-200">
    <style>
        #printable-receipt {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
        }

        #printable-receipt .store-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        #printable-receipt .store-info {
            font-size: 10px;
            color: #374151;
            text-align: center;
        }

        #printable-receipt .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        #printable-receipt .double-divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }

        #printable-receipt .info-row,
        #printable-receipt .total-row,
        #printable-receipt .item-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #printable-receipt .item-row {
            margin-bottom: 5px;
        }

        #printable-receipt .item-name {
            font-weight: bold;
            font-size: 11px;
        }

        #printable-receipt .grand-total {
            font-size: 13px;
            font-weight: bold;
        }

        #printable-receipt .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
        }

        #printable-receipt .barcode {
            margin-top: 8px;
            letter-spacing: 2px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            #printable-receipt, #printable-receipt * {
                visibility: visible;
            }
            #printable-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 80mm;
                padding: 10px;
                border: none;
                box-shadow: none;
            }
        }
    </style>

    <!-- HEADER Cabang / Toko & Customer (Kiri & Kanan) -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px; margin-bottom: 4px;">
        <!-- Kiri: Info Toko / Cabang -->
        <div style="text-align: left; width: 58%;">
            <div class="store-name" style="text-align: left;">{{ $transaction->branch->name ?? 'INDO POS SUPERMARKET' }}</div>
            @if(!empty($transaction->branch->subtitle))
                <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">{{ $transaction->branch->subtitle }}</div>
            @endif
            @if(!empty($transaction->branch->address))
                <div class="store-info" style="text-align: left;">{{ $transaction->branch->address }}</div>
            @endif
            @if(!empty($transaction->branch->phone))
                <div class="store-info" style="text-align: left;">Telp: {{ $transaction->branch->phone }}</div>
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
    <div class="space-y-1">
        @foreach($transaction->items as $item)
            <div class="item-row">
                <div class="item-name">{{ $item->product->name ?? 'Barang' }}</div>
                <div class="item-detail">
                    <span>{{ $item->quantity }} {{ $item->unit_name }} x {{ number_format($item->price_per_unit, 0, ',', '.') }}</span>
                    <span class="font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
    </div>

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
    <div class="footer">
        <p class="font-bold">*** TERIMA KASIH ***</p>
        <p>Barang yang sudah dibeli</p>
        <p>tidak dapat ditukar / dikembalikan</p>

        <div class="barcode">*{{ $transaction->transaction_number }}*</div>
    </div>
</div>
