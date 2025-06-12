<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function statusBarang(Request $request){
        $userId = $request->header('User-Id');

        $outOfStockCount = Barang::where('jumlah', 0)->where(function ($query) use ($userId) {
                      $query->where('user_id', $userId)
                            ->orWhereNull('user_id');
                  })->count();

        $lowStockCount = Barang::where('jumlah', '<', 10)
                            ->where(function ($query) use ($userId) {
                                $query->where('user_id', $userId)
                                        ->orWhereNull('user_id');
                            })->count();

        $totalItemsCount = Barang::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'out_of_stock' => $outOfStockCount,
                'low_stock' => $lowStockCount,
                'total_items' => $totalItemsCount,
            ]
        ]);
    }

    public function index(Request $request){
        $userId = $request->header('User-Id');

        if ($userId) {
            $data = Barang::where('user_id', $userId)->get();
        } else {
            $data = Barang::whereNull('user_id')->get();
        }

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

    public function show(Request $request, $id){

        $userId = $request->header('User-Id');
        $data = Barang::where('id', $id)
                  ->where(function ($query) use ($userId) {
                      $query->where('user_id', $userId)
                            ->orWhereNull('user_id');
                  })
                  ->first();

        if (!$data) {
        return response()->json([
            'status' => false,
            'message' => "Barang tidak ditemukan atau akses ditolak"
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Barang berhasil ditemukan",
            'data' => $data
        ], 200);
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

        $dataBarang->user_id = $request->header('User-Id');

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

        $userId = $request->header('User-Id');
        $dataBarang = Barang::where('id', $id)->where('user_id', $userId)->first();
        
        if (!$dataBarang) {
            return response()->json([
                'status' => false,
                'message' => "Barang tidak ditemukan atau akses ditolak"
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

    public function destroy(Request $request, $id){

        $userId = $request->header('User-Id');
        $data = Barang::where('id', $id)->where('user_id', $userId)->first();

        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => "Barang tidak ditemukan atau akses ditolak"
            ], 404);
        }

        if ($data->foto_barang && Storage::disk('public')->exists($data->foto_barang)) {
            Storage::disk('public')->delete($data->foto_barang);
        }

        $data->delete();

        return response()->json([
            'status' => true,
            'message' => "Barang berhasil dihapus",
            'data' => $data
        ], 200);
    }
}
