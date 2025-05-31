<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('reference_type', [
                'document_view',          // Melihat detail/progres dokumen
                'document_approve',       // Melakukan approval dokumen
                'document_update_request', // Permintaan update dokumen
                'safety_induction_view',  // Melihat detail safety induction
                'safety_patrol_view',     // Melihat detail safety patrol
                'safety_patrol_approve', // Memberikan feedback patrol
                'safety_patrol_action',   // Menugaskan tindakan korektif
            ])->change();
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('reference_type', ['document', 'safety_induction', 'safety_patrol'])
                ->change();
        });
    }
};
