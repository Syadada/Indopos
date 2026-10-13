<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class BarcodeCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'name',
        'category',
    ];

    /**
     * Cari barcode di database lokal.
     * Jika tidak ada, panggil Online Open Food Facts API secara otomatis,
     * lalu simpan ke database lokal untuk penggunaan berikutnya (Offline Cache).
     */
    public static function findOrFetchOnline(string $barcode): ?BarcodeCatalog
    {
        $code = trim($barcode);
        if (empty($code)) return null;

        // 1. Cek database lokal
        $local = static::where('barcode', $code)->first();
        if ($local) {
            return $local;
        }

        // 2. Cek dataset offline lokal (data_sembako_lengkap.csv & data_sembako.csv)
        $csvLengkap = base_path('data_sembako_lengkap.csv');
        if (file_exists($csvLengkap)) {
            if (($handle = fopen($csvLengkap, 'r')) !== false) {
                fgetcsv($handle); // skip header
                while (($row = fgetcsv($handle)) !== false) {
                    if (isset($row[0]) && trim($row[0]) === $code) {
                        fclose($handle);
                        return static::create([
                            'barcode' => $code,
                            'name' => trim($row[1] ?? 'Sembako'),
                            'category' => trim($row[2] ?? 'Kebutuhan Pokok'),
                        ]);
                    }
                }
                fclose($handle);
            }
        }

        $csvSimple = base_path('data_sembako.csv');
        if (file_exists($csvSimple)) {
            if (($handle = fopen($csvSimple, 'r')) !== false) {
                while (($row = fgetcsv($handle)) !== false) {
                    if (isset($row[0]) && trim($row[0]) === $code) {
                        fclose($handle);
                        return static::create([
                            'barcode' => $code,
                            'name' => trim($row[1] ?? 'Produk Sembako'),
                            'category' => 'Kebutuhan Pokok / Sembako',
                        ]);
                    }
                }
                fclose($handle);
            }
        }

        // 3. Fallback online hanya jika ada koneksi (timeout 1 detik, non-blocking)
        try {
            $response = Http::timeout(1)
                ->withHeaders(['User-Agent' => 'IndoPOS - Offline-First App'])
                ->get("https://world.openfoodfacts.net/api/v2/product/{$code}?fields=product_name,product_name_id,brands");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['product'])) {
                    $prod = $data['product'];
                    $name = $prod['product_name_id'] ?? $prod['product_name'] ?? null;
                    if ($name) {
                        $brand = $prod['brands'] ?? '';
                        $fullName = !empty($brand) ? "{$name} ({$brand})" : $name;

                        return static::create([
                            'barcode' => $code,
                            'name' => mb_substr($fullName, 0, 190),
                            'category' => 'Cloud Auto-Fetch',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Bersahabat dengan mode offline: jangan lempar error saat internet mati
        }

        return null;
    }
}
