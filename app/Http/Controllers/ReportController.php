<?php

namespace App\Http\Controllers;
use App\Models\Report;
use App\Models\User;
use App\Models\Balance;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
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

  public function create(int $workspace_id){
    //mengambil user yang sedang aktif
    $user = Auth::user();

    //apakah workspace nya ada 
    $workspace = Workspace::firstWhere('id', $workspace_id);
    if(!$workspace){
      return response()->json([
        'message' => 'workspace unavailable'
      ],404);
    }

    //kalau workspace nya ada cek user access nya ke workspace tersebut
    $this->userAccess($user,$workspace);

    //user punya access maka create report
    $report_name = $workspace->workspace_name .' report';

    $report = new Report([
      'workspace_id' => $workspace->id,
      'report_name' => $report_name
    ]);
    $report->save();
  }

  public function userAccess(User $user, Workspace $workspace){
    
    if(!($user->workspaces()->where('workspace-id', $workspace->id)))
        return response()->json([
          'message' => 'Access denied'
        ],403);
  }
}