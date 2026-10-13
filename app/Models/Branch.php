<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model implements HasName
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subtitle',
        'code',
        'phone',
        'address',
        'is_active',
    ];

    public function getFilamentName(): string
    {
        return "{$this->name} ({$this->code})";
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
