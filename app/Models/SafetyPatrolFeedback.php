<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyPatrolFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'safety_patrol_feedbacks';

    protected $fillable = [
        'safety_patrol_id',
        'actor_id',
        'feedback_date',
        'image_path',
        'description',
        'status',
    ];

    protected $casts = [
        'feedback_date' => 'date',
        'status' => 'string',
    ];

    public function safetyPatrol()
    {
        return $this->belongsTo(SafetyPatrol::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function approvals()
    {
        return $this->hasMany(SafetyPatrolFeedbackApproval::class, 'feedback_id');
    }
}
