<x-filament-panels::page>
    @php
        $data = $this->getPerformanceData();
    @endphp

    {{-- Top Consolidated KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Omset Nasional</div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">
                Rp {{ number_format($data['total_revenue'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Gabungan seluruh cabang toko</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Profit Bersih</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">
                Rp {{ number_format($data['total_profit'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Keuntungan bersih terakumulasi</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transaksi</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2">
                {{ number_format($data['total_transactions'], 0, ',', '.') }} Struk
            </div>
            <div class="text-xs text-gray-400 mt-1">Penjualan berhasil diselesaikan</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Cabang Aktif</div>
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                {{ $data['active_branches_count'] }} Lokasi Toko
            </div>
            <div class="text-xs text-gray-400 mt-1">Jaringan toko yang beroperasi</div>
        </div>
    </div>

    {{-- Branch Breakdown Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                🏆 Matriks Performa & Ranking Cabang Toko
            </h3>
            <span class="text-xs bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400 px-3 py-1 rounded-full font-medium">
                Super Admin Master Access
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-semibold uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Peringkat & Nama Cabang</th>
                        <th class="px-6 py-3">Kode Cabang</th>
                        <th class="px-6 py-3 text-right">Total Omset</th>
                        <th class="px-6 py-3 text-right">Profit Bersih</th>
                        <th class="px-6 py-3 text-center">Jumlah Transaksi</th>
                        <th class="px-6 py-3 text-right">Rata-Rata / Struk</th>
                        <th class="px-6 py-3 text-center">Stok Kritis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($data['branch_breakdown'] as $index => $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full inline-flex items-center justify-center text-xs font-bold {{ $loop->first ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $loop->iteration }}
                                </span>
                                {{ $item['name'] }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">
                                <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-800 dark:text-gray-200">
                                    {{ $item['code'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($item['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($item['profit'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center font-medium">
                                {{ number_format($item['transactions'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-xs text-gray-500">
                                Rp {{ number_format($item['avg_transaction'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item['low_stock_count'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300">
                                        ⚠️ {{ $item['low_stock_count'] }} Barang
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        ✅ Aman
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                Belum ada data transaksi di cabang manapun.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
