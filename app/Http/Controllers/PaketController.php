<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Router;
use App\Services\MikroTikService;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function __construct(protected MikroTikService $mikrotik)
    {
    }

    public function index()
    {
        $pakets = Paket::all();

        return view('paket.index', compact('pakets'));
    }

    public function create()
    {
        $routers = Router::orderBy('nama_router')->get();

        return view('paket.create', compact('routers'));
    }

    public function getProfiles(Router $router)
    {
        try {
            $profiles = $this->mikrotik->getProfileNames($router);

            return response()->json($profiles);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'router_id' => ['required', 'integer', 'exists:routers,id'],
            'nama_paket' => ['required', 'string', 'max:100'],
            'kecepatan' => ['required', 'string', 'max:100'],
            'profile_mikrotik' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        Paket::create($data);

        return redirect()
            ->route('paket.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    public function show(Paket $paket)
    {
        return redirect()->route('paket.edit', $paket);
    }

    public function edit(Paket $paket)
    {
        $routers = Router::orderBy('nama_router')->get();

        return view('paket.edit', compact('paket', 'routers'));
    }

    public function update(Request $request, Paket $paket)
    {
        $data = $request->validate([
            'router_id' => ['sometimes', 'required', 'integer', 'exists:routers,id'],
            'nama_paket' => ['required', 'string', 'max:100'],
            'kecepatan' => ['required', 'string', 'max:100'],
            'profile_mikrotik' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $paket->update($data);

        return redirect()
            ->route('paket.index')
            ->with('success', 'Paket berhasil diupdate.');
    }

    public function destroy(Paket $paket)
    {
        if ($paket->pelanggans()->exists()) {
            return redirect()
                ->route('paket.index')
                ->with('error', 'Paket tidak dapat dihapus karena masih digunakan pelanggan.');
        }

        $paket->delete();

        return redirect()
            ->route('paket.index')
            ->with('success', 'Paket berhasil dihapus.');
    }
}
