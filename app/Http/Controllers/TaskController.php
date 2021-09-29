<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //showing all task for every user
        return response()->json(Task::all(),200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        //$user = Auth::user();

        $request->validate([
            'task_name'=>'required',
        ]);

        $data = new Task([
            'task_name' => $request->get('task_name')
        ]);

        $data->save();
        $task_id = $data->id;
        //$user->tasks()->attach($task_id);
        
        return response()->json([
            'task' => $data,           
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        
        $task->save();
        return response()->json([
            'task' => $task,
            'message' => 'task updated'
        ],200);

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
}
