<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PelangganController as WebPelangganController;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::query()->with(['paket', 'router']);
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('nama', 'like', "%{$search}%")
                ->orWhere('username_pppoe', 'like', "%{$search}%")
                ->orWhere('no_hp', 'like', "%{$search}%"));
        }
        if ($request->filled('status')) $query->where('status', $request->string('status')->toString());
        return response()->json(['success'=>true,'message'=>'Pelanggan berhasil dimuat.','data'=>$query->latest()->paginate($request->integer('per_page',20))]);
    }

    public function show(Pelanggan $pelanggan)
    {
        return response()->json(['success'=>true,'message'=>'Detail pelanggan berhasil dimuat.','data'=>$pelanggan->load(['paket','router'])]);
    }

    public function store(Request $request)
    {
        app(WebPelangganController::class)->store($request);
        $pelanggan = Pelanggan::with(['paket','router'])->where('username_pppoe',$request->username_pppoe)->latest('id')->firstOrFail();
        return response()->json(['success'=>true,'message'=>'Data pelanggan berhasil ditambahkan.','data'=>$pelanggan],201);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        app(WebPelangganController::class)->update($request,$pelanggan);
        return response()->json(['success'=>true,'message'=>'Data pelanggan berhasil diperbarui.','data'=>$pelanggan->refresh()->load(['paket','router'])]);
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $id=$pelanggan->id;
        app(WebPelangganController::class)->destroy($pelanggan);
        return response()->json(['success'=>true,'message'=>"Pelanggan #{$id} berhasil dihapus."]);
    }

    public function sync()
    {
        app(WebPelangganController::class)->sync();
        return response()->json(['success'=>true,'message'=>'Sinkronisasi pelanggan berhasil.']);
    }
}
