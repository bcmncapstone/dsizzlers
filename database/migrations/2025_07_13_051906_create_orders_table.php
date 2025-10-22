<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');

            $table->foreignId('franchisee_id')
                  ->nullable()
                  ->references('franchisee_id')
                  ->on('franchisees')
                  ->nullOnDelete();

            $table->foreignId('fstaff_id')
                  ->nullable()
                  ->references('fstaff_id')
                  ->on('franchisee_staff')
                  ->nullOnDelete();

            $table->dateTime('order_date')->useCurrent();
            $table->enum('order_status', ['Pending', 'Confirmed', 'Preparing', 'Delivered', 'Cancelled'])
                  ->default('Pending');
            $table->decimal('total_amount', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
