<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type', 20);                       // expired | near_expiry | low_stock
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->nullable()->constrained('batches'); // null for low_stock
            $table->string('message', 255);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['alert_type', 'product_id', 'batch_id', 'is_resolved']);
            $table->index(['is_resolved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
