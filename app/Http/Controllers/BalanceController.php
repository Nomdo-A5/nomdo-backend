<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Balance;
use App\Models\Report;

class BalanceController extends Controller
{

    public function index(Request $request){

        //fetch specific balance with balance id as id
        if($request->id){
            $balance = Balance::find($request->id);
            if(!$balance){
                return response()->json([
                    'message' => 'Balance unavailable',
                    'balance' => $balance,
                ],404);
            }
            return response()->json([
                'balance' => $balance,
            ],200);
        }

        // fetch all balance in one report
        if($request->report_id){
            $report = Report::find($request->report_id);
            if(!$report){
                return response()->json([
                    'message' => 'Report unavailable',
                    'report' => $report,
                ],404);
            }
            $balance = $report->balances();
            if($balance != null){
                return response()->json([
                    'balance' => $balance
                ],200);
            }
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nominal' => 'required|integer',
            'is_income' => 'required|boolean',
            'report_id' => 'required',
        ],
        [
            'nominal.required' => 'Input nominal balance!',
            'is_income.required' => 'Income status is needed',
            'report_id.required' => 'report is needed'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ],400);
        }

        //check apakah report tersebut ada
        $report = Report::find($request->report_id);
        if(!$report){
            return response()->json([
                'message' => 'Report unavailable',
                'report' => $report,
            ],404);
        }

        //cek user access kedalam report tersebut 
        $user = Auth::user();

        $balance = new Balance([
            'balance_description' => $request->balance_description,
            'nominal' => $request->nominal,
            'is_income' => $request->is_income,
        ]);

        $balance->save();
        if ($balance) {
            return response()->json([
                'message' => 'Balance created',
                'board'   => $balance,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed create new balance ',
            ], 400);
        }
    }

    public function update(Request $request)
    {
            //
        $user = Auth::user();
        $balance = Balance::firstWhere('id', $request->id);
            if($balance == null){
                return response()->json([
                    'report' => $report,
                    'message' => 'Report unavailable'
                ],404);
            }
        if($balance){
            $balance->is_income=$request->is_income;
            $balance->nominal=$request->nominal;
            $balance->balance_description=$request->balance_description;
        }
        $balance->save();
        return response()->json([
            'balance' => $balance,
            'message' => 'task updated'
        ],200);
    }

    public function delete(Request $request){
        $balance = Balance::firstWhere('id', $request->id);
        if($balance){
            Balance::destroy($request->id);
            return response()->json([
                'message' => 'Balance deleted'
            ],200);
        }
        else{
            return response()->json([
                'message' => 'failed to delete workspace'
            ],404);
        }
    }
    public function userAccess(Boards $board){
        $user = Auth::user();
        $workspace = $board->workspace;
        $member = $workspace->users()->where('user_id', $user->id)->get();

        if(!$member){
            return response()->json([
                'message' => 'Access denied'
            ],403);
        }
    }

}
  
