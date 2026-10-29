<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    public $timestamps = false;   // this table uses transaction_date instead of created_at/updated_at

    protected $fillable = [
        'batch_id', 'user_id', 'transaction_type', 'quantity_changed', 'remarks', 'transaction_date',
    ];

    protected function casts(): array
    {
        return ['transaction_date' => 'datetime', 'quantity_changed' => 'decimal:2'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
