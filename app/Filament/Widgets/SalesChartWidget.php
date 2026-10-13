<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected static ?string $heading = '📈 Grafik Pendapatan & Keuntungan (7 Hari Terakhir)';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return ! (auth()->user()?->hasRole('kasir') ?? false);
    }

    protected function getData(): array
    {
        $tenant = Filament::getTenant();
        $days = collect(range(6, 0))->map(fn ($i) => Carbon::now()->subDays($i)->format('Y-m-d'));

        $revenueData = [];
        $profitData = [];
        $labels = [];

        foreach ($days as $day) {
            $revenue = Transaction::whereDate('created_at', $day)
                ->where('status', 'completed')
                ->when($tenant, fn ($q) => $q->where('branch_id', $tenant->id))
                ->sum('grand_total');

            $profit = Transaction::whereDate('created_at', $day)
                ->where('status', 'completed')
                ->when($tenant, fn ($q) => $q->where('branch_id', $tenant->id))
                ->sum('net_profit');

            $revenueData[] = (float) $revenue;
            $profitData[] = (float) $profit;
            $labels[] = Carbon::parse($day)->translatedFormat('d M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan Omset (Rp)',
                    'data' => $revenueData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Keuntungan Bersih (Profit Rp)',
                    'data' => $profitData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
