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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->foreignId('franchisee_id')
                  ->constrained('franchisees', 'franchisee_id')
                  ->onDelete('cascade');
            $table->foreignId('item_id')
                  ->constrained('items', 'item_id')
                  ->onDelete('cascade');
            $table->enum('transaction_type', ['in', 'out', 'adjustment']); // in=delivered, out=sale/spoilage, adjustment=manual
            $table->integer('quantity'); // Positive for 'in', negative for 'out'
            $table->integer('balance_after'); // Stock balance after this transaction
            $table->string('reference_type', 50)->nullable(); // order, manual, spoilage
            $table->unsignedBigInteger('reference_id')->nullable(); // order_id if related to order
            $table->text('notes')->nullable(); // For manual adjustments
            $table->string('performed_by_type', 50)->nullable(); // franchisee, franchisee_staff, admin
            $table->unsignedBigInteger('performed_by_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
