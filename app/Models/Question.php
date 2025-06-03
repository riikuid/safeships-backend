<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'question_package_id',
        'text',
        'options',
        'correct_answer',
    ];

    protected $casts = [
        'text' => 'string',
        'options' => 'array', // JSON di-cast ke array
        'correct_answer' => 'string',
    ];

    public function questionPackage()
    {
        return $this->belongsTo(QuestionPackage::class);
    }
}
