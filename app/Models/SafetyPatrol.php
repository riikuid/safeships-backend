<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyPatrol extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'manager_id',
        'report_date',
        'image_path',
        'type',
        'description',
        'location',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
        'type' => 'string',
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function approvals()
    {
        return $this->hasMany(SafetyPatrolApproval::class);
    }

    public function action()
    {
        return $this->hasOne(SafetyPatrolAction::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(SafetyPatrolFeedback::class);
    }
}
