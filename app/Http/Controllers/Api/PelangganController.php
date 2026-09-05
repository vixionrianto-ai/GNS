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

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $paginator = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dimuat.',
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function show(Pelanggan $pelanggan)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail pelanggan berhasil dimuat.',
            'data' => $pelanggan->load(['paket', 'router']),
        ]);
    }

    public function store(Request $request)
    {
        return app(WebPelangganController::class)->store($request);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        return app(WebPelangganController::class)->update($request, (string) $pelanggan->id);
    }

    public function destroy(Pelanggan $pelanggan)
    {
        return app(WebPelangganController::class)->destroy((string) $pelanggan->id);
    }

    public function sync(Request $request)
    {
        return app(WebPelangganController::class)->sync();
    }
}
