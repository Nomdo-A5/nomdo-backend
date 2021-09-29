<?php

namespace App\Http\Controllers;

use App\Models\Boards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class BoardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $boards = Boards::latest()->all();
        return response([
            'success' => true,
            'message' => 'List Semua Posts',
            'data' => $boards
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'nama_boards' => 'required|unique:boards'
        ],

        [
            'nama_boards.required' => 'Masukkan Nama board !',

        ]
    );

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Silahkan Check Kembali nama Boards',
                'data'    => $validator->errors()
            ],400);
        }else{
            $createboards = Boards::create($request->all());
            if ($createboards) {
                return response()->json([
                    'success' => true,
                    'message' => 'nama boards Berhasil Disimpan!',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'nama boards Gagal Disimpan!',
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

        $checkboards = boards::find($id);
        if(!$checkboards){

            return response()->json([
                'message' => 'Boards unavailable'
            ],404);
        }

        $validator = Validator::make($request->all(), [
            'nama_boards' => 'required|unique:boards'

        ],
            [
                'nama_boards.required' => 'Masukkan Nama board !',

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
        $deleteboards = boards::findOrFail($id);
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

}
