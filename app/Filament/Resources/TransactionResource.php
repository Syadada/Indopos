<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Manajemen Penjualan';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('transaction_number')
                            ->label('Nomor Transaksi')
                            ->readOnly(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Waktu Transaksi (Tanggal & Jam)')
                            ->displayFormat('d M Y H:i:s')
                            ->readOnly(),
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->label('Kasir')
                            ->relationship('user', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Pelanggan / Customer')
                            ->readOnly()
                            ->default('Umum / Cash'),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Harga Barang (Subtotal)')
                            ->prefix('Rp')
                            ->readOnly(),
                        Forms\Components\TextInput::make('grand_total')
                            ->label('Total Transaksi (Grand Total)')
                            ->prefix('Rp')
                            ->readOnly(),
                        Forms\Components\TextInput::make('net_profit')
                            ->label('Keuntungan Bersih (Profit)')
                            ->prefix('Rp')
                            ->readOnly(),
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('product.name')
                                    ->label('Nama Barang')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('unit_name')
                                    ->label('Satuan')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('price_per_unit')
                                    ->label('Harga per Satuan')
                                    ->prefix('Rp')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ])
                            ->columns(5)
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->default('System'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_profit')
                    ->label('Profit')
                    ->money('IDR')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Transaksi')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->label('Filter Cabang'),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'Cash' => 'Cash',
                        'QRIS' => 'QRIS',
                        'Transfer' => 'Transfer',
                        'Debit' => 'Debit',
                    ])
                    ->label('Metode Bayar'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('printReceipt')
                    ->label('Cetak Struk Thermal')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(fn ($record) => route('transactions.print', $record))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $tenant = \Filament\Facades\Filament::getTenant();

        return parent::getEloquentQuery()
            ->when($tenant, fn ($query) => $query->where('branch_id', $tenant->id));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }
}
