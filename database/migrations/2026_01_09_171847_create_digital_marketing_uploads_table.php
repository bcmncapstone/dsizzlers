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
        Schema::create('digital_marketing_uploads', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('uploaded_by');
    $table->string('image_path');
    $table->string('description')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_marketing_uploads');
    }
};
