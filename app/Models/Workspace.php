<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;
    protected $fillable = [
        'workspace_name',
        'url_join'
    ];

    public function users(){
        return $this->belongsToMany(User::class, 'workspace_members','workspace_id','user_id')->withPivot(['is_owner', 'is_admin']);
    }
    public function boards(){
        return $this->hasMany(Boards::class, 'workspace_id', 'id');
    }
    public function report(){
        return $this->hasOne(Report::class, 'workspace_id', 'id');
    }
}