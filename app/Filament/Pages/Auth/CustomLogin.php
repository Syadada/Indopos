<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->email()
            ->required()
            ->rules(['regex:/^.+@.+\..+$/'])
            ->validationMessages([
                'required' => 'Alamat email wajib diisi.',
                'email' => 'Email harus menyertakan tanda @ dan nama domain (contoh: admin@example.com).',
                'regex' => 'Format email tidak valid, pastikan menggunakan tanda @.',
            ])
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->validationMessages([
                'required' => 'Password wajib diisi.',
            ])
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function throwFailureValidationException(): never
    {
        $data = $this->form->getState();
        $email = $data['email'] ?? null;

        $userExists = $email ? User::where('email', $email)->exists() : false;

        if ($userExists) {
            throw ValidationException::withMessages([
                'data.password' => '⚠️ Password yang Anda masukkan salah.',
            ]);
        }

        throw ValidationException::withMessages([
            'data.email' => '⚠️ Email tidak terdaftar dalam sistem.',
        ]);
    }
}
