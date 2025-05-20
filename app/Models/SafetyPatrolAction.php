<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafetyPatrolAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'safety_patrol_id',
        'actor_id',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function safetyPatrol()
    {
        return $this->belongsTo(SafetyPatrol::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
