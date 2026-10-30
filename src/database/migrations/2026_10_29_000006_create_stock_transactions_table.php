<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches');
            $table->foreignId('user_id')->constrained('users');
            $table->string('transaction_type', 50);   // received | used | adjustment
            $table->decimal('quantity_changed', 10, 2); // signed: negative when stock is used
            $table->text('remarks')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
