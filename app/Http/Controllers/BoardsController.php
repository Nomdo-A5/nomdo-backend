<?php

namespace App\Http\Controllers;

use App\Models\Boards;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BoardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Checking active user
        $user = Auth::user();

        //Find user relation with requested workspace
        if ($request->workspace_id) {
            $workspace = $user->workspaces()->where('workspace_id', $request->workspace_id)->first();
            if (!$workspace) {
                return response()->json([
                    'workspace' => $workspace,
                    'message'   => 'workspace unavailable'
                ], 404);
            }
        }

        $boards = $workspace->boards()->get();
        //if user have relation with requested workspace then fetch all boards
        if ($request->id) {
            $boards = $workspace->boards()->where('id', $request->id)->first();
        }


        if (count($boards) == 0) {

        if(!$boards){

            return response()->json([
                'message' => 'Boards unavailable'
            ], 404);
        }
        return response()->json([
            'boards' => $boards,
            'message' => 'boards fetched'
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
        $validator = Validator::make(
            $request->all(),
            [
                'board_name' => 'required|unique:boards',
                'board_description' => 'string',
                'workspace_id' => 'required'
            ],
            [
                'board_name.required' => 'Input board name !',
                'workspace_id.required' => 'Workspace is needed'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ], 400);
        }

        //Checking if the workspaces is exist
        $workspace = DB::table('workspaces')->where('id', $request->workspace_id)->first();

        if ($workspace == null) {
            return response()->json([
                'workspace' => $workspace,
                'message' => 'workspace unavailable'
            ], 404);
        }

        //check if the active user has connection with choosed workspace
        $user = Auth::user();
        $workspace = $user->workspaces()->where('workspace_id', $request->workspace_id)->first();

        //user doesn't have connection with the choosen workspace
        if (!$workspace) {
            return response()->json([
                'message' => 'access denied'
            ], 405);
        }

        if ($workspace->pivot->is_owner == true || $workspace->pivot->is_admin == true) {
            $new = new Boards([
                'board_name' => $request->board_name,
                'board_description' => $request->board_description,
                'workspace_id' => $request->workspace_id,
            ]);

            $new->save();
            if ($new) {
                return response()->json([
                    'message' => 'Board created',
                    'board'   => $new,
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'failed create new board ',
                ], 400);
            }
        }
        return response()->json([
            'message' => "Acces denied"
        ], 403);
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
        $board = Boards::find($request->id);
        if (!$board) {
            return response()->json([
                'message' => 'Boards unavailable',
            ], 404);
        }

        $user = Auth::user();
        $workspace = $board->workspace;
        $member = $workspace->users()->where('user_id', $user->id)->first();

        if (!$member || $member->pivot->is_owner == false || $member->pivot->is_admin == false) {
            return response() - json([
                'message' => 'Access Denied'
            ], 405);
        }
        $validator = Validator::make($request->all(), [
            'board_name' => 'string',
            'board_description' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Silahkan Isi Board Yang Kosong',
                'data'    => $validator->errors()
            ], 400);
        }

        $message = "";
        if ($request->board_name) {
            $board->board_name =  $request->board_name;
            $message = "Board name updated";
        }
        if ($request->board_description) {
            $board->board_description = $request->board_description;
            $message .= " Board description updated";
        }

        $board->save();


        if ($board) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed boards updated!',
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //$deleteboards = Boards::findOrFail($request->id);
        $board = Boards::find($request->id);
        if (!$board) {
            return response()->json([
                'message' => 'Boards unavailable',
            ], 404);
        }

        $user = Auth::user();
        $workspace = $board->workspace;
        $member = $workspace->users()->where('user_id', $user->id)->first();

        if (!$member || $member->pivot->is_owner == false || $member->pivot->is_admin == false) {
            return response() - json([
                'message' => 'Access Denied'
            ], 405);
        }

        $board->delete();

        if ($board) {
            return response()->json([
                'success' => true,
                'message' => 'boards deleted',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed to delete boards',
            ], 500);
        }
    }

    /**
     * Checking access user of constrained workspaces
     */
    public function userAccess($id)
    {
        $user = Auth::user();
        $board = Board::findOrFail($id);

        $workspace = $board->workspace();
        $workspace = $user->workspaces()->where('workspace_id', $workspace->id)->first();

        if (!$workspace) {
            return response()->json([
                'message' => 'access denied'
            ], 405);
        }

        return $workspace;
    }

    public function taskCount(Request $request)
    {
        $board = $this->getBoard($request->board_id);
        $tasks = $this->allTask($board);
        if (count($tasks) == 0) {
            return response()->json([

                'message' => 'task empty'
            ], 404);

                'task_count' => count($tasks),
                'done_task' => 0
            ],200);

        }
        $done_task = count($this->doneTask($board));
        return response()->json([
            'task_count' => count($tasks),
            'done_task' => $done_task
        ], 200);
    }

    public function taskCountArray($id)
    {
        $board = $this->getBoard($id);
        $tasks = $this->allTask($board);
        if (count($tasks) == 0) {
            return ([
                'task_count' => 0,
                'done_task' => 0
            ]);
        }
        $done_task = count($this->doneTask($board));
        return ([
            'task_count' => count($tasks),
            'done_task' => $done_task
        ]);
        // $workspace = Workspace::firstWhere('id', $request->workspace_id);
        // $boards_controller = new BoardsController;
        // $boards = $boards_controller->getBoardOfWorkspace($workspace);
        // if (count($boards) == 0) {
        //     return response()->json([
        //         'message' => 'Boards unavailable',
        //     ], 404);
        // }

        // $tasks = array();
        // foreach ($boards as $board) {
        //     $tasks[] = $this->allTask($board);

        //     if (count($tasks) == 0) {
        //         return response()->json([
        //             'message' => 'task empty'
        //         ], 404);
        //     }
        //     $done_task = count($this->doneTask($board));
        // }

        // return response()->json([
        //     'task_count' => count($tasks),
        //     'done_task' => $done_task
        // ], 200);
    }

    public function allTask(Boards $board)
    {
        return $board->tasks()->get();
    }

    public function doneTask(Boards $board)
    {
        return $board->tasks()->where('is_done', 1)->get();
    }
    public function getBoard($id)
    {
        return Boards::firstWhere('id', $id);
    }
    public function getBoardOfWorkspace(Workspace $workspace)
    {
        return $workspace->boards()->get();
    }
}