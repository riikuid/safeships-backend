<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
