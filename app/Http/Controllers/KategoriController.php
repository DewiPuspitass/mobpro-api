<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class KategoriController extends Controller
{
    public function index(){
        $data = Kategori::all();
        return response()->json([
            'status' => true,
            'message' => "Data berhasil ditemukan",
            'data' => $data
        ], 200);
    }

    public function show($id){
        $data = Kategori::findOrFail($id);
        if($data){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil ditemukan",
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Data berhasil ditemukan"
            ]);
        }
    }

    public function store(Request $request){
        $dataKategori = new Kategori;

        $rules = [
            'nama_kategori' => 'required|string'
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => "Gagal melakukan tambah kategori",
                'data' => $validator->errors()
            ]);
        }

        $dataKategori->nama_kategori = $request->nama_kategori;

        $post = $dataKategori->save();

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil ditambahkan",
                'data' => $post
            ]);
        }
    }

    public function update(Request $request, $id){
        $dataKategori = Kategori::find($id);

        if(empty($dataKategori)){
            return response()->json([
                'status' => false,
                'message' => "Kategori tidak ditemukan",
            ], 404);
        }

        $rules = [
            'nama_kategori' => 'required|string'
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => "Gagal melakukan update kategori",
                'data' => $validator->errors()
            ]);
        }

        $dataKategori->nama_kategori = $request->nama_kategori;

        $post = $dataKategori->save();

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil diperbarui",
                'data' => $post
            ]);
        }
    }

    public function destroy($id)
    {
        $data = Kategori::find($id);
        if (empty($data)) {
            return response()->json([
                'status' => false,
                'message' => "Data tidak ditemukan",
            ], 404);
        }

        try {
            $data->delete();

            return response()->json([
                'status' => true, 
                'message' => 'Data berhasil dihapus'
            ], 200);

        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'status' => false,
                    'message' => 'Maaf, kategori sedang dipakai oleh barang lain.'
                ], 409);
            }

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server database.'
            ], 500);
        }
    }
}
