<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyInductionAttempt extends Model
{
    protected $fillable = [
        'safety_induction_id',
        'question_package_id',
        'score',
        'passed',
        'attempt_date',
    ];

    protected $casts = [
        'score' => 'integer',
        'passed' => 'boolean',
        'attempt_date' => 'date',
    ];

    public function safetyInduction()
    {
        return $this->belongsTo(SafetyInduction::class);
    }

    public function questionPackage()
    {
        return $this->belongsTo(QuestionPackage::class);
    }
}
