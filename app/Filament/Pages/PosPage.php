<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use App\Models\ProductUnitConversion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationGroup = 'Kasir POS';
    protected static ?string $navigationLabel = 'Kasir Barcode (POS)';
    protected static ?string $title = 'Kasir Transaksi Barcode (POS)';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.pos-page';

    public $selectedBranchId;
    public $barcodeInput = '';
    public $searchQuery = '';
    public $cart = [];
    public $customerName = '';
    public $paymentMethod = 'Cash';
    public $discountAmount = 0;
    public $paidAmount = 0;
    public $isSplitPayment = false;
    public $splitPayments = [
        ['method' => 'QRIS', 'amount' => 0],
        ['method' => 'Cash', 'amount' => 0],
    ];
    public $autoPrintReceipt = true;
    public $completedTransactionId = null;
    public $showReceiptModal = false;

    public function getCompletedTransactionProperty()
    {
        return $this->completedTransactionId
            ? Transaction::with(['branch', 'user', 'items.product'])->find($this->completedTransactionId)
            : null;
    }

    public function mount()
    {
        $tenant = Filament::getTenant();
        if ($tenant) {
            $this->selectedBranchId = $tenant->id;
        } else {
            $firstBranch = Branch::where('is_active', true)->first();
            $this->selectedBranchId = Auth::user()->branch_id ?? ($firstBranch->id ?? null);
        }
    }

    public function scanBarcode()
    {
        $code = trim($this->barcodeInput);
        if (empty($code)) {
            return;
        }

        $term = mb_strtolower($code);
        $product = Product::where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(barcode) = ?', [$term])
                  ->orWhereRaw('LOWER(sku) = ?', [$term])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $term . '%']);
            })
            ->with(['baseUnit', 'unitConversions.unit'])
            ->first();

        if ($product) {
            \App\Services\SystemLogger::logBarcodeScan($code, 'FOUND', $product->name, 'Database Toko');
            $this->addToCart($product->id);
            $this->barcodeInput = '';
        } else {
            $catalogMatch = \App\Models\BarcodeCatalog::findOrFetchOnline($code);
            if ($catalogMatch) {
                \App\Services\SystemLogger::logBarcodeScan($code, 'CATALOG_UNREGISTERED', $catalogMatch->name, $catalogMatch->category ?? 'Master Barcode');
                Notification::make()
                    ->title('💡 Barcode Dikenali di Master Barcode!')
                    ->body("Barang ini adalah '{$catalogMatch->name}'. Silakan daftarkan harga jual di menu 'Kelola Barang'.")
                    ->info()
                    ->send();
            } else {
                \App\Services\SystemLogger::logBarcodeScan($code, 'NOT_FOUND');
                Notification::make()
                    ->title('⚠️ Data Tidak Ada di Database')
                    ->body("Barcode '$code' tidak ditemukan di database toko maupun Master Barcode Catalog. Silakan tambahkan di menu Kelola Barang.")
                    ->danger()
                    ->send();
            }
            $this->barcodeInput = '';
        }
    }

    public function addToCart($productId, $unitId = null)
    {
        $product = Product::with(['baseUnit', 'unitConversions.unit'])->find($productId);
        if (!$product) return;

        // Check if branch stock exists
        $branchStock = BranchStock::where('branch_id', $this->selectedBranchId)
            ->where('product_id', $product->id)
            ->first();

        $availableStoreStock = $branchStock ? $branchStock->store_stock : 0;

        if ($availableStoreStock <= 0) {
            Notification::make()
                ->title('Stok Toko Habis!')
                ->body("Stok toko untuk {$product->name} kosong pada cabang ini.")
                ->warning()
                ->send();
        }

        // Determine unit and price
        $selectedUnitId = $unitId ?? $product->base_unit_id;
        $unitName = $product->baseUnit->short_name;
        $price = $product->sell_price;
        $conversionFactor = 1;

        if ($selectedUnitId != $product->base_unit_id) {
            $conversion = ProductUnitConversion::where('product_id', $product->id)
                ->where('unit_id', $selectedUnitId)
                ->with('unit')
                ->first();

            if ($conversion) {
                $unitName = $conversion->unit->short_name;
                $conversionFactor = $conversion->conversion_factor;
                $price = $conversion->sell_price ?? ($product->sell_price * $conversionFactor);
            }
        }

        $cartKey = $product->id . '_' . $selectedUnitId;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity'] += 1;
            $this->cart[$cartKey]['subtotal'] = $this->cart[$cartKey]['quantity'] * $this->cart[$cartKey]['price'];
        } else {
            $this->cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'unit_id' => $selectedUnitId,
                'unit_name' => $unitName,
                'conversion_factor' => $conversionFactor,
                'price' => $price,
                'buy_price_base' => $product->buy_price,
                'quantity' => 1,
                'subtotal' => $price,
                'available_units' => $this->getProductUnitOptions($product),
            ];
        }

        Notification::make()
            ->title('Item Ditambahkan')
            ->body("{$product->name} ({$unitName}) masuk ke keranjang.")
            ->success()
            ->send();
    }

    private function getProductUnitOptions($product)
    {
        $options = [
            $product->base_unit_id => [
                'id' => $product->base_unit_id,
                'name' => $product->baseUnit->name . ' (' . $product->baseUnit->short_name . ')',
                'factor' => 1,
                'price' => $product->sell_price,
            ]
        ];

        foreach ($product->unitConversions as $conv) {
            $options[$conv->unit_id] = [
                'id' => $conv->unit_id,
                'name' => $conv->unit->name . ' (' . $conv->unit->short_name . ') - Isi ' . $conv->conversion_factor,
                'factor' => $conv->conversion_factor,
                'price' => $conv->sell_price ?? ($product->sell_price * $conv->conversion_factor),
            ];
        }

        return $options;
    }

    public function changeUnit($cartKey, $newUnitId)
    {
        if (!isset($this->cart[$cartKey])) return;

        $productId = $this->cart[$cartKey]['product_id'];
        $qty = $this->cart[$cartKey]['quantity'];

        unset($this->cart[$cartKey]);

        $this->addToCart($productId, $newUnitId);
        $newCartKey = $productId . '_' . $newUnitId;
        if (isset($this->cart[$newCartKey])) {
            $this->cart[$newCartKey]['quantity'] = $qty;
            $this->cart[$newCartKey]['subtotal'] = $qty * $this->cart[$newCartKey]['price'];
        }
    }

    public function updateQuantity($cartKey, $delta)
    {
        if (isset($this->cart[$cartKey])) {
            $newQty = $this->cart[$cartKey]['quantity'] + $delta;
            if ($newQty <= 0) {
                $this->removeFromCart($cartKey);
            } else {
                $this->cart[$cartKey]['quantity'] = $newQty;
                $this->cart[$cartKey]['subtotal'] = $newQty * $this->cart[$cartKey]['price'];
            }
        }
    }

    public function setQuantity($cartKey, $qty)
    {
        if (isset($this->cart[$cartKey])) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                $this->removeFromCart($cartKey);
            } else {
                $this->cart[$cartKey]['quantity'] = $qty;
                $this->cart[$cartKey]['subtotal'] = $qty * $this->cart[$cartKey]['price'];
            }
        }
    }

    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
    }

    public function toggleSplitPayment()
    {
        $this->isSplitPayment = !$this->isSplitPayment;
        if ($this->isSplitPayment && empty($this->splitPayments)) {
            $this->splitPayments = [
                ['method' => 'QRIS', 'amount' => 0],
                ['method' => 'Cash', 'amount' => 0],
            ];
        }
    }

    public function addSplitPaymentRow()
    {
        $this->splitPayments[] = ['method' => 'Cash', 'amount' => 0];
    }

    public function removeSplitPaymentRow($index)
    {
        if (count($this->splitPayments) > 1) {
            unset($this->splitPayments[$index]);
            $this->splitPayments = array_values($this->splitPayments);
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->customerName = '';
        $this->paidAmount = 0;
        $this->discountAmount = 0;
        $this->isSplitPayment = false;
        $this->splitPayments = [
            ['method' => 'QRIS', 'amount' => 0],
            ['method' => 'Cash', 'amount' => 0],
        ];
    }

    public function getSubtotalProperty()
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getGrandTotalProperty()
    {
        $total = $this->subtotal - (float) $this->discountAmount;
        return max(0, $total);
    }

    public function getEffectivePaidAmountProperty()
    {
        if ($this->isSplitPayment) {
            $total = 0;
            foreach ($this->splitPayments as $sp) {
                $total += (float) ($sp['amount'] ?? 0);
            }
            return $total;
        }

        return (float) $this->paidAmount;
    }

    public function getChangeAmountProperty()
    {
        return max(0, $this->effectivePaidAmount - $this->grandTotal);
    }

    public function getRemainingAmountProperty()
    {
        return max(0, $this->grandTotal - $this->effectivePaidAmount);
    }

    public function processTransaction()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->body('Tambahkan minimal 1 item produk terlebih dahulu.')
                ->warning()
                ->send();
            return;
        }

        $effectivePaid = $this->effectivePaidAmount;

        if ($effectivePaid < $this->grandTotal) {
            Notification::make()
                ->title('Uang Pembayaran Kurang')
                ->body('Nominal uang bayar (Rp ' . number_format($effectivePaid, 0, ',', '.') . ') kurang dari total belanja (Rp ' . number_format($this->grandTotal, 0, ',', '.') . ').')
                ->danger()
                ->send();
            return;
        }

        // Build payment method string
        $finalPaymentMethod = $this->paymentMethod;
        if ($this->isSplitPayment) {
            $parts = [];
            foreach ($this->splitPayments as $sp) {
                $amt = (float) ($sp['amount'] ?? 0);
                if ($amt > 0) {
                    $parts[] = $sp['method'] . ' (Rp ' . number_format($amt, 0, ',', '.') . ')';
                }
            }
            $finalPaymentMethod = !empty($parts) ? implode(' + ', $parts) : 'Split Payment';
        }

        DB::beginTransaction();
        try {
            $trxNumber = 'TRX-' . date('YmdHis') . '-' . rand(10, 99);
            $totalCostPrice = 0;
            $itemsData = [];

            foreach ($this->cart as $item) {
                $totalBaseQty = $item['quantity'] * $item['conversion_factor'];
                $itemCostPrice = $item['buy_price_base'] * $totalBaseQty;
                $totalCostPrice += $itemCostPrice;
                $itemProfit = $item['subtotal'] - $itemCostPrice;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'unit_name' => $item['unit_name'],
                    'quantity' => $item['quantity'],
                    'conversion_factor' => $item['conversion_factor'],
                    'total_base_quantity' => $totalBaseQty,
                    'price_per_unit' => $item['price'],
                    'cost_price_per_unit' => $item['buy_price_base'] * $item['conversion_factor'],
                    'subtotal' => $item['subtotal'],
                    'item_profit' => $itemProfit,
                ];

                // Decrement store stock in BranchStock
                $stock = BranchStock::firstOrCreate(
                    ['branch_id' => $this->selectedBranchId, 'product_id' => $item['product_id']],
                    ['store_stock' => 0, 'warehouse_stock' => 0]
                );
                $stock->decrement('store_stock', $totalBaseQty);

                // Check for low stock alert
                if ($stock->store_stock <= $stock->min_store_stock_alert) {
                    Notification::make()
                        ->title("⚠️ Peringatan Stok Menipis!")
                        ->body("Stok toko {$item['name']} tersisa {$stock->store_stock} base unit pada cabang ini!")
                        ->warning()
                        ->persistent()
                        ->send();
                }
            }

            $netProfit = $this->grandTotal - $totalCostPrice;
            $changeAmt = max(0, $effectivePaid - $this->grandTotal);

            $transaction = Transaction::create([
                'transaction_number' => $trxNumber,
                'branch_id' => $this->selectedBranchId,
                'user_id' => Auth::id(),
                'customer_name' => !empty(trim($this->customerName)) ? trim($this->customerName) : null,
                'total_amount' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'tax_amount' => 0,
                'grand_total' => $this->grandTotal,
                'paid_amount' => $effectivePaid,
                'change_amount' => $changeAmt,
                'total_cost_price' => $totalCostPrice,
                'net_profit' => $netProfit,
                'payment_method' => $finalPaymentMethod,
                'status' => 'completed',
            ]);

            foreach ($itemsData as $iData) {
                $transaction->items()->create($iData);
            }

            DB::commit();

            // Log Transaksi Sukses
            \App\Services\SystemLogger::logTransaction(
                $trxNumber,
                $transaction->grand_total,
                $finalPaymentMethod,
                count($this->cart),
                $this->customerName
            );

            $this->completedTransactionId = $transaction->id;
            $this->showReceiptModal = true;
            $this->clearCart();

            if ($this->autoPrintReceipt) {
                $this->dispatch('open-print-window', url: route('transactions.print', $transaction));
            }

            Notification::make()
                ->title('Transaksi Berhasil!')
                ->body("No: {$trxNumber} - Kembalian: Rp " . number_format($transaction->change_amount, 0, ',', '.'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            \App\Services\SystemLogger::logError('POS_CHECKOUT', $e->getMessage(), $e);
            Notification::make()
                ->title('Transaksi Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->completedTransactionId = null;
    }

    protected function getViewData(): array
    {
        return [
            'completedTransaction' => $this->completedTransaction,
        ];
    }
}
