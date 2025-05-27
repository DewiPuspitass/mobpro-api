<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function index(){
        $data = Barang::all();

        if(empty($data)){
            return response()->json([
                'status' => false,
                'message' => "Tidak ada barang"
            ], 404);
        }else{
            return response()->json([
                'status' => true,
                'message' => "Barang berhasil ditemukan",
                'data' => $data
            ], 200);
        }
    }

    public function show($id){
        $data = Barang::find($id);

        if(empty($data)){
            return response()->json([
                'status' => false,
                'message' => "Tidak ada barang"
            ], 404);
        }else{
            return response()->json([
                'status' => true,
                'message' => "Barang berhasil ditemukan",
                'data' => $data
            ], 200);
        }
    }

    public function store(Request $request){
        $rules = [
            'nama_barang' => 'required|string',
            'jumlah' => 'required|numeric',
            'harga' => 'required|numeric',
            'kategori_id' => 'required|numeric',
            'barcode' => 'required|string',
            'deskripsi' => 'required|string',
            'foto_barang' => 'required|file|image|mimes:jpeg,png,jpg|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => "Gagal melakukan tambah barang",
                'data' => $validator->errors()
            ]);
        }

        $imagePath = $request->file('foto_barang')->store('foto_barang', 'public');

        $dataBarang = new Barang;
        $dataBarang->nama_barang = $request->nama_barang;
        $dataBarang->jumlah = $request->jumlah;
        $dataBarang->harga = $request->harga;
        $dataBarang->kategori_id = $request->kategori_id;
        $dataBarang->barcode = $request->barcode;
        $dataBarang->deskripsi = $request->deskripsi;
        $dataBarang->foto_barang = $imagePath;

        $post = $dataBarang->save();

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil ditambahkan",
                'data' => $dataBarang
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Gagal menyimpan data"
            ]);
        }
    }

    public function update(Request $request, $id){
        $dataBarang = Barang::find($id);
        
        if(empty($dataBarang)){
            return response()->json([
                'status' => false,
                'message' => "Barang tidak ditemukan",
            ], 404);
        }

        $rules = [
            'nama_barang' => 'required|string',
            'jumlah' => 'required|numeric',
            'harga' => 'required|numeric',
            'kategori_id' => 'required|numeric',
            'barcode' => 'required|string',
            'deskripsi' => 'required|string',
            'foto_barang' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => "Gagal melakukan update barang",
                'data' => $validator->errors()
            ], 400);
        }

        
        if ($request->hasFile('foto_barang')) {
            if ($dataBarang->foto_barang && Storage::disk('public')->exists($dataBarang->foto_barang)) {
                Storage::disk('public')->delete($dataBarang->foto_barang);
            }
            
            $imagePath = $request->file('foto_barang')->store('foto_barang', 'public');
            $dataBarang->foto_barang = $imagePath;
        }

        $dataBarang->nama_barang = $request->nama_barang;
        $dataBarang->jumlah = $request->jumlah;
        $dataBarang->harga = $request->harga;
        $dataBarang->kategori_id = $request->kategori_id;
        $dataBarang->barcode = $request->barcode;
        $dataBarang->deskripsi = $request->deskripsi;

        $post = $dataBarang->save();

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil diupdate",
                'data' => $dataBarang
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Gagal update data"
            ]);
        }
    }

    public function destroy($id){
        $data = Barang::find($id);

        if(empty($data)){
            return response()->json([
                'status' => false,
                'message' => "Barang tidak ditemukan"
            ], 404);
        }else{
            if ($data->foto_barang && Storage::disk('public')->exists($data->foto_barang)) {
                Storage::disk('public')->delete($data->foto_barang);
            }

            $post = $data->delete();

            return response()->json([
                'status' => true,
                'message' => "Barang berhasil dihapus",
                'data' => $data
            ], 200);
        }
    }
}
