<?php

namespace App\Filament\Widgets;

use App\Models\BranchStock;
use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return ! (auth()->user()?->hasRole('kasir') ?? false);
    }

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        $todayRevenue = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->when($tenant, fn ($q) => $q->where('branch_id', $tenant->id))
            ->sum('grand_total');

        $todayProfit = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->when($tenant, fn ($q) => $q->where('branch_id', $tenant->id))
            ->sum('net_profit');

        $totalTransactions = Transaction::where('status', 'completed')
            ->when($tenant, fn ($q) => $q->where('branch_id', $tenant->id))
            ->count();

        // Low stock count (store stock <= min_store_stock_alert OR warehouse stock <= min_warehouse_stock_alert)
        $lowStockCount = BranchStock::when($tenant, fn ($q) => $q->where('branch_id', $tenant->id))
            ->where(function ($q) {
                $q->whereColumn('store_stock', '<=', 'min_store_stock_alert')
                  ->orWhereColumn('warehouse_stock', '<=', 'min_warehouse_stock_alert');
            })
            ->count();

        return [
            Stat::make('Keuntungan Hari Ini (Profit)', 'Rp ' . number_format($todayProfit, 0, ',', '.'))
                ->description('Net Profit dari Penjualan Hari Ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Omset Pendapatan', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description('Pendapatan Penjualan Hari Ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Total Transaksi Selesai', number_format($totalTransactions, 0, ',', '.'))
                ->description('Jumlah Struk Terpenuhi')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Barang Stok Menipis (Alert)', $lowStockCount . ' Item')
                ->description($lowStockCount > 0 ? 'Perlu Restok Segera di Toko/Gudang!' : 'Seluruh Stok Aman')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
        ];
    }
}
