<?php

namespace App\Http\Controllers;

use File;
use App\Models\Attachment;
use App\Models\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class AttachmentController extends Controller
{
    public function getDownload(Request $request)
    {
        $balance = Balance::find($request->balance_id);
        $attachment = Attachment::where('balance_id', $request->balance_id)->first();
        $file = public_path('file/' . $attachment->file_path);

        // if(!$attachment){
        //     return response()->json([
        //         'message' => 'Attachment unavailable',
        //         'balance' => $balance,
        //         'attachment' => $attachment,
        //         'file_path'=> $file_path
        //         ],404);
        // }
        // if($attachment){
        // return response()->json([
        //     'message' => 'Attachment download',
        //     'balance' => $balance,
        //     'attachment' => $attachment,
        //     'file_path'=> $file_path
        //     ],202);
        // }
        return response()->download($file);
    }
    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file_path' => 'required|mimes:jpeg,png,doc,docx,pdf|max:1014',
                'balance_id' => 'required',
            ],
            [
                'file_path.required' => 'Input File Attachment!',
                'balance_id.required' => 'Need balance_id!',
            // if(!$attachment){
            //     return response()->json([
            //         'message' => 'Attachment unavailable',
            //         'balance' => $balance,
            //         'attachment' => $attachment,
            //         'file_path'=> $file_path
            //         ],404);
            // }
            // if($attachment){
            // return response()->json([
            //     'message' => 'Attachment download',
            //     'balance' => $balance,
            //     'attachment' => $attachment,
            //     'file_path'=> $file_path
            //     ],202);
            // }
            return response()->download($file);
        }
    public function create(Request $request){
        $validator = Validator::make($request->all(),[

            'file_path' => 'required|mimes:jpeg,png,doc,docx,pdf|max:1014',
            'balance_id' => 'required',
        ],
        [
            'file_path.required' => 'Input File Attachment!',
            'balance_id.required' => 'Need balance_id!',

            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please check your inputs',
                'data'    => $validator->errors()
            ], 400);
        }
        $file = $request->file('file_path');
        $nama_file = $file->getClientOriginalName();
        $tujuan_upload = 'file';
        $file->move($tujuan_upload, $nama_file);
        $attachment = new Attachment([
            'file_path' => $nama_file,
            'balance_id' => $request->balance_id
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
    public function update($id, Request $request)
    {
        if ($request->file != null) {
            $balance = Balance::find($request->balance_id);
            $attachment = Attachment::where('balance_id', $balance->balance_id)->first();
            File::delete('file/' . $attachment->file);
            $file = $request->file('file_path');
            $nama_file = $file->getClientOriginalName();
            $tujuan_upload = 'laporan';
            $file->move($tujuan_upload, $nama_file);
            $attachment->file = $nama_file;
            $attachment->save();
            return response()->json([
                'message' => 'Attachment updated',
                'attachment'   => $attachment,
            ], 200);
        }
    }
}