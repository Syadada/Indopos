<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyPostingResource\Pages;
use App\Models\DailyPosting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DailyPostingResource extends Resource
{
    protected static ?string $model = DailyPosting::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Manajemen Penjualan';
    protected static ?string $navigationLabel = 'Data Master Posting Harian';
    protected static ?string $title = 'Data Master Posting Transaksi Harian';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('posting_code')
                            ->label('Kode Posting Master')
                            ->readOnly(),
                        Forms\Components\DatePicker::make('posting_date')
                            ->label('Tanggal Posting')
                            ->readOnly(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Waktu Eksekusi Posting (Tanggal & Jam)')
                            ->displayFormat('d M Y H:i:s')
                            ->readOnly(),
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_transactions_count')
                            ->label('Total Transaksi Selesai')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_items_sold')
                            ->label('Total Barang Keluar (Qty)')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_money_in')
                            ->label('Total Uang Masuk (Omset)')
                            ->prefix('Rp')
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_money_out')
                            ->label('Total Uang Keluar (Modal HPP)')
                            ->prefix('Rp')
                            ->readOnly(),
                        Forms\Components\TextInput::make('net_profit')
                            ->label('Profit Bersih (Keuntungan)')
                            ->prefix('Rp')
                            ->readOnly(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Posting')
                            ->readOnly()
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('📦 Rincian Barang Keluar & Profit Per Item')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('product_name')
                                    ->label('Nama Barang')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('transaction_times')
                                    ->label('Waktu Transaksi Barang (Jam)')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('total_qty_sold')
                                    ->label('Jumlah Keluar (Qty)')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('total_revenue')
                                    ->label('Uang Masuk (Subtotal Jual)')
                                    ->prefix('Rp')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('total_cost')
                                    ->label('Uang Keluar (Subtotal Modal)')
                                    ->prefix('Rp')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('net_profit')
                                    ->label('Profit Item')
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ])
                            ->columns(6)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('posting_code')
                    ->label('Kode Posting')
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('posting_date')
                    ->label('Tanggal Posting')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_transactions_count')
                    ->label('Transaksi Selesai')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_items_sold')
                    ->label('Barang Keluar')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.') . ' Pcs')
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_money_in')
                    ->label('Uang Masuk (Omset)')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_money_out')
                    ->label('Uang Keluar (HPP)')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_profit')
                    ->label('Profit Bersih')
                    ->money('IDR')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Posting')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('posting_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->label('Filter Cabang'),
                Tables\Filters\TrashedFilter::make()
                    ->label('Filter Status / Sampah'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('exportExcel')
                    ->label('📊 Export ke Excel & Soft Delete')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Export Excel & Pindahkan ke Sampah')
                    ->modalDescription('Laporan Data Master ini akan didownload dalam format Excel (.csv) dan otomatis di-Soft Delete. Anda dapat melihat atau merestore data ini kapan saja melalui filter Sampah.')
                    ->action(function (DailyPosting $record) {
                        $record->load(['branch', 'items']);

                        $filename = "Posting_Master_{$record->posting_code}.csv";
                        $headers = [
                            "Content-type" => "text/csv; charset=UTF-8",
                            "Content-Disposition" => "attachment; filename={$filename}",
                            "Pragma" => "no-cache",
                            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                            "Expires" => "0"
                        ];

                        $callback = function () use ($record) {
                            $file = fopen('php://output', 'w');
                            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

                            fputcsv($file, ['DATA MASTER POSTING TRANSAKSI HARIAN']);
                            fputcsv($file, ['Kode Posting', $record->posting_code]);
                            fputcsv($file, ['Tanggal Posting', $record->posting_date ? $record->posting_date->format('d/m/Y') : '-']);
                            fputcsv($file, ['Waktu Eksekusi Posting', $record->created_at ? $record->created_at->format('d/m/Y H:i:s') : '-']);
                            fputcsv($file, ['Cabang Toko', $record->branch->name ?? '-']);
                            fputcsv($file, ['Total Transaksi Selesai', $record->total_transactions_count]);
                            fputcsv($file, ['Total Barang Keluar (Qty)', $record->total_items_sold]);
                            fputcsv($file, ['Total Uang Masuk (Omset)', $record->total_money_in]);
                            fputcsv($file, ['Total Uang Keluar (Modal HPP)', $record->total_money_out]);
                            fputcsv($file, ['Profit Bersih', $record->net_profit]);
                            fputcsv($file, []);

                            fputcsv($file, ['RINCIAN BARANG KELUAR PER TRANSAKSI']);
                            fputcsv($file, ['No', 'Nama Barang', 'Waktu Transaksi Barang (Jam)', 'Qty Keluar', 'Uang Masuk (Subtotal Jual)', 'Uang Keluar (Subtotal Modal)', 'Profit Item']);

                            $no = 1;
                            foreach ($record->items as $item) {
                                fputcsv($file, [
                                    $no++,
                                    $item->product_name,
                                    $item->transaction_times ?? '-',
                                    $item->total_qty_sold,
                                    $item->total_revenue,
                                    $item->total_cost,
                                    $item->net_profit,
                                ]);
                            }

                            fclose($file);
                        };

                        // Perform soft delete
                        $record->delete();

                        Notification::make()
                            ->title('Export Excel Berhasil')
                            ->body("Data master {$record->posting_code} telah di-download dan dipindahkan ke Sampah (dapat di-restore kapan saja).")
                            ->warning()
                            ->send();

                        return response()->stream($callback, 200, $headers);
                    }),
                Tables\Actions\RestoreAction::make()
                    ->label('🔄 Restore / Pulihkan')
                    ->color('warning'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('❌ Hapus Permanen'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('🔄 Restore Selected (Pulihkan Terpilih)'),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyPostings::route('/'),
            'view' => Pages\ViewDailyPosting::route('/{record}'),
        ];
    }
}
