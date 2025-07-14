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
     Schema::create('admins', function (Blueprint $table) {
    $table->id('admin_id');
    $table->string('admin_fname', 30);
    $table->string('admin_lname', 30);
    $table->string('admin_contactNo', 11);
    $table->string('admin_email', 40)->unique();
    $table->string('admin_username', 30)->unique();
    $table->string('admin_pass', 255); // hash password
    $table->enum('admin_status', ['Active', 'Inactive'])->default('Active');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
