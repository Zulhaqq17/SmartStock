<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandForecast extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'forecast_date', 'predicted_demand_qty', 'predicted_spoilage_qty',
        'recommended_reorder_qty', 'confidence_score', 'generated_at',
    ];

    protected function casts(): array
    {
        return ['forecast_date' => 'date', 'generated_at' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
