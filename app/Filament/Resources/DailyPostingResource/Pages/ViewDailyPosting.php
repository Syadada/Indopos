<?php

namespace App\Filament\Resources\DailyPostingResource\Pages;

use App\Filament\Resources\DailyPostingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDailyPosting extends ViewRecord
{
    protected static string $resource = DailyPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\RestoreAction::make()
                ->label('🔄 Restore / Pulihkan Data Ini')
                ->color('warning'),
            Actions\ForceDeleteAction::make()
                ->label('❌ Hapus Permanen'),
        ];
    }
}
