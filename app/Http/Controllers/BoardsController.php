<?php

namespace App\Http\Controllers;

use App\Models\Boards;
use App\Models\Workspaces;
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
        $workspace = $user->workspaces()->where('workspace_id' , $request->workspace_id)->first();
        if(!$workspace){
            return response()->json([
                'workspace' => $workspace,
                'message'   => 'workspace unavailable'
            ],404);
        }

        //if user have relation with requested workspace then fetch all boards
        if(!$request->id)
            $boards = DB::table('boards')
                        ->where('workspace_id', $request->workspace_id)->get();        
        else{
            $boards = DB::table('boards')
                ->where([
                    ['workspace_id', $request->workspace_id],
                        ['id', $request->id],
                    ])->get();        
        
        }
    
        if($boards->isEmpty()){
            return response()->json([
                'boards' => $boards,
                'message' => 'Boards unavailable'
            ],404);
        }
        return response()->json([
            'boards' => $boards
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
        $validator = Validator::make($request->all(),[
            'board_name' => 'required|unique:boards',
            'workspace_id' => 'required'
        ],
        [
            'board_name.required' => 'Input board name !',
            'workspace_id.required' => 'Workspace is needed'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ],400);
        }
         
        //Checking if the workspaces is exist         
        $workspace = DB::table('workspaces')->where('id', $request->workspace_id)->first();
        
        if($workspace == null){
            return response()->json([
                'workspace' => $workspace,
                'message' => 'workspace unavailable'
            ], 404);
        }

        //check if the active user has connection with choosed workspace
        $user = Auth::user();
        $workspace = $user->workspaces()->where('workspace_id' , $request->workspace_id)->first();

        //user doesn't have connection with the choosen workspace
        if(!$workspace){
            return response()->json([
                'message' => 'access denied'
            ], 405);
        }

        if($workspace->pivot->is_owner == true || $workspace->pivot->is_admin == true){
            $new = new Boards([
                'board_name' => $request->board_name,
                'board_description' => $request->board_description,
                'workspace_id' => $request->workspace_id,
            ]);

            $new -> save();
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
    public function update(Request $request, $id)
    {
        $checkboards = Boards::find($id);
        if(!$checkboards){

            return response()->json([
                'message' => 'Boards unavailable'
            ],404);
        }

        $validator = Validator::make($request->all(), [
            'boards_name' => 'required|unique:boards'

        ],
            [
                'boards_name.required' => 'Masukkan Nama board !',

            ]
        );
        if($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Silahkan Isi Board Yang Kosong',
                'data'    => $validator->errors()
            ],400);

        }else{
            $updateboards = Boards::find($id);
            $updateboards->nama_boards =  $request->get('nama_boards');
            $updateboards->save();
        if($updateboards){
            return response()->json([
                'success' => true,
                'message' => 'Boards updated!',
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Failed boards updated!',
            ], 400);
        }

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
        $deleteboards = Boards::findOrFail($id);
        $deleteboards->delete();

        if ($deleteboards) {
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

    public function userAccess(BigInt $id){
        
    }

}
