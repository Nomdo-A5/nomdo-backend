<?php

namespace App\Http\Controllers;
use App\Models\Report;
use App\Models\User;
use App\Models\Balance;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;

class ReportController extends Controller
{

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

  public function select(Request $request){
    $validator = Validator::make($request->all(),[
      'workspace_id' => 'required'
    ],[
      'workspace_id.required' => 'Workspace id is needed'
    ]);

    if($validator->fails()){
      return response()->json([
        'message' => 'Please check your input'
      ],400);
    }

    //cek apakah workspace tersebut ada 
    $workspace = Workspace::firstWhere('id', $request->workspace_id);
    if(!$workspace){
      return response()->json([
        'message' => 'Workspace unavailable'
      ],404);
    }

    $user = Auth::user();
    $this->userAccess($user,$workspace);

    //cek report pada workspace tersebut
    $report = Report::firstWhere('workspace_id', $workspace->id);
    if(!$report){
      return response()->json([
        'message' => 'Report unavailable'
      ],404);
    }

    //select seluruh balance dalam satu report
    $balance = $report->balances()->get();
    return response()->json([
      'balance' => $balance,
      'message' => 'Balance fetched'
    ],200);
  }
  public function userAccess(User $user, Workspace $workspace){    
    if(!($user->workspaces()->where('workspace-id', $workspace->id)))
        return response()->json([
          'message' => 'Access denied'
        ],403);
  }
}