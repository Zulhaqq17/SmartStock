<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    public const EXPIRED = 'expired';
    public const NEAR_EXPIRY = 'near_expiry';
    public const LOW_STOCK = 'low_stock';

    protected $fillable = ['alert_type', 'product_id', 'batch_id', 'message', 'is_resolved', 'resolved_at'];

    protected function casts(): array
    {
        return ['is_resolved' => 'boolean', 'resolved_at' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
