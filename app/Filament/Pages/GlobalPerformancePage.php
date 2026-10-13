<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GlobalPerformancePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Laporan & Analisis';
    protected static ?string $navigationLabel = '📊 Performa Seluruh Cabang';
    protected static ?string $title = '📊 Analisis & Performa Seluruh Cabang Toko';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.global-performance-page';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getPerformanceData(): array
    {
        $branches = Branch::all();

        $totalRevenue = Transaction::where('status', 'completed')->sum('grand_total');
        $totalProfit = Transaction::where('status', 'completed')->sum('net_profit');
        $totalTransactions = Transaction::where('status', 'completed')->count();

        $branchBreakdown = $branches->map(function ($branch) {
            $revenue = Transaction::where('branch_id', $branch->id)->where('status', 'completed')->sum('grand_total');
            $profit = Transaction::where('branch_id', $branch->id)->where('status', 'completed')->sum('net_profit');
            $txCount = Transaction::where('branch_id', $branch->id)->where('status', 'completed')->count();
            $avgTx = $txCount > 0 ? ($revenue / $txCount) : 0;
            $lowStockCount = BranchStock::where('branch_id', $branch->id)
                ->where(function ($q) {
                    $q->whereColumn('store_stock', '<=', 'min_store_stock_alert')
                      ->orWhereColumn('warehouse_stock', '<=', 'min_warehouse_stock_alert');
                })->count();

            return [
                'name' => $branch->name,
                'code' => $branch->code,
                'revenue' => $revenue,
                'profit' => $profit,
                'transactions' => $txCount,
                'avg_transaction' => $avgTx,
                'low_stock_count' => $lowStockCount,
            ];
        })->sortByDesc('revenue');

        return [
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'total_transactions' => $totalTransactions,
            'active_branches_count' => $branches->count(),
            'branch_breakdown' => $branchBreakdown,
        ];
    }
}
