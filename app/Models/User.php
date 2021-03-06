<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'file_profile'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'fcm_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members', 'user_id', 'workspace_id')->withPivot(['is_owner', 'is_admin']);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_members', 'user_id', 'task_id');
    }
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
}