<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns only if they don't already exist to avoid duplicate column errors
        if (!Schema::hasColumn('orders', 'name')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('name', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('orders', 'contact')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('contact', 50)->nullable();
            });
        }

        if (!Schema::hasColumn('orders', 'address')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('address', 500)->nullable();
            });
        }

        if (!Schema::hasColumn('orders', 'payment_receipt')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_receipt', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('orders', 'payment_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_status', 50)->default('Pending');
            });
        }

        if (!Schema::hasColumn('orders', 'delivery_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('delivery_status', 50)->default('Pending');
            });
        }
    }

    public function down(): void
    {
        // Only drop columns if they exist to avoid errors when rolling back
        $cols = ['name', 'contact', 'address', 'payment_receipt', 'payment_status', 'delivery_status'];
        foreach ($cols as $col) {
            if (Schema::hasColumn('orders', $col)) {
                Schema::table('orders', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
