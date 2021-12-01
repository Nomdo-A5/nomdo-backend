<?php

namespace App\Http\Controllers;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttachmentController extends Controller
{
    public function create(Request $request){
        $validator = Validator::make($request->all(),[
            'file_path' => 'required|mimes:jpeg,png|max:1014',
            'balance_id' => 'required',
        ],
        [
            'file_path.required' => 'Input File Attachment!',
            'balance_id.required' => 'Need balance_id!',

        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ],400);
        }
        $file = $request->file('file_path');
        $nama_file = $file->getClientOriginalName();
        $tujuan_upload = 'file';
        $file->move($tujuan_upload, $nama_file);
        $attachment = new Attachment([
           'file_path'=> $nama_file,
           'balance_id'=> $request->balance_id
        ]);
        $attachment->save();

        if ($attachment) {
            return response()->json([
                'message' => 'Attachment created',
                'attachment'   => $attachment,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed create new attachment ',
            ], 400);
        }
    }
}