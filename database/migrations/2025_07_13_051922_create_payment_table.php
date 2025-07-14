<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('order_id')
                  ->constrained('orders', 'order_id')
                  ->onDelete('cascade');
            $table->foreignId('item_id')
                  ->nullable()
                  ->constrained('items', 'item_id')
                  ->nullOnDelete();
            $table->float('amount_paid');
            $table->date('date');
            $table->string('status', 50)->default('Pending');
            $table->string('receipt', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
