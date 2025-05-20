<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafetyPatrolFeedbackApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_id',
        'approver_id',
        'status',
        'comments',
        'approved_at',
    ];

    protected $casts = [
        'status' => 'string',
        'approved_at' => 'datetime',
    ];

    public function feedback()
    {
        return $this->belongsTo(SafetyPatrolFeedback::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
