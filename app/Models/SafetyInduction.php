<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyInduction extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'address',
        'phone_number',
        'email',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attempts()
    {
        return $this->hasMany(SafetyInductionAttempt::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }
}
