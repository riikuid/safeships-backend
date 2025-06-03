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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key ke users
            $table->foreignId('safety_induction_id')->constrained()->onDelete('cascade'); // Foreign key ke safety_inductions
            $table->date('issued_date'); // Tanggal penerbitan sertifikat
            $table->date('expired_date')->nullable(); // Tanggal kedaluwarsa sertifikat
            $table->string('url'); // URL file sertifikat (PDF)
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
