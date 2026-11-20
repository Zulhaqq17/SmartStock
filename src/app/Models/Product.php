<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'product_name', 'sku_code', 'unit_of_measure', 'min_stock_level', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'min_stock_level' => 'decimal:2'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(DemandForecast::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
