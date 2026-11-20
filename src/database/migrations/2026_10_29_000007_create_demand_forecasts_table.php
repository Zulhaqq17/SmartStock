<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->date('forecast_date');
            $table->decimal('predicted_demand_qty', 10, 2);
            $table->decimal('predicted_spoilage_qty', 10, 2);
            $table->decimal('recommended_reorder_qty', 10, 2);
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->timestamp('generated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demand_forecasts');
    }
};
