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
       Schema::create('packages', function (Blueprint $table) {
            $table->id('package_id');
            $table->string('package_name', 50);
            $table->text('package_description')->nullable();
            $table->float('package_price');
            $table->enum('package_status', ['Active', 'Inactive'])->default('Active');
            $table->dateTime('created_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
