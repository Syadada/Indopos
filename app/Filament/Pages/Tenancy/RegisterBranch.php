<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Branch;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterBranch extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Tambah / Daftar Cabang Toko Baru';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Cabang Toko')
                    ->required()
                    ->placeholder('contoh: AMIRA DESI'),
                TextInput::make('subtitle')
                    ->label('Keterangan / Subtitle Struk')
                    ->placeholder('contoh: Toko plastik dan bahan kue')
                    ->helperText('Tampil di bawah nama toko pada header nota struk'),
                TextInput::make('code')
                    ->label('Kode Cabang (Unik)')
                    ->required()
                    ->unique(Branch::class, 'code')
                    ->placeholder('contoh: SPA-01'),
                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel(),
                Textarea::make('address')
                    ->label('Alamat Lengkap Cabang')
                    ->rows(3),
                Toggle::make('is_active')
                    ->label('Status Aktif Cabang')
                    ->default(true),
            ]);
    }

    protected function handleRegistration(array $data): Branch
    {
        $branch = Branch::create($data);

        $branch->members()->attach(auth()->user());

        return $branch;
    }
}
