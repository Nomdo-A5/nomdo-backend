<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Boards;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;

class TaskController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if($request->board_id){
            //check if board is available 
            $board = Boards::find($request->board_id);
            if(!$board){
                return response()->json([
                    'message' => 'Board unavailable',
                    'board' => $board,
                ],404);
            }

            $this->userAccess($board);

            return response()->json([
                'task'    =>  $board->tasks
            ],200);
        }

        if($request->id){
            $task = Task::firstWhere('id', $request->id);
            if(!$task){
                return response()->json([
                    'task' =>$task,
                    'message' => 'task unavalable',
                ],404);
            }
            
            $is_member = $task->users()->where('user_id', $user->id)->first();
            if(!$is_member){
                return response()->json([
                    'task' => $task,
                    'message' => 'Access denied'
                ],405);
            }

            return response()->json([
                'task' => $task,
            ],200);
        }
        //showing all task for every user
        return response()->json([
            'task' => $user->tasks
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        //check input from user
        $validator = Validator::make($request->all(),[
            'task_name' => 'required',
            'task_description' => 'string',
            'due_date' => 'date|date_format:Y-m-d',
            'board_id' => 'required'
        ],
        [
            'task_name.required' => 'Input task name !',
            'board_id.required' => 'Board needed'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ],400);
        }

        //check if board is available 
        $board = Boards::find($request->board_id);
        if(!$board){
            return response()->json([
                'message' => 'Board unavailable',
                'board' => $board,
            ],404);
        }

        //check user access with board founded
        $this->userAccess($board);      
        $user = Auth::user();
        
        $task = new Task([
            'task_name' => $request->task_name,
            'task_description' => $request->task_description,
            'due_date' => $request->due_date,
        ]);

        $task->save();
        $board->tasks()->attach($task->id);
        $user->tasks()->attach($task->id);
        
        return response()->json([
            'task' => $task,
            //'member' => $data->users,           
        ], 200);
    }
   
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $user = Auth::user();
        $task = Task::firstWhere('id', $request->id);
        
        if($task == null){
            return response()->json([
                'task' => $task,
                'message' => 'Task unavailable'
            ],404);
        }

        if($request->task_name)
            $task->task_name = $request->task_name;
        if($request->task_description)
            $task->task_description = $request->task_description;
        if($request->status)
            $task->status = $request->status;
        if($request->member_id){
            $user = User::firstWhere('id', $request->member_id);
            
            $user->tasks()->attach($task->id);
            return response()->json([
                'task' => $task,
                'message' => 'Task member update'
            ],200);
        }

        if($request->member_id){
            $user = User::firstWhere('id', $request->member_id);
            
            $user->tasks()->attach($task->id);
            return response()->json([
                'task' => $task,
                'message' => 'Task member update'
            ],200);
        }

        $task->save();
        return response()->json([
            'task' => $task,
            'message' => 'task updated'
        ],200);

    }

    public function delete(Request $request){
        $task = Task::firstWhere('id', $request->id);
        if($task){
            Task::destroy($request->id);
            return response()->json([
                'message' => 'Task deleted'
            ],200);
        }
        else{
            return response()->json([
                'message' => 'failed to delete workspace'
            ],404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Checking user access to existing board
     */
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
