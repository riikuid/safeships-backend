<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'reference_type',
        'reference_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
