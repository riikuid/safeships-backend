<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'safety_induction_id',
        'issued_date',
        'expired_date',
        'url',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expired_date' => 'date',
        'url' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function safetyInduction()
    {
        return $this->belongsTo(SafetyInduction::class);
    }
}
