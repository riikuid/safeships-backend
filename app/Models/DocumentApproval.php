<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentApproval extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_id',
        'approver_id',
        'status',
        'comments',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
