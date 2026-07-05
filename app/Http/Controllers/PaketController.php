<?php

namespace App\Http\Controllers;
use App\Models\Router;
use App\Services\MikroTikService;
use App\Models\Paket;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    protected $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pakets = Paket::all();

        return view('paket.index', compact('pakets'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $routers = Router::orderBy('nama_router')->get();
        return view('paket.create', compact('routers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    
    /*
    |--------------------------------------------------------------------------
    | AJAX : AMBIL PPP PROFILE DARI MIKROTIK
    |--------------------------------------------------------------------------
    */

    public function getProfiles(Router $router)
    {
        try {

            $profiles = $this->mikrotik->getProfileNames($router);

            return response()->json($profiles);

        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);

        }
    }
    public function store(Request $request)
    {
        $request->validate([
        'router_id'         => 'required',
        'nama_paket'        => 'required',
        'kecepatan'         => 'required',
        'profile_mikrotik'  => 'required',
        'harga'             => 'required|numeric',
        'status'            => 'required',
    ]);

        Paket::create($request->all());

        return redirect()->route('paket.index')
                        ->with('success', 'Paket berhasil ditambahkan.');
    }
    /**
     * Display the specified resource.
     */
    public function show(Paket $paket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paket $paket)
    {
        $routers = \App\Models\Router::orderBy('nama_router')->get();

        return view('paket.edit', compact('paket', 'routers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paket $paket)
    {
        $request->validate([
            'nama_paket'       => 'required',
            'kecepatan'        => 'required',
            'profile_mikrotik' => 'required',
            'harga'            => 'required|numeric',
            'status'           => 'required',
        ]);

        $paket->update($request->all());

        return redirect()->route('paket.index')
                        ->with('success', 'Paket berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paket $paket)
    {
        $paket->delete();

        return redirect()->route('paket.index')
                         ->with('success', 'Paket berhasil dihapus.');
    }
}