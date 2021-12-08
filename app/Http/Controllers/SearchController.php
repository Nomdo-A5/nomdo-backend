<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Boards;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
class SearchController extends Controller
{
    public function search(Request $request){
        $search = $request->q;
        $workspace = DB::table('users')
        ->leftJoin('workspaces','workspaces.id','=','users.id')
        ->where('workspace_name','like',"%".$search."%")
        ->get();
        $boards = DB::table('users')
        ->leftJoin('boards','boards.id','=','users.id')
        ->where('board_name','like',"%".$search."%")
        ->get();
        $tasks = DB::table('users')
        ->leftJoin('tasks','tasks.id','=','users.id')
        ->where('task_name','like',"%".$search."%")
        ->get();
        $balance = DB::table('users')
        ->leftJoin('balances','balances.id','=','users.id')
        ->where('balance_description','like',"%".$search."%")
        ->get();
        return response()->json([
            'workspace' => $workspace,
            'boards' => $boards,
            'tasks'=> $tasks,
            'balance' => $balance,
            'message' => 'search novalid'
        ],200);
    }
}
