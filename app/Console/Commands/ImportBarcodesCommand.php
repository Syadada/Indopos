<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportBarcodesCommand extends Command
{
    protected $signature = 'pos:import-barcodes {file : Path ke file CSV yang akan di-import} {--to-products : Juga masukkan seluruh barang dari CSV langsung ke katalog barang aktif toko (Kelola Barang)}';

    protected $description = 'Import dataset produk & barcode dari CSV ke Master Barcode Catalog dan/atau Katalog Barang Aktif Toko (Kelola Barang)';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return 1;
        }

        DB::disableQueryLog();

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Gagal membuka file: {$filePath}");
            return 1;
        }

        $importToProducts = $this->option('to-products');

        $this->info("Mulai memproses import dataset dari file: {$filePath}...");
        $this->info($importToProducts ? "-> Mode: Import ke Master Barcode & Kelola Barang Toko (Products)" : "-> Mode: Import ke Master Barcode Catalog");

        $header = fgetcsv($handle, 1000, ',');
        
        $barcodeIdx = 0;
        $nameIdx = 1;
        $categoryIdx = 2;
        $buyPriceIdx = 3;
        $sellPriceIdx = 4;

        if ($header) {
            foreach ($header as $index => $colName) {
                $colLower = strtolower(trim($colName));
                if (str_contains($colLower, 'barcode') || str_contains($colLower, 'code') || str_contains($colLower, 'ean')) {
                    $barcodeIdx = $index;
                } elseif (str_contains($colLower, 'nama') || str_contains($colLower, 'name') || str_contains($colLower, 'product')) {
                    $nameIdx = $index;
                } elseif (str_contains($colLower, 'kategori') || str_contains($colLower, 'category')) {
                    $categoryIdx = $index;
                } elseif (str_contains($colLower, 'beli') || str_contains($colLower, 'modal') || str_contains($colLower, 'buy')) {
                    $buyPriceIdx = $index;
                } elseif (str_contains($colLower, 'jual') || str_contains($colLower, 'sell') || str_contains($colLower, 'price')) {
                    $sellPriceIdx = $index;
                }
            }
        }

        // Cache category and unit mapping for products insert
        $categoriesMap = Category::pluck('id', 'name')->toArray();
        $defaultCategory = Category::firstOrCreate(['slug' => 'sembako'], ['name' => 'Sembako & Bahan Dapur']);
        $defaultUnit = Unit::firstOrCreate(['short_name' => 'Pcs'], ['name' => 'Pieces (Pcs)']);
        $branches = Branch::where('is_active', true)->get();

        $batchSize = 300;
        $catalogBuffer = [];
        $productBuffer = [];
        $totalCatalog = 0;
        $totalProducts = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $barcode = isset($row[$barcodeIdx]) ? trim($row[$barcodeIdx]) : null;
            $name = isset($row[$nameIdx]) ? trim($row[$nameIdx]) : null;

            if (empty($barcode) || empty($name)) {
                continue;
            }

            $catName = isset($row[$categoryIdx]) ? trim($row[$categoryIdx]) : 'Sembako & Bahan Dapur';
            
            // Add to Catalog Buffer
            $catalogBuffer[] = [
                'barcode' => mb_substr($barcode, 0, 50),
                'name' => mb_substr($name, 0, 190),
                'category' => mb_substr($catName, 0, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // If importing directly to products
            if ($importToProducts) {
                // Determine Category ID
                $catId = $defaultCategory->id;
                foreach ($categoriesMap as $cName => $cId) {
                    if (mb_stripos($cName, $catName) !== false || mb_stripos($catName, $cName) !== false) {
                        $catId = $cId;
                        break;
                    }
                }

                // Prices
                $buyPrice = isset($row[$buyPriceIdx]) && is_numeric($row[$buyPriceIdx]) ? (float)$row[$buyPriceIdx] : $this->guessBuyPrice($name);
                $sellPrice = isset($row[$sellPriceIdx]) && is_numeric($row[$sellPriceIdx]) ? (float)$row[$sellPriceIdx] : $this->guessSellPrice($name, $buyPrice);

                $sku = 'SKU-' . strtoupper(Str::slug(mb_substr($name, 0, 10))) . '-' . rand(100, 999);

                $productBuffer[] = [
                    'category_id' => $catId,
                    'base_unit_id' => $defaultUnit->id,
                    'name' => mb_substr($name, 0, 190),
                    'barcode' => mb_substr($barcode, 0, 50),
                    'sku' => $sku,
                    'description' => 'Imported Sembako Product',
                    'buy_price' => $buyPrice,
                    'sell_price' => $sellPrice,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($catalogBuffer) >= $batchSize) {
                DB::table('barcode_catalogs')->insertOrIgnore($catalogBuffer);
                $totalCatalog += count($catalogBuffer);
                $catalogBuffer = [];

                if ($importToProducts && !empty($productBuffer)) {
                    $totalProducts += $this->flushProducts($productBuffer, $branches);
                    $productBuffer = [];
                }

                $this->line(" - Berhasil memproses {$totalCatalog} data...");
            }
        }

        if (!empty($catalogBuffer)) {
            DB::table('barcode_catalogs')->insertOrIgnore($catalogBuffer);
            $totalCatalog += count($catalogBuffer);
        }

        if ($importToProducts && !empty($productBuffer)) {
            $totalProducts += $this->flushProducts($productBuffer, $branches);
        }

        fclose($handle);

        $this->info("✅ SELESAI!");
        $this->info(" -> Master Barcode Catalog: {$totalCatalog} data");
        if ($importToProducts) {
            $this->info(" -> Kelola Barang (Toko): {$totalProducts} produk baru berhasil dimasukkan!");
        }
        return 0;
    }

    private function flushProducts(array $products, $branches): int
    {
        DB::table('products')->insertOrIgnore($products);

        $barcodes = array_column($products, 'barcode');
        $insertedProducts = DB::table('products')->whereIn('barcode', $barcodes)->get(['id']);

        $stockBuffer = [];
        foreach ($insertedProducts as $p) {
            foreach ($branches as $b) {
                $stockBuffer[] = [
                    'branch_id' => $b->id,
                    'product_id' => $p->id,
                    'store_stock' => 50,
                    'warehouse_stock' => 100,
                    'min_store_stock_alert' => 10,
                    'min_warehouse_stock_alert' => 20,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($stockBuffer)) {
            DB::table('branch_stocks')->insertOrIgnore($stockBuffer);
        }

        return count($insertedProducts);
    }

    private function guessBuyPrice(string $name): float
    {
        $lower = strtolower($name);
        if (str_contains($lower, 'indomie') || str_contains($lower, 'mie sedaap') || str_contains($lower, 'pop mie')) return 2800;
        if (str_contains($lower, 'aqua 600') || str_contains($lower, 'le minerale')) return 2600;
        if (str_contains($lower, 'teh pucuk') || str_contains($lower, 'floridina')) return 2800;
        if (str_contains($lower, 'minyak') || str_contains($lower, 'bimoli') || str_contains($lower, 'sania')) return 32000;
        if (str_contains($lower, 'terigu') || str_contains($lower, 'segitiga biru')) return 10500;
        if (str_contains($lower, 'gulaku') || str_contains($lower, 'gula')) return 14500;
        if (str_contains($lower, 'sunlight') || str_contains($lower, 'rinso')) return 12500;
        if (str_contains($lower, 'pepsodent') || str_contains($lower, 'biore')) return 9500;
        return 5000;
    }

    private function guessSellPrice(string $name, float $buyPrice): float
    {
        $lower = strtolower($name);
        if (str_contains($lower, 'indomie') || str_contains($lower, 'mie sedaap')) return 3500;
        if (str_contains($lower, 'pop mie')) return 5000;
        if (str_contains($lower, 'aqua 600')) return 4000;
        if (str_contains($lower, 'teh pucuk')) return 4000;
        if (str_contains($lower, 'minyak') || str_contains($lower, 'bimoli')) return 38500;
        if (str_contains($lower, 'terigu') || str_contains($lower, 'segitiga biru')) return 13000;
        if (str_contains($lower, 'gulaku')) return 17500;
        return round($buyPrice * 1.25);
    }
}
