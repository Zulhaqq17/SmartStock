<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('batch_number', 100);
            $table->decimal('initial_quantity', 10, 2);
            $table->decimal('current_quantity', 10, 2);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();          // null = non-perishable
            $table->string('status', 20)->default('active');  // active | near_expiry | expired | depleted
            $table->timestamps();

            $table->unique(['product_id', 'batch_number']);
            $table->index(['product_id', 'expiry_date']);      // speeds up the FEFO query
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
