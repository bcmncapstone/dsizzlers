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
       Schema::create('admin_staff', function (Blueprint $table) {
            $table->id('astaff_id');
            $table->foreignId('staffAdmin_id')
                  ->constrained('admins', 'admin_id')
                  ->onDelete('cascade');
            $table->string('astaff_fname', 30);
            $table->string('astaff_lname', 30);
            $table->string('astaff_contactNo', 11);
            $table->string('astaff_username', 30)->unique();
            $table->string('astaff_pass', 255);
            $table->enum('astaff_status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_of_admin');
    }
};