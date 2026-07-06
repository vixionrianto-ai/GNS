<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\Router;
use App\Services\MikroTikService;
use Exception;

class PelangganController extends Controller
{
    protected $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $pelanggans = Pelanggan::with([
            'paket',
            'router'
        ])->get();

        return view(
            'pelanggan.index',
            compact('pelanggans')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $pakets = Paket::where(
            'status',
            'Aktif'
        )->get();

        $routers = Router::where(
            'status',
            'Aktif'
        )->get();

        return view(
            'pelanggan.create',
            compact(
                'pakets',
                'routers'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'nama' => 'required',

            'alamat' => 'required',

            'no_hp' => 'required',

            'router_id' =>
                'required|exists:routers,id',

            'paket_id' =>
                'required|exists:pakets,id',

            'username_pppoe' =>
                'required',

            'password_pppoe' =>
                'required',

            'status' =>
                'required',

        ]);

        /*
        |--------------------------------------------------------------------------
        | GENERATE KODE
        |--------------------------------------------------------------------------
        */

        $last = Pelanggan::orderBy(
            'id',
            'desc'
        )->first();

        if (
            $last &&
            !empty($last->kode_pelanggan)
        ) {

            $nomor =
                (int) substr(
                    $last->kode_pelanggan,
                    3
                ) + 1;

        } else {

            $nomor = 1;

        }

        $kode =
            'GNS' .
            str_pad(
                $nomor,
                5,
                '0',
                STR_PAD_LEFT
            );

        /*
        |--------------------------------------------------------------------------
        | ROUTER & PAKET
        |--------------------------------------------------------------------------
        */

        $router = Router::findOrFail(
            $request->router_id
        );

        $paket = Paket::findOrFail(
            $request->paket_id
        );

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | CREATE PPP SECRET
            |--------------------------------------------------------------------------
            */

            $secretId =
                $this->mikrotik->createSecret(

                    $router,

                    $request->username_pppoe,

                    $request->password_pppoe,

                    $paket->profile_mikrotik

                );

            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATABASE
            |--------------------------------------------------------------------------
            */

            Pelanggan::create([

                'kode_pelanggan' =>
                    $kode,

                'nama' =>
                    $request->nama,

                'alamat' =>
                    $request->alamat,

                'no_hp' =>
                    $request->no_hp,

                'paket_id' =>
                    $request->paket_id,

                'router_id' =>
                    $request->router_id,

                'mikrotik_secret_id' =>
                    $secretId,

                'username_pppoe' =>
                    $request->username_pppoe,

                'password_pppoe' =>
                    $request->password_pppoe,

                'ip_address' =>
                    $request->ip_address,

                'mac_address' =>
                    $request->mac_address,

                'tanggal_pasang' =>
                    $request->tanggal_pasang,

                'tanggal_aktif' =>
                    $request->tanggal_aktif,

                'status' =>
                    $request->status,

            ]);

            DB::commit();

            return redirect()
                ->route('pelanggan.index')
                ->with(
                    'success',
                    'Data pelanggan berhasil ditambahkan.'
                );

        } catch (Exception $e) {

            DB::rollBack();

            \Log::error('Gagal menambah pelanggan', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'mikrotik' => $e->getMessage(),
                ]);

        }

    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(string $id)
    {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(string $id)
    {
        $pelanggan =
            Pelanggan::findOrFail($id);

        $pakets =
            Paket::where(
                'status',
                'Aktif'
            )->get();

        $routers =
            Router::where(
                'status',
                'Aktif'
            )->get();

        return view(
            'pelanggan.edit',
            compact(
                'pelanggan',
                'pakets',
                'routers'
            )
        );
    }

        /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, string $id)
    {
        $request->validate([

            'nama' => 'required',

            'alamat' => 'required',

            'no_hp' => 'required',

            'router_id' =>
                'required|exists:routers,id',

            'paket_id' =>
                'required|exists:pakets,id',

            'username_pppoe' =>
                'required',

            'password_pppoe' =>
                'required',

            'status' =>
                'required',

        ]);

        $pelanggan = Pelanggan::findOrFail($id);

        $router = Router::findOrFail(
            $request->router_id
        );

        $paket = Paket::findOrFail(
            $request->paket_id
        );

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | UPDATE PPP SECRET
            |--------------------------------------------------------------------------
            */

            /*
             | Jika pelanggan lama belum mempunyai
             | mikrotik_secret_id (data lama),
             | ambil dari MikroTik lalu simpan.
             */

            if (empty($pelanggan->mikrotik_secret_id)) {

                $secret =
                    $this->mikrotik->getSecretByName(
                        $router,
                        $pelanggan->username_pppoe
                    );

                if (!$secret) {

                    throw new Exception(
                        'PPP Secret tidak ditemukan di MikroTik.'
                    );

                }

                $pelanggan->mikrotik_secret_id =
                    $secret['.id'];

                $pelanggan->save();

            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE SECRET BERDASARKAN .ID
            |--------------------------------------------------------------------------
            */

            $this->mikrotik->updateSecretById(

                $router,

                $pelanggan->mikrotik_secret_id,

                $request->username_pppoe,

                $request->password_pppoe,

                $paket->profile_mikrotik

            );



            /*
            |--------------------------------------------------------------------------
            | SINKRONISASI STATUS
            |--------------------------------------------------------------------------
            */

            if ($request->status === 'Aktif') {

                $this->mikrotik->enableSecretById(
                    $router,
                    $pelanggan->mikrotik_secret_id
                );

            } else {

                $this->mikrotik->disableSecretById(
                    $router,
                    $pelanggan->mikrotik_secret_id
                );
                $this->mikrotik->disconnectActiveSessionBySecretId(
                    $router,
                    $pelanggan->mikrotik_secret_id
                );

            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
            */

            $pelanggan->update([

                'nama' =>
                    $request->nama,

                'alamat' =>
                    $request->alamat,

                'no_hp' =>
                    $request->no_hp,

                'router_id' =>
                    $request->router_id,

                'paket_id' =>
                    $request->paket_id,

                'username_pppoe' =>
                    $request->username_pppoe,

                'password_pppoe' =>
                    $request->password_pppoe,

                'ip_address' =>
                    $request->ip_address,

                'mac_address' =>
                    $request->mac_address,

                'tanggal_pasang' =>
                    $request->tanggal_pasang,

                'tanggal_aktif' =>
                    $request->tanggal_aktif,

                'status' =>
                    $request->status,

            ]);

            DB::commit();

            return redirect()
                ->route('pelanggan.index')
                ->with(
                    'success',
                    'Data pelanggan berhasil diperbarui.'
                );
        } catch (Exception $e) {
            DB::rollBack();

            \Log::error('Gagal memperbarui pelanggan', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'mikrotik' => $e->getMessage(),
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SINKRONISASI PPP SECRET MIKROTIK
    |--------------------------------------------------------------------------
    */

    public function sync()
    {
        $routers = Router::where(
            'status',
            'Aktif'
        )->get();

        $jumlahImport = 0;
        $jumlahUpdate = 0;

        foreach ($routers as $router) {

            $secrets = $this->mikrotik->getSecrets($router);

            foreach ($secrets as $secret) {

                $username = $secret['name'] ?? '';

                if (empty($username)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CARI PAKET BERDASARKAN PROFILE MIKROTIK
                |--------------------------------------------------------------------------
                */
                
                $paket = Paket::where(
                    'profile_mikrotik',
                    $secret['profile'] ?? ''
                )->first();

                /*
                |--------------------------------------------------------------------------
                | CARI PELANGGAN
                |--------------------------------------------------------------------------
                */

                $pelanggan = Pelanggan::where(
                    'username_pppoe',
                    $username
                )->first();

                /*
                |--------------------------------------------------------------------------
                | IMPORT DATA BARU
                |--------------------------------------------------------------------------
                */

                if (!$pelanggan) {

                    $last = Pelanggan::orderBy(
                        'id',
                        'desc'
                    )->first();

                    if (
                        $last &&
                        !empty($last->kode_pelanggan)
                    ) {

                        $nomor =
                            (int) substr(
                                $last->kode_pelanggan,
                                3
                            ) + 1;

                    } else {

                        $nomor = 1;

                    }

                    $kode =
                        'GNS' .
                        str_pad(
                            $nomor,
                            5,
                            '0',
                            STR_PAD_LEFT
                        );

                    Pelanggan::create([

                        'kode_pelanggan' =>
                            $kode,

                        'nama' =>
                            $username,

                        'alamat' =>
                            '-',

                        'no_hp' =>
                            '-',

                        'router_id' =>
                            $router->id,

                        'paket_id' =>
                            $paket?->id,

                        'mikrotik_secret_id' =>
                            $secret['.id'] ?? null,

                        'username_pppoe' =>
                            $username,

                        'password_pppoe' =>
                            $secret['password'] ?? '',

                        'status' =>
                            ($secret['disabled'] ?? 'false') == 'true'
                                ? 'Nonaktif'
                                : 'Aktif',

                    ]);

                    $jumlahImport++;

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE DATA YANG SUDAH ADA
                    |--------------------------------------------------------------------------
                    */

                    $pelanggan->update([

                        'router_id' =>
                            $router->id,

                        'paket_id' =>
                            $paket?->id,

                        'mikrotik_secret_id' =>
                            $secret['.id'] ?? null,

                        'password_pppoe' =>
                            $secret['password'] ?? '',

                        'status' =>
                            ($secret['disabled'] ?? 'false') == 'true'
                                ? 'Nonaktif'
                                : 'Aktif',

                    ]);

                    $jumlahUpdate++;

                }

            }

        }

        return redirect()
            ->route('pelanggan.index')
            ->with(
                'success',
                "Sinkronisasi selesai.
Import : {$jumlahImport}
Update : {$jumlahUpdate}"
            );
    }

        /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | HAPUS PPP SECRET DI MIKROTIK
            |--------------------------------------------------------------------------
            */

            if (!empty($pelanggan->mikrotik_secret_id)) {

                $router = Router::findOrFail(
                    $pelanggan->router_id
                );

                $this->mikrotik->deleteSecretById(
                    $router,
                    $pelanggan->mikrotik_secret_id
                );

            }

            /*
            |--------------------------------------------------------------------------
            | HAPUS DATABASE
            |--------------------------------------------------------------------------
            */

            $pelanggan->delete();

            DB::commit();

            return redirect()
                ->route('pelanggan.index')
                ->with(
                    'success',
                    'Data pelanggan berhasil dihapus.'
                );

        } catch (Exception $e) {

            DB::rollBack();

            return back()->withErrors(
                $e->getMessage()
            );

        }

    }

}