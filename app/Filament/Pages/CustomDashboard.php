<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;

class CustomDashboard extends BaseDashboard
{
    public function mount(): void
    {
        $user = auth()->user();

        if ($user && $user->hasRole('kasir')) {
            $tenant = Filament::getTenant();
            $tenantCode = $tenant ? $tenant->code : ($user->branch?->code ?? 'JKT01');
            redirect()->to("/admin/{$tenantCode}/pos-page");
            return;
        }
    }

    public static function canAccess(): bool
    {
        return ! (auth()->user()?->hasRole('kasir') ?? false);
    }
}
