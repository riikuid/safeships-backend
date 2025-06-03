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
        Schema::create('questions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('question_package_id')->constrained()->onDelete('cascade'); // Foreign key ke question_packages
            $table->text('text'); // Teks soal
            $table->json('options'); // Pilihan jawaban (A, B, C, D)
            $table->string('correct_answer', 1); // Jawaban benar (A, B, C, atau D)
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
