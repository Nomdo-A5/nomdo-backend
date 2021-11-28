<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BoardsController;

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
            'workspace_description' => 'string',
        ]);

        $data = new Workspace([
            'workspace_name' => $request->get('workspace_name'),
            'workspace_description' => $request->get('workspace_description'),
            'url_join' => $this->unique_code(16),
        ]);

        $data->save();

        $workspace_id = $data->id;
        $user->workspaces()->attach($workspace_id);
        $user->workspaces()->updateExistingPivot($workspace_id,['is_owner' => true , 'is_admin' => true]);
        $report_controller = new ReportController;
        $report_controller->create($workspace_id);


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
    public function addMember(Workspace $workspace, int $id){
        $workspace->users()->attach($id);
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

    }
    // Return unique token for url_join
    public function unique_code($limit){
        $token = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
        $check = Workspace::firstWhere('url_join', $token);

        while($check){
            $token = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
            $check = Workspace::firstWhere('url_join', $token);
        }

        return $token;
    }

    public function join(Request $request){
        $user = Auth::user();
        $is_update = false;

        $request->validate([
            'url_join' => 'required',
            'member_id' => 'required'
         ]);
         $device = $user->device_token;

         $workspace = Workspace::firstWhere('url_join', $request->url_join);
         if($workspace){
            $member = User::firstWhere('id', $request->member_id);
            $message = $workspace->workspace_name . $member->name;
            $this->addMember($workspace, $request->member_id);
            $is_update = true;

         }

         if($is_update){
            auth()->user()->notify(new WorkspaceJoin);
            return response()->json([
                'workspace' => $workspace,
                'message' => 'Workspace joined'
            ],200);
        }
        else{
            return response()->json($workspace,404);
        }
    }

    public function getTaskInfo(Request $request){
        $workspace = Workspace::firstWhere('id', $request->workspace_id);
        $boards_controller = new BoardsController;
        $boards = $boards_controller->getBoardOfWorkspace($workspace);
        if(count($boards) == 0){
            return response()->json([
                'message' => 'board unavailable',
            ],404);
        }
        $task_count = 0;
        $task_done = 0;
        foreach($boards as $board){
            $task_count += count($boards_controller->allTask($board));
            $task_done += count($boards_controller->doneTask($board));
        }
        return response()->json([
            'task_count' => $task_count,
            'task_done' => $task_done
        ],200);
    }
    public function getMember(Request $request){
        $workspace = Workspace::firstWhere('id', $request->workspace_id);
        if(!$workspace){
            return response()->json([
                'message' => 'workspace unavailable'
            ],404);
        }

        return response()->json([
            'member' => $workspace->users()->get()
        ],200);
    }
    // public function sendNotification($device_token, $message)
    // {
    //     $SERVER_API_KEY = 'ServerAPIKey';

    //     // payload data, it will vary according to requirement
    //     $data = [
    //         "registration_ids" => $device_token, // for single device id
    //         "notification" => $message
    //     ];
    //     $dataString = json_encode($data);

    //     $headers = [
    //         'Authorization: key=' . $SERVER_API_KEY,
    //         'Content-Type: application/json',
    //     ];

    //     $ch = curl_init();

    //     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

    //     $response = curl_exec($ch);

    //     curl_close($ch);

    //     return $response;
    // }
}