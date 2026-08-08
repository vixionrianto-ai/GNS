<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\Router;
use App\Models\Tagihan;
use App\Services\AuditTrailService;
use App\Services\MikroTikService;
use Exception;

class PelangganController extends Controller
{
    protected $mikrotik;
    protected AuditTrailService $auditTrail;

    public function __construct(
        MikroTikService $mikrotik,
        AuditTrailService $auditTrail
    ) {
        $this->mikrotik = $mikrotik;
        $this->auditTrail = $auditTrail;
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $search = $request->input('search');

        $pelanggans = Pelanggan::with([
            'paket',
            'router'
        ])
        ->when($search, function ($query, $search) {
            return $query->where('nama', 'like', "%{$search}%")
                         ->orWhere('kode_pelanggan', 'like', "%{$search}%")
                         ->orWhere('no_hp', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

        // Statistik untuk card dashboard pelanggan
        $totalPelanggan = Pelanggan::count();
        $pelangganAktif = Pelanggan::where('status', Pelanggan::AKTIF)->count();
        $pelangganNonAktif = Pelanggan::where('status', Pelanggan::NONAKTIF)->count();
        $totalPaket = Paket::count();

        return view(
            'pelanggan.index',
            compact(
                'pelanggans',
                'totalPelanggan',
                'pelangganAktif',
                'pelangganNonAktif',
                'totalPaket'
            )
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
    | STORE (Tambah Pelanggan + Auto Generate Tagihan)
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'router_id' => 'required|exists:routers,id',
            'paket_id' => 'required|exists:pakets,id',
            'username_pppoe' => 'required',
            'password_pppoe' => 'required',
            'status' => 'required',
            'isolation_use_default' => 'required|boolean',
            'isolation_period_limit' => 'nullable|integer|min:2',
        ]);

        /*
        |--------------------------------------------------------------------------
        | GENERATE KODE PELANGGAN
        |--------------------------------------------------------------------------
        */

        $last = Pelanggan::orderBy('id', 'desc')->first();

        if ($last && !empty($last->kode_pelanggan)) {
            $nomor = (int) substr($last->kode_pelanggan, 3) + 1;
        } else {
            $nomor = 1;
        }

        $kode = 'GNS' . str_pad($nomor, 5, '0', STR_PAD_LEFT);

        $router = Router::findOrFail($request->router_id);
        $paket = Paket::findOrFail($request->paket_id);

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | CREATE PPP SECRET DI MIKROTIK
            |--------------------------------------------------------------------------
            */

            $secretId = $this->mikrotik->createSecret(
                $router,
                $request->username_pppoe,
                $request->password_pppoe,
                $paket->profile_mikrotik
            );

            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATA PELANGGAN
            |--------------------------------------------------------------------------
            */

            $pelanggan = Pelanggan::create([
                'kode_pelanggan'         => $kode,
                'nama'                   => $request->nama,
                'alamat'                 => $request->alamat,
                'no_hp'                  => $request->no_hp,
                'router_id'              => $request->router_id,
                'paket_id'               => $request->paket_id,
                'mikrotik_secret_id'     => $secretId,
                'username_pppoe'         => $request->username_pppoe,
                'password_pppoe'         => $request->password_pppoe,
                'ip_address'             => $request->ip_address,
                'mac_address'            => $request->mac_address,
                'tanggal_pasang'         => $request->tanggal_pasang,
                'tanggal_aktif'          => $request->tanggal_aktif,
                'status'                 => $request->status,
                'isolation_use_default'  => $request->boolean('isolation_use_default'),
                'isolation_period_limit' => $request->boolean('isolation_use_default') ? null : $request->isolation_period_limit,
                'keterangan'             => $request->keterangan,
            ]);

            /*
            |--------------------------------------------------------------------------
            | OTOMATIS GENERATE TAGIHAN PERTAMA
            |--------------------------------------------------------------------------
            */

            $bulan = date('m');
            $tahun = date('Y');
            $periode = $tahun . '-' . $bulan;
            $invoiceNo = 'INV-' . $tahun . $bulan . '-' . str_pad($pelanggan->id, 5, '0', STR_PAD_LEFT);
            $nominal = $paket->harga ?? $paket->tarif ?? 0;

            Tagihan::create([
                'pelanggan_id'        => $pelanggan->id,
                'invoice_no'          => $invoiceNo,
                'bulan'               => $bulan,
                'tahun'               => $tahun,
                'periode'             => $periode,
                'nominal'             => $nominal,
                'denda'               => 0,
                'total'               => $nominal,
                'tanggal_tagihan'     => now(),
                'tanggal_jatuh_tempo' => now()->addDays(10), // Tenggat waktu 10 hari
                'status'              => 'Belum Bayar',
            ]);

            /*
            |--------------------------------------------------------------------------
            | AUDIT TRAIL
            |--------------------------------------------------------------------------
            */

            $this->auditTrail->pelanggan(
                'create',
                'Menambah pelanggan ' . $pelanggan->nama . ' beserta tagihan otomatis',
                [
                    'pelanggan_id' => $pelanggan->id,
                    'kode'         => $pelanggan->kode_pelanggan,
                    'router_id'    => $pelanggan->router_id,
                    'paket_id'     => $pelanggan->paket_id,
                    'username'     => $pelanggan->username_pppoe,
                ]
            );

            DB::commit();

            return redirect()
                ->route('pelanggan.index')
                ->with('success', 'Data pelanggan berhasil ditambahkan dan tagihan otomatis telah dibuat.');

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
        $pelanggan = Pelanggan::with([
            'router',
            'paket',
            'tagihans.pembayaran',
        ])->findOrFail($id);

        // Ambil tagihan aktif (belum lunas) atau tagihan terakhir untuk tombol pembayaran
        $tagihanAktif = $pelanggan->tagihans->where('status', '!=', 'Lunas')->first() 
                        ?? $pelanggan->tagihans->last();

        return view(
            'pelanggan.show',
            compact('pelanggan', 'tagihanAktif')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $pakets = Paket::where('status', 'Aktif')->get();
        $routers = Router::where('status', 'Aktif')->get();

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
            'router_id' => 'required|exists:routers,id',
            'paket_id' => 'required|exists:pakets,id',
            'username_pppoe' => 'required',
            'password_pppoe' => 'required',
            'status' => 'required',
            'isolation_use_default' => 'required|boolean',
            'isolation_period_limit' => 'nullable|integer|min:2',
        ]);

        $pelanggan = Pelanggan::findOrFail($id);
        $router = Router::findOrFail($request->router_id);
        $paket = Paket::findOrFail($request->paket_id);

        DB::beginTransaction();

        try {
            if (empty($pelanggan->mikrotik_secret_id)) {
                $secret = $this->mikrotik->getSecretByName(
                    $router,
                    $pelanggan->username_pppoe
                );

                if (!$secret) {
                    throw new Exception('PPP Secret tidak ditemukan di MikroTik.');
                }

                $pelanggan->mikrotik_secret_id = $secret['.id'];
                $pelanggan->save();
            }

            $this->mikrotik->updateSecretById(
                $router,
                $pelanggan->mikrotik_secret_id,
                $request->username_pppoe,
                $request->password_pppoe,
                $paket->profile_mikrotik
            );

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

            $pelanggan->update([
                'nama'                   => $request->nama,
                'alamat'                 => $request->alamat,
                'no_hp'                  => $request->no_hp,
                'router_id'              => $request->router_id,
                'paket_id'               => $request->paket_id,
                'username_pppoe'         => $request->username_pppoe,
                'password_pppoe'         => $request->password_pppoe,
                'ip_address'             => $request->ip_address,
                'mac_address'            => $request->mac_address,
                'tanggal_pasang'         => $request->tanggal_pasang,
                'tanggal_aktif'          => $request->tanggal_aktif,
                'status'                 => $request->status,
                'isolation_use_default'  => $request->boolean('isolation_use_default'),
                'isolation_period_limit' => $request->boolean('isolation_use_default') ? null : $request->isolation_period_limit,
            ]);

            $this->auditTrail->pelanggan(
                'update',
                'Mengubah pelanggan ' . $pelanggan->nama,
                [
                    'pelanggan_id' => $pelanggan->id,
                    'router_id'    => $pelanggan->router_id,
                    'paket_id'     => $pelanggan->paket_id,
                    'status'       => $pelanggan->status,
                ]
            );

            DB::commit();

            return redirect()
                ->route('pelanggan.index')
                ->with('success', 'Data pelanggan berhasil diperbarui.');

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
    | SINKRONISASI MIKROTIK
    |--------------------------------------------------------------------------
    */

    public function sync()
    {
        $routers = Router::where('status', 'Aktif')->get();
        $jumlahImport = 0;
        $jumlahUpdate = 0;

        foreach ($routers as $router) {
            $secrets = $this->mikrotik->getSecrets($router);

            foreach ($secrets as $secret) {
                $username = $secret['name'] ?? '';
                if (empty($username)) {
                    continue;
                }

                $paket = Paket::where('profile_mikrotik', $secret['profile'] ?? '')->first();
                $pelanggan = Pelanggan::where('username_pppoe', $username)->first();

                if (!$pelanggan) {
                    $last = Pelanggan::orderBy('id', 'desc')->first();
                    $nomor = ($last && !empty($last->kode_pelanggan)) ? (int) substr($last->kode_pelanggan, 3) + 1 : 1;
                    $kode = 'GNS' . str_pad($nomor, 5, '0', STR_PAD_LEFT);

                    $pelangganBaru = Pelanggan::create([
                        'kode_pelanggan'     => $kode,
                        'nama'               => $username,
                        'alamat'             => '-',
                        'no_hp'              => '-',
                        'router_id'          => $router->id,
                        'paket_id'           => $paket?->id,
                        'mikrotik_secret_id' => $secret['.id'] ?? null,
                        'username_pppoe'     => $username,
                        'password_pppoe'     => $secret['password'] ?? '',
                        'status'             => ($secret['disabled'] ?? 'false') == 'true' ? 'Nonaktif' : 'Aktif',
                    ]);

                    // Opsional: Buat tagihan otomatis juga saat sinkron jika belum ada
                    $bulan = date('m');
                    $tahun = date('Y');
                    Tagihan::firstOrCreate(
                        [
                            'pelanggan_id' => $pelangganBaru->id,
                            'bulan'        => $bulan,
                            'tahun'        => $tahun,
                        ],
                        [
                            'invoice_no'          => 'INV-' . $tahun . $bulan . '-' . str_pad($pelangganBaru->id, 5, '0', STR_PAD_LEFT),
                            'periode'             => $tahun . '-' . $bulan,
                            'nominal'             => $paket?->harga ?? 0,
                            'denda'               => 0,
                            'total'               => $paket?->harga ?? 0,
                            'tanggal_tagihan'     => now(),
                            'tanggal_jatuh_tempo' => now()->addDays(10),
                            'status'              => 'Belum Bayar',
                        ]
                    );

                    $jumlahImport++;
                } else {
                    $pelanggan->update([
                        'router_id'          => $router->id,
                        'paket_id'           => $paket?->id,
                        'mikrotik_secret_id' => $secret['.id'] ?? null,
                        'password_pppoe'     => $secret['password'] ?? '',
                        'status'             => ($secret['disabled'] ?? 'false') == 'true' ? 'Nonaktif' : 'Aktif',
                    ]);

                    $jumlahUpdate++;
                }
            }
        }

        $this->auditTrail->mikrotik(
            'sync',
            'Sinkronisasi pelanggan dengan MikroTik',
            [
                'import' => $jumlahImport,
                'update' => $jumlahUpdate,
            ]
        );

        return redirect()
            ->route('pelanggan.index')
            ->with('success', "Sinkronisasi selesai.\nImport : {$jumlahImport}\nUpdate : {$jumlahUpdate}");
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
            if (!empty($pelanggan->mikrotik_secret_id)) {
                $router = Router::findOrFail($pelanggan->router_id);
                $this->mikrotik->deleteSecretById(
                    $router,
                    $pelanggan->mikrotik_secret_id
                );
            }

            $pelanggan->delete();
            
            $this->auditTrail->pelanggan(
                'delete',
                'Menghapus pelanggan ' . $pelanggan->nama,
                [
                    'pelanggan_id' => $pelanggan->id,
                    'kode'         => $pelanggan->kode_pelanggan,
                ]
            );

            DB::commit();

            return redirect()
                ->route('pelanggan.index')
                ->with('success', 'Data pelanggan berhasil dihapus.');

        } catch (Exception $e) {
            DB::rollBack();

            return back()->withErrors($e->getMessage());
        }
    }
}