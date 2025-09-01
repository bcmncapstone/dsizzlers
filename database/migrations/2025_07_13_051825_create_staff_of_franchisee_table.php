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
        Schema::create('franchisee_staff', function (Blueprint $table) {
            $table->id('fstaff_id');
            $table->foreignId('franchisee_id')
                  ->constrained('franchisees', 'franchisee_id')
                  ->onDelete('cascade');
            $table->string('fstaff_fname', 50);
            $table->string('fstaff_lname', 50);
            $table->string('fstaff_contactNo', 13);
            $table->string('fstaff_username', 50)->unique();
            $table->string('fstaff_pass', 255);
            $table->enum('fstaff_status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_of_franchisee');
    }
};