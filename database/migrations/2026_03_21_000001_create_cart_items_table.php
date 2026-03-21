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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id('cart_item_id');

            $table->foreignId('franchisee_id')
                ->nullable()
                ->references('franchisee_id')
                ->on('franchisees')
                ->cascadeOnDelete();

            $table->foreignId('fstaff_id')
                ->nullable()
                ->references('fstaff_id')
                ->on('franchisee_staff')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->references('item_id')
                ->on('items')
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['franchisee_id', 'item_id'], 'cart_items_franchisee_item_unique');
            $table->unique(['fstaff_id', 'item_id'], 'cart_items_fstaff_item_unique');
            $table->index('item_id', 'cart_items_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
