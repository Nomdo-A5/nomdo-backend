<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Balance;

class BalanceController extends Controller
{

    public function index(){

    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[

            'nominal' => 'required|integer',

        ],
        [
            'nominal.required' => 'Input nominal balance!',
            'report_id.required' => 'report is needed'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ],400);
        }
        $report = Report::find($request->report_id);
        if(!$report){
            return response()->json([
                'message' => 'Report unavailable',
                'report' => $report,
            ],404);
        }
        $user = Auth::user();

        $Balance = new Balance([
            'balance_description' => $request->balance_description,
            'nominal' => $request->nominal,
            'is_income' => $request->is_income,
        ]);

        $Balance->save();
                  if ($Balance) {
                return response()->json([
                    'message' => 'Balance created',
                    'board'   => $Balance,
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
            // if($request->task_name)
            //     $task->task_name = $request->task_name;
            // if($request->task_description)
            //     $task->task_description = $request->task_description;
            // if($request->status)
            //     $task->status = $request->status;
            // if($request->member_id){
            //     $user = User::firstWhere('id', $request->member_id);

            //     $user->tasks()->attach($task->id);
            //     return response()->json([
            //         'task' => $task,
            //         'message' => 'Task member update'
            //     ],200);
            // }

            // if($request->member_id){
            //     $user = User::firstWhere('id', $request->member_id);

            //     $user->tasks()->attach($task->id);
            //     return response()->json([
            //         'task' => $task,
            //         'message' => 'Task member update'
            //     ],200);
            // }

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
    }
