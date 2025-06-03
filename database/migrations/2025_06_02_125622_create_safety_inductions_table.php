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
        Schema::create('safety_inductions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key ke tabel users
            $table->string('name', 255); // Nama lengkap pemohon
            $table->enum('type', ['karyawan', 'mahasiswa', 'tamu', 'kontraktor']); // Tipe pemohon
            $table->text('address')->nullable(); // Alamat pemohon (opsional)
            $table->string('phone_number', 20)->nullable(); // Nomor telepon (opsional)
            $table->string('email')->nullable(); // Email pemohon (opsional)
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending'); // Status pengajuan
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_inductions');
    }
};
