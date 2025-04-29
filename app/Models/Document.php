<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'manager_id',
        'file_path',
        'title',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function documentApprovals()
    {
        return $this->hasMany(DocumentApproval::class, 'document_id');
    }

    public function updateRequests()
    {
        return $this->hasMany(DocumentUpdateRequest::class, 'document_id');
    }
}
