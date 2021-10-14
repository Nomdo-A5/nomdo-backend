<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
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
            'workspace_name'=> 'required',
        ]);

        $data = new Workspace([
            'workspace_name' => $request->get('workspace_name')
        ]);

        $data->save();

        $workspace_id = $data->id;
        $user->workspaces()->attach($workspace_id);
        $user->workspaces()->updateExistingPivot($workspace_id,['is_owner' => true , 'is_admin' => true]);       

        return response()->json([
            'workspace' => $data,      
            'message' => 'workspace created'
        ], 201);
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
        if($request->id){
            $workspace = $user->workspaces()->where('workspace_id' , $request->id)->first();
            if(!$workspace){
                return response()->json([
                    'workspace' => $workspace,
                    'message' => 'workspace unavailable',
                ],404);
            }
            return response()->json($workspace,200);
        }
        return response()->json([
            'workspace' => $user->workspaces()->get(), 
        ],200);
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
        $user = Auth::user();
        $is_updated = false;

        //Validate request
        $request->validate([
            'id' => 'required',
            'workspace_name' => 'string',
            'member_id' => 'bigInt',
        ]);
        
        //mengecek apakah workspace dengan ID tersebut adalah milik user yang sedang aktif  
        $workspace = $user->workspaces()->where('workspace_id' , $request->id)->first();
        if(!$workspace){            
            return response()->json($workspace,404);
        }

        //Checking the user access on that workspace
        if($workspace->pivot->is_owner == true || $workspace->pivot->is_admin == true){
            //Update name
            if($request->workspace_name){
                $this->updateName($workspace,$request->workspace_name);
                $is_update = true;
            }
                       
            //adding member
            if($request->member_id){
                $this->addMember($workspace, $request->member_id);
                $is_update = true;
            }
            
        }
        else{
            return response()->json([
                'message' => "access denied",
            ],405);
        }

        //jika terjadi perubahan
        if($is_update){
            return response()->json([
                'workspace' => $workspace,
                'message' => 'Workspace updated'
            ],200);
        }
        else{
            return response()->json($workspace,304);
        }
        
        
    }
    /**
     * Updating workspace name
     */
    public function updateName(Workspace $workspace,String $name){
        $workspace->workspace_name = $name;
        $workspace->save();
    }

    /**
     * Updating member of workspace
     */
    public function addMember(Workspace $workspace, BigInt $id){
        $workspace->user()->attach($id);
    }
    /**
     * Delete the specific object
     */
    public function delete(Request $request){
        //check hak akses user terhadap worksapce tersebut 
        $user = Auth::user();

        $workspace = $user->workspaces()->where('workspace_id' , $request->id)->first();
        if(!$workspace){            
            return response()->json($workspace,404);
        }

        //checking user access permission
        if($workspace->pivot->is_owner == true){
            Workspace::destroy($workspace->id);
            return response()->json([                
                'message' => 'workspace deleted'
            ],200);
        }
        else{
            return response()->json([
                'message' => 'access denied'
            ] ,405);
        }

        // $check = Workspace::firstWhere('id', $request->id);
        // if($check){
        //     Workspace::destroy($request->id);
        //     return response()->json([
        //         'message' => 'Workspace deleted'
        //     ],200);
        // }
        // else{
        //     return response()->json([
        //         'message' => 'failed to delete workspace'
        //     ],404);
        // }
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
