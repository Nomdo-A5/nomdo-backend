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
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }
    public function balances(){
        return $this->belongsToMany(Balance::class, 'report_balances', 'report_id');
    }
}