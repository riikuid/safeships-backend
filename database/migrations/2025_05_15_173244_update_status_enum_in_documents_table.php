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
        // DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending_super_admin', 'pending_manager', 'approved', 'rejected', 'DELETED') DEFAULT 'pending_super_admin'");

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', ['pending_super_admin', 'pending_manager', 'approved', 'rejected', 'DELETED'])->default('pending_super_admin')->change();
        });
    }

    public function down()
    {
        // Kembalikan ke enum sebelumnya
        // DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending_super_admin', 'pending_manager', 'approved', 'rejected') DEFAULT 'pending_super_admin'");
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', ['pending_super_admin', 'pending_manager', 'approved', 'rejected'])->default('pending_super_admin')->change();
        });
    }
};
