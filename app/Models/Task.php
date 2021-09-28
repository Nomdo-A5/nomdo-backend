<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'task_name',
        'task_description',
        'status'
    ];

    public function users(){
        return $this->belongsToMany(User::class, 'task_members','task_id','user_id');
    }
}
