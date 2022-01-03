<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::user()->id);

        return response()->json([
            'user' => $user,
            'message' => 'profile view'
        ], 200);
    }
    public function updateProfile(Request $request)
    {
        if ($request->file('file_profile')) {

            $file = $request->file('file_profile');
            $nama_file = $file->getClientOriginalName();
            $tujuan_upload = 'file/profile';
            $file->move($tujuan_upload, $nama_file);


            $user = User::find($request->id)
                ->update([

                    'file_profile' => $nama_file,

                ]);
            return response()->json([
                'user' => $user,
                'message' => 'profile updated'
            ], 200);
        } else {
            $user = User::find($request->id)
                ->update([

                    'name' => $request->name,
                    'email' => $request->email

                ]);
            return response()->json([
                'user' => $user,
                'message' => 'profile updated'
            ], 200);
        }
    }
    public function updateSetting(Request $request, $id)
    {
        $user = User::findOrFail($id);

        /*
        * Validate all input fields
        */
        $this->validate($request, [
            'password' => 'required',
            'konfirmasi_password' => 'confirmed|max:8|different:password',

        ]);

        if (Hash::check($request->password, $user->password)) {
            if (Hash::check($request->password, $request->konfirmasi_password)) {
                $user1 = User::find(Auth::user()->id)->update([
                    'password' => Hash::make($request->password),
                ]);

                return response()->json([
                    'user1' => $user1,
                    'message' => 'password updated'
                ], 200);
            } else {
                return response()->json([
                    'user' => $user,
                    'message' => 'password dont match'
                ], 400);
            }
        } else {
            return response()->json([
                'user' => $user,
                'message' => 'wrong old password'
            ], 400);
        }
    }
}