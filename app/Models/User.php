<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_path',
        'role',
        'dob',
        'phone',
        'address',
        'lock_flag',
        'lock_count',
        'last_lock_at',
        'last_login_at',
        'create_user_id',
        'updated_user_id',
        'deleted_user_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date',
        'last_lock_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'create_user_id');
    }

    public function isAdmin()
    {
        return $this->role === 0;
    }

    public function isLocked()
    {
        return $this->lock_flg === 1;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_user_id');
    }
}