<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class DailyPosting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'posting_code',
        'posting_date',
        'branch_id',
        'total_transactions_count',
        'total_items_sold',
        'total_money_in',
        'total_money_out',
        'net_profit',
        'status',
        'notes',
    ];

    protected $casts = [
        'posting_date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyPostingItem::class);
    }
}
