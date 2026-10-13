<?php

namespace App\Filament\Widgets;

use App\Models\BranchStock;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlertWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = '⚠️ Peringatan Restok: Stok Toko & Gudang Menipis';

    public static function canView(): bool
    {
        return ! (auth()->user()?->hasRole('kasir') ?? false);
    }

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();

        return $table
            ->query(
                BranchStock::query()
                    ->when($tenant, fn ($q) => $q->where('branch_id', $tenant->id))
                    ->where(function ($q) {
                        $q->whereColumn('store_stock', '<=', 'min_store_stock_alert')
                          ->orWhereColumn('warehouse_stock', '<=', 'min_warehouse_stock_alert');
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang Toko')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.barcode')
                    ->label('Barcode')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('store_stock')
                    ->label('Stok Toko')
                    ->formatStateUsing(fn ($record) => $record->store_stock . ' ' . ($record->product->baseUnit->short_name ?? 'Pcs'))
                    ->badge()
                    ->color(fn ($record) => $record->store_stock <= $record->min_store_stock_alert ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('min_store_stock_alert')
                    ->label('Min Alert Toko')
                    ->suffix(' Pcs'),
                Tables\Columns\TextColumn::make('warehouse_stock')
                    ->label('Stok Gudang')
                    ->formatStateUsing(fn ($record) => $record->warehouse_stock . ' ' . ($record->product->baseUnit->short_name ?? 'Pcs'))
                    ->badge()
                    ->color(fn ($record) => $record->warehouse_stock <= $record->min_warehouse_stock_alert ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('min_warehouse_stock_alert')
                    ->label('Min Alert Gudang')
                    ->suffix(' Pcs'),
            ])
            ->actions([
                Tables\Actions\Action::make('restock')
                    ->label('Isi Stok')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('add_store_stock')
                            ->label('Tambah Stok Toko (Base Unit)')
                            ->numeric()
                            ->default(50),
                        \Filament\Forms\Components\TextInput::make('add_warehouse_stock')
                            ->label('Tambah Stok Gudang (Base Unit)')
                            ->numeric()
                            ->default(100),
                    ])
                    ->action(function (BranchStock $record, array $data): void {
                        $record->increment('store_stock', (int) $data['add_store_stock']);
                        $record->increment('warehouse_stock', (int) $data['add_warehouse_stock']);

                        \Filament\Notifications\Notification::make()
                            ->title('Stok Berhasil Ditambahkan!')
                            ->body("Stok untuk {$record->product->name} di cabang {$record->branch->name} telah diupdate.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
