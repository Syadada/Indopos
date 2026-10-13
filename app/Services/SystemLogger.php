<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SystemLogger
{
    /**
     * Catat aktivitas pemindaian barcode POS.
     */
    public static function logBarcodeScan(string $barcode, string $status, ?string $productName = null, ?string $source = null): void
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'event' => 'BARCODE_SCAN',
            'barcode' => $barcode,
            'status' => $status,
            'product' => $productName,
            'source' => $source ?? 'Database Toko',
            'user' => auth()->user()->name ?? 'Guest',
        ];

        Log::channel('single')->info('POS Barcode Scan: ' . json_encode($logData));
        self::appendCustomLog('pos_scans.log', $logData);
    }

    /**
     * Catat transaksi selesai di POS.
     */
    public static function logTransaction(string $invoiceNumber, float $grandTotal, string $paymentMethod, int $itemsCount, ?string $customerName = null): void
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'event' => 'TRANSACTION_SUCCESS',
            'invoice' => $invoiceNumber,
            'customer' => $customerName ?? 'Umum',
            'total' => $grandTotal,
            'payment_method' => $paymentMethod,
            'items_count' => $itemsCount,
            'cashier' => auth()->user()->name ?? 'Kasir',
        ];

        Log::channel('single')->info('POS Transaction: ' . json_encode($logData));
        self::appendCustomLog('pos_transactions.log', $logData);
    }

    /**
     * Catat error sistem ke file log khusus.
     */
    public static function logError(string $context, string $message, ?\Throwable $exception = null): void
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'event' => 'SYSTEM_ERROR',
            'context' => $context,
            'message' => $message,
            'trace' => $exception ? $exception->getMessage() : null,
        ];

        Log::channel('single')->error('System Error: ' . json_encode($logData));
        self::appendCustomLog('system_errors.log', $logData);
    }

    private static function appendCustomLog(string $fileName, array $data): void
    {
        try {
            $logDir = storage_path('logs');
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $line = '[' . date('Y-m-d H:i:s') . '] ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
            file_put_contents($logDir . DIRECTORY_SEPARATOR . $fileName, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Silently ignore if file permission fails
        }
    }
}
