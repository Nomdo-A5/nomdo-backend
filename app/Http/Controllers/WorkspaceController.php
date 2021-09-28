<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->id){
            $check = Workspace::firstWhere('id', $request->id);
            if(!$check)
                return response()->json([
                    'message' => 'Workspace Unavailable'
                ],404);

            $workspace = Workspace::find($request->id);
            
            return response()->json([
                'workspace' => $workspace,
                'message' => 'Workspace fetched'
            ],200);
        }
        //get all data whoever the user 
        return response()->json(Workspace::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //mengambil data user yang sedang aktif
        $user = Auth::user();

        $request->validate([
            'workspace_name'=>'required',
        ]);

        $data = new Workspace([
            'workspace_name' => $request->get('workspace_name')
        ]);

        $data->save();

        $workspace_id = $data->id;
        $user->workspaces()->attach($workspace_id);
        
        return response()->json([
            'workspace' => $data,           
            'message' => 'workspace created'
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
     * @param  \App\Models\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    //public function show(Workspace $workspace)
    public function show(Request $request)
    {
        //
        $user = Auth::user();
        if($user->workspace == null){
            return response()->json([
                'message' => 'Workspace Unavailable'
            ],404);
        }
        return response()->json([
            'workspaces' => $user->workspaces,
            'message' => "Workspace fetched"
        ],200);
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function edit(Workspace $workspace)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $check = Workspace::firstWhere('id', $request->id);
        if(!$check){
            
            return response()->json([
                'message' => 'Workspace unavailable'
            ],404);
        }

        $workspace = Workspace::where('id', $request->id);
        $workspace->workspace_name = $request->name;
        $workspace->save();

        return response()->json([
            'workspace' => $workspace,
            'message' => 'Workspace updated'
        ],200);
        
    }
    /**
     * Delete the specific object
     */
    public function delete(Request $request){
        $check = Workspace::firstWhere('id', $request->id);
        if($check){
            Workspace::destroy($id);
            return response()->json([
                'message' => 'Workspace deleted'
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
     * @param  \App\Models\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function destroy(Workspace $workspace)
    {
        //
    }
}
