<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_income',
        'nominal',
        'balance_description',
    ];
    public function report(){
        return $this->belongsTo(Report::class, 'report_id', 'id');
    }
}