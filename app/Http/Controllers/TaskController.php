<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Boards;
use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Notifications\TaskNotif;

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

        if ($request->board_id) {

            //check if board is available
            $board = Boards::find($request->board_id);
            if (!$board) {
                return response()->json([
                    'message' => 'Board unavailable',
                    'board' => $board,
                ], 404);
            }

            $this->userAccess($board);

            if ($request->is_done != null) {
                $task = $board->tasks
                    ->where('is_done', $request->is_done);

                if ($request->is_done == 1) {
                    $task_done = $task->values();
                    
                    return response()->json([
                        'message' => 'Task done',
                        'tasks' => $task_done
                    ], 200);
                } else if ($request->is_done == 0) {
                    if ($request->due_date != null) {
                        if ($request->due_date == "Today") {
                            $task_date = $task
                                ->where('due_date', Carbon::today()->toDateString())
                                ->values();

                            return response()->json([
                                'message' => 'Task Today',
                                'tasks' => $task_date
                            ], 200);
                        } else if ($request->due_date == "Overdue") {
                            $task_date = $task
                                ->where('due_date', '<', Carbon::now()->subDay()->toDateString())
                                ->values();
                            return response()->json([
                                'message' => 'Task overdue',
                                'tasks' => $task_date
                            ], 200);
                        } else if ($request->due_date == "Week") {
                            $task_date = $task
                                ->where('due_date', '<', Carbon::today()->addDays(7)->toDateString())
                                ->where('due_date', '>', Carbon::today()->toDateString())
                                ->values();
                            return response()->json([
                                'message' => 'Task week',
                                'tasks' => $task_date
                            ], 200);
                        } else if ($request->due_date == "Later") {
                            $task_date = $task
                                ->where('due_date', '>', Carbon::today()->addDays(7)->toDateString())
                                ->values();
                            return response()->json([
                                'message' => 'Task Later',
                                'tasks' => $task_date
                            ], 200);
                        }
                    }

                    return response()->json([
                        'message' => 'Task not done',
                        'tasks' => $task
                    ], 200);
                }
            }

            return response()->json([
                'task'    =>  $board->tasks
            ], 200);
        }

        if ($request->id) {
            $task = Task::firstWhere('id', $request->id);
            if (!$task) {
                return response()->json([
                    'task' => $task,
                    'message' => 'task unavalable',
                ], 404);
            }

            $is_member = $task->users()->where('user_id', $user->id)->first();
            if (!$is_member) {
                return response()->json([
                    'task' => $task,
                    'message' => 'Access denied'
                ], 405);
            }

            return response()->json([
                'task' => $task,
            ], 200);
        }

        //showing all task for every user
        return response()->json([
            'task' => $user->tasks
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        //check input from user
        $validator = Validator::make(
            $request->all(),
            [
                'task_name' => 'required',
                'task_description' => 'string',
                'due_date' => 'date|date_format:Y-m-d',
                'board_id' => 'required'
            ],
            [
                'task_name.required' => 'Input task name !',
                'board_id.required' => 'Board needed'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ], 400);
        }

        //check if board is available
        $board = Boards::find($request->board_id);
        if (!$board) {
            return response()->json([
                'message' => 'Board unavailable',
                'board' => $board,
            ], 404);
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

        if ($task == null) {
            return response()->json([
                'task' => $task,
                'message' => 'Task unavailable'
            ], 404);
        }

        if ($request->task_name)
            $task->task_name = $request->task_name;
        if ($request->task_description)
            $task->task_description = $request->task_description;
        if ($request->due_date)
            $task->due_date = $request->due_date;
        $task->is_done = $request->is_done;

        if ($request->member_id) {
            $user = User::firstWhere('id', $request->member_id);
            if (!$user) {
                return response()->json([
                    'message' => 'User unavailable'
                ], 403);
            }
            $user->tasks()->attach($task->id);
            return response()->json([
                'task' => $task,
                'message' => 'Task member update'
            ], 200);
        }


        $task->save();
        return response()->json([
            'task' => $task,
            'message' => 'task updated'
        ], 200);
    }

    public function delete(Request $request)
    {
        $task = Task::firstWhere('id', $request->id);
        if ($task) {
            Task::destroy($request->id);
            return response()->json([
                'message' => 'Task deleted'
            ], 200);
        } else {
            return response()->json([
                'message' => 'failed to delete workspace'
            ], 404);
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
    public function userAccess(Boards $board)
    {
        $user = Auth::user();
        $workspace = $board->workspace;
        $member = $workspace->users()->where('user_id', $user->id)->get();

        if (!$member) {
            return response()->json([
                'message' => 'Access denied'
            ], 403);
        }
    }

    public function getMember(Request $request)
    {
        $task = Task::firstWhere('id', $request->task_id);
        if (!$task) {
            return response()->json([
                'message' => 'Task unavailable'
            ], 404);
        }
        return response()->json([
            'member' => $task->users()->get()
        ], 200);
    }
    public function filter(Request $request)
    {

        if ($request->is_done == 1) {
            $tasks = DB::table('tasks')->where('is_done', $request->is_done)->get();
            return response()->json([
                'message' => 'Task done',
                'tasks' => $tasks
            ], 200);
        } else if ($request->is_done == 0) {
            $tasks = DB::table('tasks')->where('is_done', $request->is_done)->get();
            return response()->json([
                'message' => 'Task not done',
                'tasks' => $tasks
            ], 200);
        }
    }
    public function filterdue_date(Request $request)
    {
        if (($request->is_done == 0) && ($request->due_date == "Today")) {
            $tasks = DB::table('tasks')
                ->where('is_done', $request->is_done)
                ->whereDate('due_date', Carbon::today())->get();

            return response()->json([
                'message' => 'Task Today',
                'tasks' => $tasks
            ], 200);
        } else if (($request->is_done == 0) && ($request->due_date == "overdue")) {
            $fdate = now();
            $datetime1 = new DateTime($fdate);
            $tasks = DB::table('tasks')
                ->whereDate('due_date', '<', $datetime1)
                ->get();
            return response()->json([
                'message' => 'Task overdue',
                'tasks' => $tasks
            ], 200);
        } else if (($request->is_done == 0) && ($request->due_date == "week")) {
            $tasks = DB::table('tasks')->whereDate('due_date', '<', Carbon::now()->subDays(7)->toDateTimeString())->get();
            return response()->json([
                'message' => 'Task week',
                'tasks' => $tasks
            ], 200);
        } else if (($request->is_done == 0) && ($request->due_date == "later")) {
            $tasks = DB::table('tasks')->whereDate('due_date', '>', Carbon::now()->subDays(7)->toDateTimeString())->get();
            return response()->json([
                'message' => 'Task Later',
                'tasks' => $tasks
            ], 200);
        }
    }
    public function tasknotif(Request $request)
    {
        if ($request->board_id) {
            $board = Boards::find($request->board_id);
            if (!$board) {
                return response()->json([
                    'message' => 'Board unavailable',
                    'board' => $board,
                ], 404);
            }

            $this->userAccess($board);


            if ($request->task_id) {
                $tasks = $board->tasks
                    ->where('task_id', $request->task_id);
                $tasks = DB::table('tasks')->whereDate('due_date', '<', Carbon::now()->subDays(7)->toDateTimeString())->get();
                $user = User::where('id', '=', 'member_id')->get();
                $user->notify(new TaskNotif);

                return response()->json([
                    'message' => 'Task Mendekati deadline',
                    'tasks' => $tasks
                ], 200);
            }
        }
    }
}