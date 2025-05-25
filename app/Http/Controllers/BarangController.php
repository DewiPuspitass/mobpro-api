<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $dataBarang = new Barang;

        $rules = [
            'nama_barang' => 'required|string',
            'jumlah' => 'required|numerik',
            'harga' => 'required|numerik',
            'kategori_id' => 'required|numerik',
            'barcode' => 'required|string',
            'deskripsi' => 'required|string',
            'foto_barang' => 'required| string'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => "Gagal melakukan tambah kategori",
                'data' => $validator->errors()
            ]);
        }

        $dataBarang->nama_kategori = $request->nama_kategori;
        $dataBarang->jumlah = $request->jumlah;
        $dataBarang->harga = $request->harga;
        $dataBarang->kategori_id = $request->kategori_id;
        $dataBarang->barcode = $request->barcode;
        $dataBarang->deskripsi = $request->deskripsi;
        $dataBarang->foto_barang = $request->foto_barang;

        $post = $dataBarang->save();

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Data berhasil ditambahkan",
                'data' => $post
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
            $post = $data->delete();

            return response()->json([
                'status' => true,
                'message' => "Barang berhasil dihapus",
                'data' => $data
            ], 200);
        }
    }
}
