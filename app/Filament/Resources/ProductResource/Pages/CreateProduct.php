<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Branch;
use App\Models\BranchStock;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $branches = Branch::all();
        foreach ($branches as $branch) {
            BranchStock::firstOrCreate(
                ['branch_id' => $branch->id, 'product_id' => $this->record->id],
                [
                    'store_stock' => 0,
                    'warehouse_stock' => 0,
                    'min_store_stock_alert' => 10,
                    'min_warehouse_stock_alert' => 20,
                ]
            );
        }
    }
}
