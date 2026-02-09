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
        Schema::create('franchisee_stock', function (Blueprint $table) {
            $table->id('stock_id');
            $table->foreignId('franchisee_id')
                  ->constrained('franchisees', 'franchisee_id')
                  ->onDelete('cascade');
            $table->foreignId('item_id')
                  ->constrained('items', 'item_id')
                  ->onDelete('cascade');
            $table->integer('current_quantity')->default(0);
            $table->integer('minimum_quantity')->default(10); // For low stock alerts
            $table->timestamps();
            
            // Ensure one record per franchisee per item
            $table->unique(['franchisee_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('franchisee_stock');
    }
};
