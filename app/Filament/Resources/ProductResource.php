<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Manajemen Stok & Produk';
    protected static ?string $navigationLabel = 'Katalog Barang';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('ProductTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Utama')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Barang')
                                            ->required()
                                            ->placeholder('contoh: Indomie Goreng 85g')
                                            ->extraInputAttributes([
                                                'onkeydown' => 'if(event.key === "Enter"){ event.preventDefault(); return false; }'
                                            ]),
                                        Forms\Components\TextInput::make('barcode')
                                            ->label('Kode Barcode')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('Scan barcode atau ketik di sini...')
                                            ->extraInputAttributes([
                                                'onkeydown' => 'if(event.key === "Enter"){ event.preventDefault(); return false; }'
                                            ])
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                if (empty($state)) return;
                                                $catalog = \App\Models\BarcodeCatalog::findOrFetchOnline(trim($state));
                                                if ($catalog) {
                                                    if (empty($get('name'))) {
                                                        $set('name', $catalog->name);
                                                    }
                                                    \Filament\Notifications\Notification::make()
                                                        ->title('✨ Data Ditemukan di Master Barcode!')
                                                        ->body("Nama '{$catalog->name}' otomatis terisi. Silakan tentukan harga beli & harga jual toko Anda.")
                                                        ->success()
                                                        ->send();
                                                } else {
                                                    \Filament\Notifications\Notification::make()
                                                        ->title('⚠️ Data Tidak Ada di Database')
                                                        ->body("Barcode '{$state}' belum terdaftar di database. Silakan isi nama barang & harga secara manual.")
                                                        ->warning()
                                                        ->send();
                                                }
                                            })
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('searchCatalog')
                                                    ->label('Cari Master Barcode')
                                                    ->icon('heroicon-o-magnifying-glass')
                                                    ->modalHeading('🔎 Cari Barang dari Master Barcode Catalog')
                                                    ->modalDescription('Cari nama atau barcode produk dari 1.000+ database barcode sembako Indonesia')
                                                    ->form([
                                                        Forms\Components\Select::make('catalog_id')
                                                            ->label('Pilih Barang dari Catalog')
                                                            ->options(function () {
                                                                return \App\Models\BarcodeCatalog::limit(100)->pluck('name', 'id');
                                                            })
                                                            ->getSearchResultsUsing(function (string $search) {
                                                                $term = mb_strtolower(trim($search));
                                                                return \App\Models\BarcodeCatalog::whereRaw('LOWER(name) LIKE ?', ['%' . $term . '%'])
                                                                    ->orWhere('barcode', 'LIKE', '%' . $term . '%')
                                                                    ->limit(50)
                                                                    ->get()
                                                                    ->pluck('name', 'id');
                                                            })
                                                            ->searchable()
                                                            ->required(),
                                                    ])
                                                    ->action(function (array $data, Forms\Set $set) {
                                                        $catalog = \App\Models\BarcodeCatalog::find($data['catalog_id']);
                                                        if ($catalog) {
                                                            $set('barcode', $catalog->barcode);
                                                            $set('name', $catalog->name);

                                                            \Filament\Notifications\Notification::make()
                                                                ->title('Data Master Diterapkan')
                                                                ->body("Barcode & Nama '{$catalog->name}' berhasil terisi ke form.")
                                                                ->success()
                                                                ->send();
                                                        }
                                                    })
                                            )
                                            ->hintAction(
                                                Forms\Components\Actions\Action::make('generateBarcode')
                                                    ->label('Acak Barcode')
                                                    ->icon('heroicon-o-sparkles')
                                                    ->action(fn (Forms\Set $set) => $set('barcode', '899' . rand(100000000, 999999999)))
                                            ),
                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU (Kode Internal)')
                                            ->placeholder('contoh: IND-GRG-85')
                                            ->extraInputAttributes([
                                                'onkeydown' => 'if(event.key === "Enter"){ event.preventDefault(); return false; }'
                                            ]),
                                    ]),
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Kategori')
                                            ->relationship('category', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\Select::make('base_unit_id')
                                            ->label('Satuan Dasar (Base Unit)')
                                            ->relationship('baseUnit', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Satuan terkecil (misal: Pcs, Botol)'),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Deskripsi Barang')
                                            ->rows(1),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('buy_price')
                                            ->label('Harga Beli (Modal per Satuan Dasar)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(),
                                        Forms\Components\TextInput::make('sell_price')
                                            ->label('Harga Jual (per Satuan Dasar)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(),
                                    ]),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Status Dijual')
                                    ->default(true),
                            ]),

                        Forms\Components\Tabs\Tab::make('Multi-Satuan & Konversi')
                            ->icon('heroicon-o-scale')
                            ->schema([
                                Forms\Components\Repeater::make('unitConversions')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('unit_id')
                                            ->label('Satuan Besar')
                                            ->options(Unit::all()->pluck('name', 'id'))
                                            ->required()
                                            ->searchable(),
                                        Forms\Components\TextInput::make('conversion_factor')
                                            ->label('Isi (Jumlah Satuan Dasar)')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->helperText('misal: 1 Pack = 5 Pcs (Isi: 5)'),
                                        Forms\Components\TextInput::make('sell_price')
                                            ->label('Harga Jual Satuan Ini')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->helperText('Kosongkan untuk otomatis perkalian harga base unit'),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->createItemButtonLabel('Tambah Konversi Satuan (Pack / Box)'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Stok Toko & Gudang per Cabang')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Forms\Components\Repeater::make('branchStocks')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('branch_id')
                                            ->label('Cabang Toko')
                                            ->options(Branch::all()->pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->distinct(),
                                        Forms\Components\TextInput::make('store_stock')
                                            ->label('Stok di Toko (Base Unit)')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                        Forms\Components\TextInput::make('warehouse_stock')
                                            ->label('Stok di Gudang (Base Unit)')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                        Forms\Components\TextInput::make('min_store_stock_alert')
                                            ->label('Batas Warning Stok Toko')
                                            ->numeric()
                                            ->default(10)
                                            ->required()
                                            ->helperText('Beri notifikasi jika stok toko di bawah angka ini'),
                                        Forms\Components\TextInput::make('min_warehouse_stock_alert')
                                            ->label('Batas Warning Stok Gudang')
                                            ->numeric()
                                            ->default(20)
                                            ->required()
                                            ->helperText('Beri notifikasi jika stok gudang di bawah angka ini'),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(1)
                                    ->createItemButtonLabel('Atur Stok di Cabang Lain'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode')
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('baseUnit.short_name')
                    ->label('Satuan')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('buy_price')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sell_price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unitConversions_count')
                    ->label('Satuan Lain')
                    ->counts('unitConversions')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "+$state Satuan" : '1 Satuan')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Filter Kategori'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Data Tidak Ada di Database')
            ->emptyStateDescription('Barcode atau nama barang yang Anda cari tidak ditemukan di dalam katalog toko.')
            ->emptyStateIcon('heroicon-o-exclamation-triangle');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
