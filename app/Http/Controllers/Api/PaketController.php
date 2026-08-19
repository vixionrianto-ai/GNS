<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Models\Router;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil dimuat.',
            'data' => Paket::with('router')->orderBy('nama_paket')->get(),
        ]);
    }

    public function show(Paket $paket)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail paket berhasil dimuat.',
            'data' => $paket->load('router'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'router_id' => ['required', 'exists:routers,id'],
            'nama_paket' => ['required', 'string', 'max:255'],
            'kecepatan' => ['required', 'string', 'max:255'],
            'profile_mikrotik' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
        ]);

        $paket = Paket::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil ditambahkan.',
            'data' => $paket->load('router'),
        ], 201);
    }

    public function update(Request $request, Paket $paket)
    {
        $data = $request->validate([
            'router_id' => ['sometimes', 'exists:routers,id'],
            'nama_paket' => ['sometimes', 'required', 'string', 'max:255'],
            'kecepatan' => ['sometimes', 'required', 'string', 'max:255'],
            'profile_mikrotik' => ['sometimes', 'required', 'string', 'max:255'],
            'harga' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'string'],
        ]);

        $paket->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil diperbarui.',
            'data' => $paket->fresh()->load('router'),
        ]);
    }

    public function destroy(Paket $paket)
    {
        $paket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil dihapus.',
        ]);
    }

    public function profiles(Router $router)
    {
        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil dimuat.',
            'data' => app(\App\Services\MikroTikService::class)->getProfileNames($router),
        ]);
    }
}
