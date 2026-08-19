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
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dimuat.',
            'data' => $query->latest()->paginate($request->integer('per_page', 20)),
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
        $controller = app(WebPelangganController::class);
        $response = $controller->store($request);

        return $this->normalizeResponse($response);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $controller = app(WebPelangganController::class);
        $request->route()->setParameter('pelanggan', $pelanggan);
        $response = $controller->update($request, $pelanggan);

        return $this->normalizeResponse($response);
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $controller = app(WebPelangganController::class);
        $response = $controller->destroy($pelanggan);

        return $this->normalizeResponse($response);
    }

    public function sync()
    {
        $controller = app(WebPelangganController::class);
        return $this->normalizeResponse($controller->sync());
    }

    private function normalizeResponse($response)
    {
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response;
        }

        return response()->json([
            'success' => true,
            'message' => 'Operasi pelanggan berhasil.',
        ]);
    }
}
