<?php

namespace App\Http\Controllers;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(){
        $report = DB::table('users')
        ->leftJoin('workspace', 'users.id', '=', 'workspace.id')
        ->leftJoin('task', 'users.id', '=', 'task.id')
        ->leftJoin('balance', 'users.id', '=', 'balance.id')
       ->select('workspace.nama','task.nama','balance.incomme','tbalance.outcome')
        ->where('users.id', '=', $id)
        ->get();

    return response()->json($report, 200);


    }
}