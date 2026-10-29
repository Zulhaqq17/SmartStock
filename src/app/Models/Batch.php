<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'product_id', 'batch_number', 'initial_quantity', 'current_quantity',
        'manufacture_date', 'expiry_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'manufacture_date' => 'date',
            'expiry_date' => 'date',          // a Carbon date: $batch->expiry_date->isPast()
            'initial_quantity' => 'decimal:2',
            'current_quantity' => 'decimal:2',
        ];
    }

    /** Batch::active()->get()  => only batches that still have stock */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('current_quantity', '>', 0);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }
}
