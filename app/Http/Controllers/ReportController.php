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

    if($request->is_income != null){
      if($request->is_income == 1){
        if($request->status == "Done"){
          $balance = $report->balances()->where('is_income', 1)->where('status', "Done")->get();
        }else if($request->status == "Planned"){
          $balance = $report->balances()->where('is_income', 1)->where('status', "Planned")->get();
        }else{
          $balance = $report->balances()->where('is_income', 1)->get();
        }
      }else{
        if($request->status == "Done"){
          $balance = $report->balances()->where('is_income', 0)->where('status', "Done")->get();
        }else if($request->status == "Planned"){
          $balance = $report->balances()->where('is_income', 0)->where('status', "Planned")->get();
        }else{
          $balance = $report->balances()->where('is_income', 0)->get();
        }
      }
    }else{
      //select seluruh balance dalam satu report
      $balance = $report->balances()->get();
    }

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

  public function overview(Request $request){
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

    if($request->status != null){
        if($request->status == "Done"){
          $incomeBalance = $report->balances()->where('is_income', 1)->where('status', "Done")->sum('nominal');
          $outcomeBalance = $report->balances()->where('is_income', 0)->where('status', "Done")->sum('nominal');
        }else if($request->status == "Planned"){
          $incomeBalance = $report->balances()->where('is_income', 1)->where('status', "Planned")->sum('nominal');
          $outcomeBalance = $report->balances()->where('is_income', 0)->where('status', "Planned")->sum('nominal');
        }else{
          return response()->json([
            'message' => 'Invalid Status'
          ],404);
        }
    }else{
      $incomeBalance = $report->balances()->where('is_income', 1)->sum('nominal');
      $outcomeBalance = $report->balances()->where('is_income', 0)->sum('nominal');    
    }

    $totalBalance = $incomeBalance - $outcomeBalance;

    return response()->json([
      'income_balance' => $incomeBalance,
      'outcome_balance' => $outcomeBalance,
      'total_balance' => $totalBalance,
      'message' => 'Balance fetched'
    ],200);
  }
  
}