<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('safety_patrols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->date('report_date');
            $table->string('image_path');
            $table->enum('type', ['condition', 'unsafe_action']);
            $table->text('description');
            $table->string('location');
            $table->enum('status', [
                'pending_super_admin',
                'pending_manager',
                'pending_action',
                'action_in_progress',
                'pending_feedback_approval',
                'done',
                'rejected'
            ])->default('pending_super_admin');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('safety_patrols');
    }
};
