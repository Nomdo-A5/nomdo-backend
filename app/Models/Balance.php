<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;
    protected $fillable = [
        'balance_description',
        'date',
        'nominal',
        'is_income',
        'status',

    ];
    public function report(){
        return $this->belongsTo(Report::class, 'report_id', 'id');
    }

    public function attachment(){
        return $this->belongTo(Attachment::class,'attachment_id','id');
    }
}