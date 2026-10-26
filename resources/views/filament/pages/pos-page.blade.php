<x-filament-panels::page>
    <div x-data="{
            barcodeBuffer: '',
            lastKeyTime: 0,
            init() {
                this.focusInput();
                document.addEventListener('livewire:initialized', () => {
                    Livewire.hook('commit', ({ succeed }) => {
                        succeed(() => {
                            setTimeout(() => this.focusInput(), 80);
                        });
                    });
                });
            },
            focusInput() {
                const el = document.getElementById('barcode-scanner-input');
                if (el && document.activeElement !== el && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                    el.focus();
                }
            },
            handleGlobalScanner(e) {
                // Do not intercept if cashier is typing in another field (e.g. customer name or search)
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) && e.target.id !== 'barcode-scanner-input') {
                    return;
                }
                const now = Date.now();
                if (now - this.lastKeyTime > 200) {
                    this.barcodeBuffer = '';
                }
                this.lastKeyTime = now;
                if (e.key === 'Enter') {
                    if (this.barcodeBuffer.length >= 3) {
                        e.preventDefault();
                        $wire.set('barcodeInput', this.barcodeBuffer);
                        $wire.scanBarcode();
                        this.barcodeBuffer = '';
                    }
                } else if (e.key.length === 1) {
                    this.barcodeBuffer += e.key;
                }
            }
        }" 
        @keydown.window="handleGlobalScanner($event)"
        x-on:open-print-window.window="window.open($event.detail.url, '_blank', 'width=450,height=650')" 
        class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT & MIDDLE: SCANNER BARCODE & KERANJANG -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- BARCODE SCANNER & BRANCH SELECTOR CARD -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Lokasi Cabang Toko</label>
                    <select wire:model.live="selectedBranchId" class="w-full text-sm font-semibold rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-primary-500">
                        @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-2/3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">⚡ Input / Scan Barcode Barang</label>
                    <div class="relative">
                        <input type="text" 
                               id="barcode-scanner-input"
                               wire:model="barcodeInput"
                               wire:keydown.enter="scanBarcode"
                               placeholder="Ketik angka atau scan barcode di sini..." 
                               autofocus
                               class="w-full pl-10 pr-24 py-2.5 text-base font-bold text-gray-900 dark:text-gray-100 bg-emerald-50/50 dark:bg-emerald-950/30 border-2 border-emerald-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <button type="button" wire:click="scanBarcode" class="absolute right-1 top-1 bottom-1 px-4 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition-colors">
                            SCAN
                        </button>
                    </div>

                    <!-- SIMULATOR SCANNER BARCODE CHIPS (UJI COBA TANPA ALAT) -->
                    <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold text-gray-500">🧪 Simulator Scan:</span>
                        <button type="button" wire:click="$set('barcodeInput', '8998866200011'); $wire.scanBarcode()" class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 hover:bg-emerald-100 text-gray-800 hover:text-emerald-800 rounded border border-gray-200 transition-colors">⚡ Indomie</button>
                        <button type="button" wire:click="$set('barcodeInput', '8998866200019'); $wire.scanBarcode()" class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 hover:bg-emerald-100 text-gray-800 hover:text-emerald-800 rounded border border-gray-200 transition-colors">⚡ Mie Sedaap</button>
                        <button type="button" wire:click="$set('barcodeInput', '8998866200017'); $wire.scanBarcode()" class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 hover:bg-emerald-100 text-gray-800 hover:text-emerald-800 rounded border border-gray-200 transition-colors">⚡ Pop Mie</button>
                        <button type="button" wire:click="$set('barcodeInput', '8999908000101'); $wire.scanBarcode()" class="px-2 py-0.5 text-[10px] font-bold bg-yellow-50 hover:bg-yellow-100 text-yellow-800 rounded border border-yellow-200 transition-colors">⚡ Neo Rheumacyl (Sembako)</button>
                    </div>
                </div>
            </div>

            <!-- QUICK PRODUCT SEARCH GRID -->
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">📦 Quick Search Barang</h3>
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari nama barang..." class="text-xs rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-1.5 w-48">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-h-44 overflow-y-auto pr-1">
                    @php
                        $productsQuery = \App\Models\Product::where('is_active', true)->with(['baseUnit', 'unitConversions.unit']);
                        if(!empty($searchQuery)) {
                            $term = mb_strtolower(trim($searchQuery));
                            $productsQuery->where(function($q) use ($term) {
                                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $term . '%'])
                                  ->orWhereRaw('LOWER(barcode) LIKE ?', ['%' . $term . '%'])
                                  ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . $term . '%']);
                            });
                        }
                        $products = $productsQuery->take(8)->get();
                    @endphp
                    @foreach($products as $prod)
                        @php
                            $bStock = \App\Models\BranchStock::where('branch_id', $selectedBranchId)->where('product_id', $prod->id)->first();
                            $stk = $bStock ? $bStock->store_stock : 0;
                        @endphp
                        <button type="button" 
                                wire:click="addToCart({{ $prod->id }})" 
                                class="p-2.5 bg-gray-50 dark:bg-gray-700/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-gray-200 dark:border-gray-600 hover:border-emerald-500 rounded-lg text-left transition-all">
                            <div class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate">{{ $prod->name }}</div>
                            <div class="flex items-center justify-between mt-1 text-[11px]">
                                <span class="font-semibold text-emerald-600">Rp {{ number_format($prod->sell_price, 0, ',', '.') }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $stk <= 5 ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200' }}">
                                    Stok: {{ $stk }}
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- CART ITEMS TABLE -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        🛒 Keranjang Belanja <span class="px-2 py-0.5 text-xs bg-emerald-100 text-emerald-800 rounded-full font-bold">{{ count($cart) }} Item</span>
                    </h3>
                    @if(count($cart) > 0)
                        <button type="button" wire:click="clearCart" class="text-xs text-red-600 hover:text-red-700 font-semibold">Bersihkan Keranjang</button>
                    @endif
                </div>

                @if(count($cart) === 0)
                    <div class="p-12 text-center text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                        <p class="mt-2 text-sm">Keranjang belanja masih kosong.</p>
                        <p class="text-xs text-gray-400">Scan barcode atau klik item pada Quick Search di atas.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold border-b border-gray-200 dark:border-gray-600">
                                    <th class="py-2.5 px-3">Barang & Barcode</th>
                                    <th class="py-2.5 px-3 text-center">Jenis Satuan Unit</th>
                                    <th class="py-2.5 px-3 text-center">Harga Satuan</th>
                                    <th class="py-2.5 px-3 text-center">Jumlah</th>
                                    <th class="py-2.5 px-3 text-right">Subtotal</th>
                                    <th class="py-2.5 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($cart as $key => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="py-2.5 px-3">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ $item['name'] }}</div>
                                            <div class="text-[10px] font-mono text-gray-400">BC: {{ $item['barcode'] }}</div>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <select wire:change="changeUnit('{{ $key }}', $event.target.value)" class="text-xs py-1 px-2 font-semibold rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                                @foreach($item['available_units'] as $unitOpt)
                                                    <option value="{{ $unitOpt['id'] }}" {{ $item['unit_id'] == $unitOpt['id'] ? 'selected' : '' }}>
                                                        {{ $unitOpt['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-semibold text-gray-700 dark:text-gray-300">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <div class="inline-flex items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
                                                <button type="button" wire:click="updateQuantity('{{ $key }}', -1)" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 font-extrabold text-gray-700 dark:text-gray-200 text-xs transition-colors">-</button>
                                                <input type="number" 
                                                       min="1" 
                                                       value="{{ $item['quantity'] }}" 
                                                       wire:change="setQuantity('{{ $key }}', $event.target.value)" 
                                                       wire:keydown.enter="setQuantity('{{ $key }}', $event.target.value)" 
                                                       class="w-12 text-center text-xs font-bold text-gray-900 dark:text-gray-100 border-none p-1 focus:ring-1 focus:ring-emerald-500 focus:outline-none bg-transparent [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                                <button type="button" wire:click="updateQuantity('{{ $key }}', 1)" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 font-extrabold text-gray-700 dark:text-gray-200 text-xs transition-colors">+</button>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <button type="button" wire:click="removeFromCart('{{ $key }}')" class="p-1 text-red-500 hover:text-red-700 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-100 dark:bg-gray-700/80 font-bold border-t-2 border-gray-300 dark:border-gray-600 text-xs">
                                    <td colspan="3" class="py-3 px-3 text-right text-gray-700 dark:text-gray-200">Total Harga Barang (Subtotal):</td>
                                    <td class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400 font-extrabold">
                                        {{ array_sum(array_column($cart, 'quantity')) }} Item
                                    </td>
                                    <td class="py-3 px-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">
                                        Rp {{ number_format($this->subtotal, 0, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        <!-- RIGHT: CHECKOUT & PAYMENT PANEL -->
        <div class="space-y-6">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 border-b pb-3 flex items-center justify-between">
                    <span>💳 Ringkasan Pembayaran</span>
                    <span class="text-xs font-normal text-emerald-600 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-full border border-emerald-200">Indomaret POS Mode</span>
                </h3>

                <!-- SUBTOTAL & GRAND TOTAL DISPLAY -->
                <div class="bg-gray-900 dark:bg-gray-950 text-white p-4 rounded-xl space-y-2 shadow-inner text-right">
                    <div class="flex justify-between items-center text-xs text-gray-400 border-b border-gray-800 pb-2">
                        <span class="uppercase font-semibold">Total Harga Barang (Subtotal):</span>
                        <span class="font-mono font-bold text-emerald-300 text-sm">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 uppercase font-semibold">Total Tagihan (Grand Total)</div>
                        <div class="text-3xl font-extrabold text-emerald-400 font-mono tracking-tight">
                            Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Nama Pelanggan (Opsional / Yg Yth)</label>
                        <input type="text"
                               wire:model="customerName"
                               placeholder="contoh: Amira Desi / Ibu Susi (Boleh kosong)"
                               class="w-full text-sm font-semibold rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Diskon (Rp)</label>
                        <input type="text"
                               x-data="{
                                   get formatted() {
                                       return $wire.discountAmount ? $wire.discountAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                                   }
                               }"
                               x-bind:value="formatted"
                               x-on:input="
                                   let raw = $event.target.value.replace(/[^0-9]/g, '');
                                   $event.target.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                   $wire.set('discountAmount', raw ? parseInt(raw) : 0);
                               "
                               placeholder="0"
                               class="w-full text-sm font-semibold rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                    </div>

                    <!-- TOGGLE SPLIT PAYMENT -->
                    <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/60 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div>
                            <div class="text-xs font-bold text-gray-800 dark:text-gray-200">🔀 Mode Pembayaran Kombinasi</div>
                            <div class="text-[10px] text-gray-500">Bayar pakai multiple metode (misal QRIS + Cash)</div>
                        </div>
                        <button type="button" 
                                wire:click="toggleSplitPayment" 
                                class="px-3 py-1 text-xs font-bold rounded-lg transition-all {{ $isSplitPayment ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200' }}">
                            {{ $isSplitPayment ? '✓ Kombinasi Aktif' : '+ Aktifkan' }}
                        </button>
                    </div>

                    @if(!$isSplitPayment)
                        <!-- SINGLE PAYMENT MODE -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Metode Pembayaran</label>
                            <div class="grid grid-cols-4 gap-1">
                                @foreach(['Cash', 'QRIS', 'Transfer', 'Debit'] as $method)
                                    <button type="button" 
                                            wire:click="$set('paymentMethod', '{{ $method }}')"
                                            class="py-2 text-xs font-bold rounded-lg border transition-all {{ $paymentMethod === $method ? 'bg-primary-600 text-white border-primary-600 shadow-sm' : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600' }}">
                                        {{ $method }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Nominal Uang Bayar (Rp)</label>
                            <input type="text"
                                   x-data="{
                                       get formatted() {
                                           return $wire.paidAmount ? $wire.paidAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                                       }
                                   }"
                                   x-bind:value="formatted"
                                   x-on:input="
                                       let raw = $event.target.value.replace(/[^0-9]/g, '');
                                       $event.target.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                       $wire.set('paidAmount', raw ? parseInt(raw) : 0);
                                   "
                                   placeholder="contoh: 100.000"
                                   class="w-full text-lg font-bold rounded-lg border-gray-300 dark:border-gray-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-mono">
                        </div>

                        <!-- QUICK MONEY BUTTONS -->
                        <div class="grid grid-cols-4 gap-1.5 text-center">
                            <button type="button" wire:click="$set('paidAmount', {{ $this->grandTotal }})" class="py-1.5 text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">Uang Pas</button>
                            <button type="button" wire:click="$set('paidAmount', 50000)" class="py-1.5 text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">50.000</button>
                            <button type="button" wire:click="$set('paidAmount', 100000)" class="py-1.5 text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">100.000</button>
                            <button type="button" wire:click="$set('paidAmount', 200000)" class="py-1.5 text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">200.000</button>
                        </div>
                    @else
                        <!-- SPLIT PAYMENT MULTI-METHOD BUILDER MODE -->
                        <div class="space-y-2.5 p-3 bg-amber-50/40 dark:bg-amber-950/20 rounded-xl border border-amber-200 dark:border-amber-900/50">
                            <div class="flex justify-between items-center text-xs font-bold text-amber-800 dark:text-amber-300">
                                <span>Rincian Pembayaran Kombinasi:</span>
                                <button type="button" wire:click="addSplitPaymentRow" class="text-[11px] bg-amber-600 hover:bg-amber-700 text-white px-2 py-0.5 rounded font-bold">
                                    + Tambah Metode
                                </button>
                            </div>

                            @foreach($splitPayments as $idx => $sp)
                                <div class="flex items-center gap-2">
                                    <select wire:model="splitPayments.{{ $idx }}.method" class="w-1/3 text-xs font-semibold rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                                        @foreach(['QRIS', 'Cash', 'Transfer', 'Debit'] as $mOpt)
                                            <option value="{{ $mOpt }}">{{ $mOpt }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text"
                                           x-data="{
                                               get formatted() {
                                                   let val = $wire.splitPayments[{{ $idx }}]['amount'];
                                                   return val ? val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                                               }
                                           }"
                                           x-bind:value="formatted"
                                           x-on:input="
                                               let raw = $event.target.value.replace(/[^0-9]/g, '');
                                               $event.target.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                               $wire.set('splitPayments.{{ $idx }}.amount', raw ? parseInt(raw) : 0);
                                           "
                                           placeholder="Nominal Rp (misal 50.000)" 
                                           class="w-2/3 text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-mono">
                                    @if(count($splitPayments) > 1)
                                        <button type="button" wire:click="removeSplitPaymentRow({{ $idx }})" class="text-red-500 hover:text-red-700 text-sm font-bold px-1">&times;</button>
                                    @endif
                                </div>
                            @endforeach

                            <div class="pt-2 text-xs font-bold flex justify-between border-t border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200">
                                <span>Total Bayar Masuk:</span>
                                <span class="font-mono">Rp {{ number_format($this->effectivePaidAmount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- STATUSTEXT: KEMBALIAN / KURANG -->
                    @if($this->effectivePaidAmount < $this->grandTotal)
                        <div class="p-3 bg-red-50 dark:bg-red-950/40 rounded-lg flex justify-between items-center border border-red-200 dark:border-red-800">
                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">Sisa Tagihan (Kurang):</span>
                            <span class="text-base font-extrabold text-red-600 dark:text-red-400 font-mono">
                                Rp {{ number_format($this->remainingAmount, 0, ',', '.') }}
                            </span>
                        </div>
                    @else
                        <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-lg flex justify-between items-center border border-blue-200 dark:border-blue-800">
                            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">Kembalian:</span>
                            <span class="text-base font-extrabold text-blue-600 dark:text-blue-400 font-mono">
                                Rp {{ number_format($this->changeAmount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center gap-2 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="autoPrintReceipt" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            ⚡ Otomatis Cetak Struk Thermal
                        </label>
                    </div>

                    <div class="pt-2">
                        @if(count($cart) > 0)
                            <button type="button" 
                                    wire:click="processTransaction" 
                                    style="width: 100%; min-height: 54px; padding: 14px 20px; background: linear-gradient(135deg, #059669, #047857); color: #ffffff !important; font-weight: 800; font-size: 15px; border-radius: 12px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 15px rgba(5, 150, 105, 0.4); text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s;"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(5, 150, 105, 0.5)';"
                                    onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(5, 150, 105, 0.4)';">
                                <span style="font-size: 22px;">💳</span>
                                <span>SELESAIKAN PEMBAYARAN (Rp {{ number_format($this->grandTotal, 0, ',', '.') }})</span>
                            </button>
                        @else
                            <button type="button" 
                                    disabled
                                    style="width: 100%; min-height: 54px; padding: 14px 20px; background: #f1f5f9; color: #64748b !important; font-weight: 700; font-size: 14px; border-radius: 12px; border: 2px dashed #cbd5e1; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <span style="font-size: 20px;">🛒</span>
                                <span>Keranjang Kosong - Silakan Scan / Tambah Barang</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

    </div>

    <!-- RECEIPT POPUP MODAL (TAMPIL SETELAH PEMBAYARAN SUKSES) -->
    @if($showReceiptModal && $this->completedTransaction)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <div>
                        <h3 class="font-bold text-lg text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                            ✅ Pembayaran Berhasil!
                        </h3>
                        <p class="text-xs text-gray-500">Transaksi #{{ $this->completedTransaction->transaction_number }} telah tersimpan.</p>
                    </div>
                    <button type="button" wire:click="closeReceiptModal" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
                </div>

                <!-- NOTA RECEIPT SUMMARY -->
                @include('filament.pages.receipt-modal', ['transaction' => $this->completedTransaction])

                <!-- TOMBOL OPSI CETAK STRUK / STRUK BARU -->
                <div class="flex flex-col gap-2 pt-2 border-t">
                    <button type="button" 
                            onclick="window.open('/transactions/{{ $this->completedTransaction->id }}/print', '_blank', 'width=450,height=650')" 
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-md hover:shadow-emerald-600/30 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        🖨️ Cetak Struk Nota (POS Thermal)
                    </button>
                    <div class="flex gap-2">
                        <button type="button" onclick="window.print()" class="w-1/2 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs">
                            🖨️ Cetak Langsung
                        </button>
                        <button type="button" wire:click="closeReceiptModal" class="w-1/2 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold rounded-lg text-xs">
                            ➕ Transaksi Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
