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
        Schema::create('question_packages', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name', 255); // Nama paket (misalnya, Paket A, Paket B, Paket Mudah)
            $table->enum('type', ['standard', 'easy']); // Tipe paket (standar atau mudah)
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_packages');
    }
};
