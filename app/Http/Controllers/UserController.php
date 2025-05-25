<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(){
        $dataUser = User::all();

        if(empty($dataUser)){
            return response()->json([
                'status' => true,
                'message' => "Tidak ada data"
            ], 200);
        }else{
            return response()->json([
                'status' => true,
                'message' => "Data berhasil ditampilkan",
                'data' => $dataUser
            ], 200);
        }
    }

    public function show($id){
        $dataUser = User::find($id);

        if($dataUser){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil ditampilkan",
                'data' => $dataUser
            ], 200);
        } else { 
            return response()->json([
                'status' => false,
                'message' => "Tidak ada data"
            ], 404);
        }
    }

    public function store(Request $request){
        $rules = [
            'nama' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'foto_profil' => 'required|file|image|mimes:jpeg,png,jpg|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => "Gagal melakukan tambah barang",
                'data' => $validator->errors()
            ]);
        }

        $imagePath = $request->file('foto_profil')->store('foto_profil', 'public');

        $dataUser = new User;
        $dataUser->nama = $request->nama;
        $dataUser->email = $request->email;
        $dataUser->password = Hash::make($request->password);
        $dataUser->foto_profil = $imagePath;

        $post = $dataUser->save();

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil ditambahkan",
                'data' => $dataUser
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Gagal menyimpan data"
            ]);
        }
    }

    public function update(Request $request, $id){
        $dataUser = User::find($id);
        
        if(empty($dataUser)){
            return response()->json([
                'status' => false,
                'message' => "User tidak ditemukan",
            ], 404);
        }

        $rules = [
            'nama' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'foto_profil' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => "Gagal melakukan update user",
                'data' => $validator->errors()
            ], 400);
        }

        
        if ($request->hasFile('foto_profil')) {
            if ($dataUser->foto_profil && Storage::disk('public')->exists($dataUser->foto_profil)) {
                Storage::disk('public')->delete($dataUser->foto_profil);
            }
            
            $imagePath = $request->file('foto_profil')->store('foto_profil', 'public');
            $dataUser->foto_profil = $imagePath;
        }

        $dataUser->nama = $request->nama;
        $dataUser->email = $request->email;
        $dataUser->password = $request->password;

        $post = $dataUser->save();

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil diupdate",
                'data' => $dataUser
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Gagal update data"
            ]);
        }
    }

    public function destroy($id){
        $data = User::find($id);

        if(empty($data)){
            return response()->json([
                'status' => false,
                'message' => "User tidak ditemukan"
            ], 404);
        }else{
            if ($data->foto_profil && Storage::disk('public')->exists($data->foto_profil)) {
                Storage::disk('public')->delete($data->foto_profil);
            }

            $post = $data->delete();

            return response()->json([
                'status' => true,
                'message' => "User berhasil dihapus",
                'data' => $data
            ], 200);
        }
    }

    public function login(Request $request){
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = Str::random(60);
        $user->remember_token = $token;
        $user->save();

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->header('Authorization');

        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 401);
        }

        $user->remember_token = null;
        $user->save();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
