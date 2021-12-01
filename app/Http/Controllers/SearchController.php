<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Boards;
use App\Models\Task;
class SearchController extends Controller
{
    public function search(Request $request){
        $search = $request->q;
        $user = DB::table('users')
        ->leftJoin('workspace','workspace.id','=','users.id')
        ->leftJoin('boards','boards.id','=','users.id')
        ->leftJoin('task','task.id','=','users.id')
        ->leftJoin('balances','balances.id','=','users.id')
        ->where('workspace_name','like',"%".$search."%")
        ->where('board_name','like',"%".$search."%")
        ->where('task_name','like',"%".$search."%")
        ->where('balance_description','like',"%".$search."%")
        ->get();
        return response()->json([
            'user' => $user,
            'message' => 'search valid'
        ],200);
    }
}
