<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionPrintController extends Controller
{
    public function print(Transaction $transaction)
    {
        $transaction->load(['branch', 'user', 'items.product', 'items.unit']);

        return view('print.receipt', [
            'transaction' => $transaction,
        ]);
    }
}
