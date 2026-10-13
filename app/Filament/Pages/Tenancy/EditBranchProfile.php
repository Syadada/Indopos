<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditBranchProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Pengaturan Profil Cabang';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Cabang Toko')
                    ->required(),
                TextInput::make('subtitle')
                    ->label('Keterangan / Subtitle Struk')
                    ->placeholder('contoh: Toko plastik dan bahan kue')
                    ->helperText('Tampil di bawah nama toko pada nota struk'),
                TextInput::make('code')
                    ->label('Kode Cabang')
                    ->required(),
                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel(),
                Textarea::make('address')
                    ->label('Alamat Lengkap')
                    ->rows(3),
                Toggle::make('is_active')
                    ->label('Status Aktif'),
            ]);
    }
}
