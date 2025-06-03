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
        Schema::create('safety_induction_attempts', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('safety_induction_id')->constrained()->onDelete('cascade'); // Foreign key ke safety_inductions
            $table->foreignId('question_package_id')->constrained()->onDelete('cascade'); // Foreign key ke question_packages
            $table->integer('score'); // Nilai tes (0-100)
            $table->boolean('passed')->default(false); // Status lulus (true jika score >= 80)
            $table->date('attempt_date'); // Tanggal percobaan
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_induction_attempts');
    }
};
