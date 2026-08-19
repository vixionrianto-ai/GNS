<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\MikroTikService;
use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Router berhasil dimuat.',
            'data' => Router::orderBy('nama_router')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_router' => ['required', 'string', 'max:255'],
            'ip_router' => ['required', 'string', 'max:255'],
            'api_port' => ['required', 'integer'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'lokasi' => ['nullable', 'string'],
            'versi_routeros' => ['nullable', 'string'],
            'ssl' => ['nullable', 'boolean'],
            'status' => ['required', 'string'],
        ]);

        $router = Router::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Router berhasil ditambahkan.',
            'data' => $router,
        ], 201);
    }

    public function show(Router $router)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail router berhasil dimuat.',
            'data' => $router,
        ]);
    }

    public function update(Request $request, Router $router)
    {
        $data = $request->validate([
            'nama_router' => ['sometimes', 'required', 'string', 'max:255'],
            'ip_router' => ['sometimes', 'required', 'string', 'max:255'],
            'api_port' => ['sometimes', 'required', 'integer'],
            'username' => ['sometimes', 'required', 'string', 'max:255'],
            'password' => ['sometimes', 'required', 'string'],
            'lokasi' => ['nullable', 'string'],
            'versi_routeros' => ['nullable', 'string'],
            'ssl' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'required', 'string'],
        ]);

        $router->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Router berhasil diperbarui.',
            'data' => $router->fresh(),
        ]);
    }

    public function destroy(Router $router)
    {
        $router->delete();

        return response()->json([
            'success' => true,
            'message' => 'Router berhasil dihapus.',
        ]);
    }

    public function test(Router $router, MikroTikService $mikrotik)
    {
        $identity = $mikrotik->getIdentity($router);
        $version = $mikrotik->getRouterVersion($router);

        return response()->json([
            'success' => true,
            'message' => 'Koneksi router berhasil.',
            'data' => [
                'identity' => $identity,
                'version' => $version,
            ],
        ]);
    }
}
