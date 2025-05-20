<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafetyPatrolApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'safety_patrol_id',
        'approver_id',
        'status',
        'comments',
        'approved_at',
    ];

    protected $casts = [
        'status' => 'string',
        'approved_at' => 'datetime',
    ];

    public function safetyPatrol()
    {
        return $this->belongsTo(SafetyPatrol::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
