<?php

namespace App\Filament\Resources\DailyPostingResource\Pages;

use App\Filament\Resources\DailyPostingResource;
use App\Models\Branch;
use App\Models\DailyPosting;
use App\Services\DailyPostingService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListDailyPostings extends ListRecords
{
    protected static string $resource = DailyPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportRange')
                ->label('📊 Export Excel Rentang Waktu (7 / 14 / 30 Hari)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('range_days')
                        ->label('Pilih Rentang Waktu Laporan')
                        ->options([
                            '7' => '7 Hari Terakhir',
                            '14' => '14 Hari Terakhir',
                            '30' => '1 Bulan Terakhir (30 Hari)',
                        ])
                        ->default('7')
                        ->required(),
                    Forms\Components\Select::make('branch_id')
                        ->label('Filter Cabang (Opsional)')
                        ->options(Branch::all()->pluck('name', 'id'))
                        ->placeholder('Semua Cabang Toko'),
                ])
                ->action(function (array $data) {
                    $days = (int) $data['range_days'];
                    $startDate = now()->subDays($days - 1)->startOfDay();
                    $endDate = now()->endOfDay();
                    $branchId = $data['branch_id'] ?? null;

                    $query = DailyPosting::whereBetween('posting_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->with(['branch', 'items']);

                    $postings = $query->get();

                    if ($postings->isEmpty()) {
                        Notification::make()
                            ->title('Data Tidak Ditemukan')
                            ->body("Tidak ada data master posting dalam rentang {$days} hari terakhir.")
                            ->warning()
                            ->send();
                        return;
                    }

                    $rangeLabel = "{$days}Hari";
                    $filename = "Laporan_Master_Posting_{$rangeLabel}_" . date('Ymd_His') . ".csv";

                    $headers = [
                        "Content-type" => "text/csv; charset=UTF-8",
                        "Content-Disposition" => "attachment; filename={$filename}",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    ];

                    $callback = function () use ($postings, $days) {
                        $file = fopen('php://output', 'w');
                        fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

                        fputcsv($file, ["LAPORAN MASTER POSTING TRANSAKSI HARIAN ({$days} HARI TERAKHIR)"]);
                        fputcsv($file, ["Tanggal Cetak", date('d/m/Y H:i:s')]);
                        fputcsv($file, ["Total Hari Terarsip", $postings->count() . " Hari/Batch"]);
                        fputcsv($file, ["Total Omset (Uang Masuk)", $postings->sum('total_money_in')]);
                        fputcsv($file, ["Total Modal (Uang Keluar)", $postings->sum('total_money_out')]);
                        fputcsv($file, ["Total Profit Bersih", $postings->sum('net_profit')]);
                        fputcsv($file, []);

                        fputcsv($file, ['RINGKASAN HARIAN']);
                        fputcsv($file, ['No', 'Kode Posting', 'Tanggal Posting', 'Cabang', 'Transaksi Selesai', 'Barang Keluar (Qty)', 'Uang Masuk (Omset)', 'Uang Keluar (HPP)', 'Profit Bersih']);

                        $no = 1;
                        foreach ($postings as $p) {
                            fputcsv($file, [
                                $no++,
                                $p->posting_code,
                                $p->posting_date ? $p->posting_date->format('d/m/Y') : '-',
                                $p->branch->name ?? '-',
                                $p->total_transactions_count,
                                $p->total_items_sold,
                                $p->total_money_in,
                                $p->total_money_out,
                                $p->net_profit,
                            ]);
                        }

                        fputcsv($file, []);
                        fputcsv($file, ['RINCIAN DETAIL BARANG KELUAR PER KODE POSTING']);
                        fputcsv($file, ['Kode Posting', 'Tanggal', 'Nama Barang', 'Waktu Transaksi Barang (Jam)', 'Qty Keluar', 'Uang Masuk (Subtotal Jual)', 'Uang Keluar (Subtotal Modal)', 'Profit Item']);

                        foreach ($postings as $p) {
                            foreach ($p->items as $item) {
                                fputcsv($file, [
                                    $p->posting_code,
                                    $p->posting_date ? $p->posting_date->format('d/m/Y') : '-',
                                    $item->product_name,
                                    $item->transaction_times ?? '-',
                                    $item->total_qty_sold,
                                    $item->total_revenue,
                                    $item->total_cost,
                                    $item->net_profit,
                                ]);
                            }
                        }

                        fclose($file);
                    };

                    // Soft delete exported postings
                    foreach ($postings as $p) {
                        $p->delete();
                    }

                    Notification::make()
                        ->title('Export Excel Berhasil!')
                        ->body("Data master {$days} hari terakhir berhasil di-export ke Excel dan dipindahkan ke Sampah (dapat di-restore).")
                        ->warning()
                        ->send();

                    return response()->stream($callback, 200, $headers);
                }),

            Actions\Action::make('purgeOldTrash')
                ->label('🧹 Bersihkan Sampah > 30 Hari')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Purge Hapus Permanen Data Sampah > 30 Hari')
                ->modalDescription('Data master posting yang sudah di-Soft Delete lebih dari 30 hari akan DIHAPUS PERMANEN dari database secara otomatis.')
                ->action(function () {
                    Artisan::call('pos:purge-trashed', ['--days' => 30]);

                    Notification::make()
                        ->title('Pembersihan Sampah Selesai')
                        ->body('Seluruh data master soft-deleted yang berusia lebih dari 30 hari telah dihapus permanen.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('postDaily')
                ->label('⚙️ Posting Transaksi Harian')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('branch_id')
                        ->label('Pilih Cabang Toko')
                        ->options(Branch::where('is_active', true)->pluck('name', 'id'))
                        ->default(fn () => Branch::where('is_active', true)->first()?->id)
                        ->required(),
                    Forms\Components\DatePicker::make('posting_date')
                        ->label('Tanggal Posting Transaksi')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $dateStr = date('Y-m-d', strtotime($data['posting_date']));
                    $branchId = $data['branch_id'];

                    $posting = DailyPostingService::postForDate($branchId, $dateStr);

                    Notification::make()
                        ->title('Posting Transaksi Harian Berhasil!')
                        ->body("Data master posting {$posting->posting_code} berhasil dibuat. Uang Masuk: Rp " . number_format($posting->total_money_in, 0, ',', '.') . " | Uang Keluar: Rp " . number_format($posting->total_money_out, 0, ',', '.'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
