<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tambahkan DELETED ke enum status
        // DB::statement("ALTER TABLE document_approvals MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'DELETED') NOT NULL DEFAULT 'pending'");
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'DELETED'])->default('pending')->change();
        });
    }

    public function down()
    {
        // Kembalikan ke enum sebelumnya
        // DB::statement("ALTER TABLE document_approvals MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
