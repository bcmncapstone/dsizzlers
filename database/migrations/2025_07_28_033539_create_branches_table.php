<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
     $table->id('branch_id');
    $table->string('location');
    $table->string('first_name');
    $table->string('last_name');
    $table->string('email')->unique();
    $table->string('contact_number');
    $table->string('contract_file')->nullable(); // Path to uploaded contract file
    $table->date('contract_expiration');
    $table->string('branch_status');
    $table->boolean('archived')->default(false); // Used instead of delete
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
