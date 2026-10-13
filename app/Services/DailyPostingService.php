<?php

namespace App\Services;

use App\Models\DailyPosting;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DailyPostingService
{
    public static function postForDate(int $branchId, string $dateString): DailyPosting
    {
        return DB::transaction(function () use ($branchId, $dateString) {
            $transactions = Transaction::where('branch_id', $branchId)
                ->where('status', 'completed')
                ->whereDate('created_at', $dateString)
                ->with(['items.product'])
                ->get();

            $totalTrxCount = $transactions->count();
            $totalMoneyIn = $transactions->sum('grand_total');
            $totalMoneyOut = $transactions->sum('total_cost_price');
            $netProfit = $transactions->sum('net_profit');

            $itemAggregates = [];
            $totalItemsCount = 0;

            foreach ($transactions as $trx) {
                foreach ($trx->items as $item) {
                    $prodId = $item->product_id;
                    $prodName = $item->product->name ?? ('Barang #' . $prodId);

                    if (!isset($itemAggregates[$prodId])) {
                        $itemAggregates[$prodId] = [
                            'product_id' => $prodId,
                            'product_name' => $prodName,
                            'total_qty_sold' => 0,
                            'total_base_qty_sold' => 0,
                            'total_revenue' => 0,
                            'total_cost' => 0,
                            'net_profit' => 0,
                            'times' => [],
                        ];
                    }

                    $itemAggregates[$prodId]['total_qty_sold'] += $item->quantity;
                    $itemAggregates[$prodId]['total_base_qty_sold'] += $item->total_base_quantity;
                    $itemAggregates[$prodId]['total_revenue'] += $item->subtotal;
                    $costForItem = ($item->cost_price_per_unit * $item->quantity);
                    $itemAggregates[$prodId]['total_cost'] += $costForItem;
                    $itemAggregates[$prodId]['net_profit'] += $item->item_profit;
                    $itemAggregates[$prodId]['times'][] = $trx->created_at ? $trx->created_at->format('H:i:s') : date('H:i:s');

                    $totalItemsCount += $item->quantity;
                }
            }

            $postingCode = 'POST-' . date('Ymd', strtotime($dateString)) . '-B' . $branchId;

            $posting = DailyPosting::updateOrCreate(
                [
                    'posting_date' => $dateString,
                    'branch_id' => $branchId,
                ],
                [
                    'posting_code' => $postingCode,
                    'total_transactions_count' => $totalTrxCount,
                    'total_items_sold' => $totalItemsCount,
                    'total_money_in' => $totalMoneyIn,
                    'total_money_out' => $totalMoneyOut,
                    'net_profit' => $netProfit,
                    'status' => 'completed',
                    'notes' => "Posting harian tanggal {$dateString} ({$totalTrxCount} transaksi selesai).",
                ]
            );

            $posting->items()->delete();

            foreach ($itemAggregates as $itemData) {
                $timesList = implode(', ', array_unique($itemData['times']));
                unset($itemData['times']);
                $itemData['transaction_times'] = $timesList;

                $posting->items()->create($itemData);
            }

            // Cleanup / delete posted transactions to keep active transactions list clean
            foreach ($transactions as $trx) {
                $trx->items()->delete();
                $trx->delete();
            }

            return $posting;
        });
    }
}
