<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('reference_type', ['document', 'request_update_document', 'safety_induction', 'safety_patrol', 'safety_patrol_feedback'])
                ->change();
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
