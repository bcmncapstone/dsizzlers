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
           Schema::create('price_used', function (Blueprint $table) {
            $table->id('price_used_id');
            $table->foreignId('item_id')
                  ->constrained('items', 'item_id')
                  ->onDelete('cascade');
            $table->decimal('used_price', 10, 2);
            $table->timestamp('used_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_used');
    }
};
