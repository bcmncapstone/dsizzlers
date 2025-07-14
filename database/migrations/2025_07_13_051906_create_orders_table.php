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
         Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->foreignId('astaff_id')
                  ->nullable()
                  ->constrained('admin_staff', 'astaff_id')
                  ->nullOnDelete();
            $table->foreignId('fstaff_id')
                  ->nullable()
                  ->constrained('franchisee_staff', 'fstaff_id')
                  ->nullOnDelete();
            $table->dateTime('order_date')->useCurrent();
            $table->enum('order_status', ['Pending', 'Confirmed', 'Preparing', 'Delivered', 'Cancelled'])
                  ->default('Pending');
            $table->decimal('total_amount', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
