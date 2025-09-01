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
         Schema::create('franchisees', function (Blueprint $table) {
            $table->id('franchisee_id');
            $table->foreignId('admin_id')
                  ->constrained('admins', 'admin_id')
                  ->onDelete('cascade');
            $table->string('franchisee_name', 50);
            $table->string('franchisee_contactNo', 11);
            $table->string('franchisee_email', 40)->unique();
            $table->string('franchisee_username', 30)->unique();
            $table->string('franchisee_pass', 255);
            $table->string('franchisee_address');
            $table->enum('franchisee_status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('franchisees');
    }
};