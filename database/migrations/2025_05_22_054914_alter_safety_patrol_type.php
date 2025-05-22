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
        Schema::table('safety_patrols', function (Blueprint $table) {
            $table->enum('type', ['unsafe_condition', 'unsafe_action'])
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safety_patrols', function (Blueprint $table) {
            $table->enum('type', ['condition', 'unsafe_action'])
                ->change();
        });
    }
};
