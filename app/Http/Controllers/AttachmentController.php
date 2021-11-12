<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function create(){
        $validator = Validator::make($request->all(),[
            'file_path' => 'required|mimes:jpeg,png|max:1014',
        ],
        [
            'file_path.required' => 'Input File Attachment!',

        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ],400);
        }
        $task = Task::find($request->id_task);
        if(!$task){
            return response()->json([
                'message' => 'task unavailable',
                'task' => $task,
            ],404);
        }
        $file = $request->file('file_path');
        $nama_file = $file->getClientOriginalName();
        $tujuan_upload = 'file';
        $file->move($tujuan_upload, $nama_file);
        $attachment = new Attachment([
           'id_task'=> $task,
           'file_path'=> $nama_file
        ]);
        $attachment->save();

        if ($attachment) {
            return response()->json([
                'message' => 'Attachment created',
                'attachment'   => $attachment,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed create new attachment ',
            ], 400);
        }
    }
}
