<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyPostingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_posting_id',
        'product_id',
        'product_name',
        'total_qty_sold',
        'total_base_qty_sold',
        'total_revenue',
        'total_cost',
        'net_profit',
        'transaction_times',
    ];

    public function dailyPosting(): BelongsTo
    {
        return $this->belongsTo(DailyPosting::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
