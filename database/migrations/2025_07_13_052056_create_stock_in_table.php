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
          Schema::create('stock_in', function (Blueprint $table) {
            $table->id('stock_in_id');
            $table->foreignId('item_id')
                  ->constrained('items', 'item_id')
                  ->onDelete('cascade');
            $table->integer('quantity_received');
            $table->dateTime('received_date')->useCurrent();
            $table->string('supplier_name', 50)->nullable();
            $table->unsignedBigInteger('restocked_by'); // Could reference admin_staff or franchisee_staff
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_in');
    }
};
