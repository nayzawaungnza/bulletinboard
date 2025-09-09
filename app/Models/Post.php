<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'create_user_id',
        'updated_user_id',
        'deleted_user_id',
    ];

public function creator()
{
    return $this->belongsTo(User::class, 'create_user_id');
}

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_user_id');
    }

    public function isActive()
    {
        return $this->status === 1;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}