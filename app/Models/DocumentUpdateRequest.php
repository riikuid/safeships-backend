<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentUpdateRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_id',
        'manager_id',
        'comments',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
