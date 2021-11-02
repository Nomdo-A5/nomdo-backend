<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $fillable = [
        'report_name',
        'workspace_id'
    ];
    public function workspace(){
        return $this->belongsTo(workspace::class, 'workspace_id', 'id');
    }
    public function balance(){
        return $this->hasMany(Balance::class, 'balance_id', 'id');
    }
}