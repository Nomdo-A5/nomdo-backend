<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Boards extends Model
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'boards_name'
    ];
    public function workspace(){
        return $this->belongsTo(Workspace::class,'workspace_id','id');
    }
}