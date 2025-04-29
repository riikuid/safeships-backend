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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_id')->constrained('users');
            $table->longText('file_path');
            $table->string('title');
            $table->longText('description');
            $table->enum('status', ['pending_super_admin', 'pending_manager', 'approved', 'rejected'])->default('pending_super_admin');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
