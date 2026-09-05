<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function index()
    {
        $pelanggans = Pelanggan::with(['paket', 'router'])->get();
        return view('pelanggan.index', compact('pelanggans'));
    }

    public function create()
    {
        $pakets = Paket::where('status', 'Aktif')->get();
        $routers = Router::where('status', 'Aktif')->get();
        return view('pelanggan.create', compact('pakets', 'routers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required', 'alamat' => 'required', 'no_hp' => 'required',
            'router_id' => 'required|exists:routers,id',
            'paket_id' => 'required|exists:pakets,id',
            'username_pppoe' => 'required', 'password_pppoe' => 'required', 'status' => 'required',
        ]);

        $last = Pelanggan::orderByDesc('id')->first();
        $nomor = $last && !empty($last->kode_pelanggan) ? (int) substr($last->kode_pelanggan, 3) + 1 : 1;
        $kode = 'GNS' . str_pad($nomor, 5, '0', STR_PAD_LEFT);
        $router = Router::findOrFail($request->router_id);
        $paket = Paket::findOrFail($request->paket_id);

        DB::beginTransaction();
        try {
            $secretId = $this->mikrotik->createSecret($router, $request->username_pppoe, $request->password_pppoe, $paket->profile_mikrotik);
            if ($request->status !== 'Aktif') $this->mikrotik->disableSecretById($router, $secretId);

            Pelanggan::create([
                'kode_pelanggan' => $kode, 'nama' => $request->nama, 'alamat' => $request->alamat, 'no_hp' => $request->no_hp,
                'paket_id' => $request->paket_id, 'router_id' => $request->router_id, 'mikrotik_secret_id' => $secretId,
                'username_pppoe' => $request->username_pppoe, 'password_pppoe' => $request->password_pppoe,
                'ip_address' => $request->ip_address, 'mac_address' => $request->mac_address,
                'tanggal_pasang' => $request->tanggal_pasang, 'tanggal_aktif' => $request->tanggal_aktif, 'status' => $request->status,
            ]);

            DB::commit();
            return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil ditambahkan.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambah pelanggan', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['mikrotik' => $e->getMessage()]);
        }
    }

    public function show(string $id) { /* intentionally unused */ }

    public function edit(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pakets = Paket::where('status', 'Aktif')->get();
        $routers = Router::where('status', 'Aktif')->get();
        return view('pelanggan.edit', compact('pelanggan', 'pakets', 'routers'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama' => 'required', 'alamat' => 'required', 'no_hp' => 'required',
            'router_id' => 'required|exists:routers,id', 'paket_id' => 'required|exists:pakets,id',
            'username_pppoe' => 'required', 'password_pppoe' => 'required', 'status' => 'required',
        ]);

        $pelanggan = Pelanggan::findOrFail($id);
        $oldRouter = $pelanggan->router_id ? Router::findOrFail($pelanggan->router_id) : null;
        $newRouter = Router::findOrFail($request->router_id);
        $paket = Paket::findOrFail($request->paket_id);
        $routerChanged = !$oldRouter || (int) $oldRouter->id !== (int) $newRouter->id;
        $oldSecretId = $pelanggan->mikrotik_secret_id;
        $newSecretId = $oldSecretId;
        $createdNewSecret = false;

        DB::beginTransaction();
        try {
            if ($routerChanged) {
                if ($this->mikrotik->getSecretByName($newRouter, $request->username_pppoe)) {
                    throw new Exception('PPP Secret dengan username tersebut sudah ada di router tujuan.');
                }
                $newSecretId = $this->mikrotik->createSecret($newRouter, $request->username_pppoe, $request->password_pppoe, $paket->profile_mikrotik);
                $createdNewSecret = true;
                if ($request->status === 'Aktif') $this->mikrotik->enableSecretById($newRouter, $newSecretId);
                else $this->mikrotik->disableSecretById($newRouter, $newSecretId);
            } else {
                if (empty($oldSecretId)) {
                    $secret = $this->mikrotik->getSecretByName($newRouter, $pelanggan->username_pppoe);
                    if (!$secret) throw new Exception('PPP Secret tidak ditemukan di MikroTik.');
                    $oldSecretId = $secret['.id'];
                    $newSecretId = $oldSecretId;
                }
                $this->mikrotik->updateSecretById($newRouter, $oldSecretId, $request->username_pppoe, $request->password_pppoe, $paket->profile_mikrotik);
                if ($request->status === 'Aktif') $this->mikrotik->enableSecretById($newRouter, $oldSecretId);
                else {
                    $this->mikrotik->disableSecretById($newRouter, $oldSecretId);
                    $this->mikrotik->disconnectActiveSessionBySecretId($newRouter, $oldSecretId);
                }
            }

            $pelanggan->update([
                'nama' => $request->nama, 'alamat' => $request->alamat, 'no_hp' => $request->no_hp,
                'router_id' => $newRouter->id, 'paket_id' => $request->paket_id, 'mikrotik_secret_id' => $newSecretId,
                'username_pppoe' => $request->username_pppoe, 'password_pppoe' => $request->password_pppoe,
                'ip_address' => $request->ip_address, 'mac_address' => $request->mac_address,
                'tanggal_pasang' => $request->tanggal_pasang, 'tanggal_aktif' => $request->tanggal_aktif, 'status' => $request->status,
            ]);
            DB::commit();

            if ($routerChanged && $oldRouter && !empty($oldSecretId)) {
                try {
                    $this->mikrotik->disconnectActiveSessionBySecretId($oldRouter, $oldSecretId);
                    $this->mikrotik->deleteSecretById($oldRouter, $oldSecretId);
                } catch (\Throwable $cleanupError) {
                    Log::warning('PPP Secret lama gagal dibersihkan setelah perpindahan router', ['pelanggan_id' => $pelanggan->id, 'old_router_id' => $oldRouter->id, 'old_secret_id' => $oldSecretId, 'message' => $cleanupError->getMessage()]);
                }
            }
            return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
        } catch (Exception $e) {
            DB::rollBack();
            if ($routerChanged && $createdNewSecret && !empty($newSecretId)) {
                try {
                    $this->mikrotik->disconnectActiveSessionBySecretId($newRouter, $newSecretId);
                    $this->mikrotik->deleteSecretById($newRouter, $newSecretId);
                } catch (\Throwable $cleanupError) {
                    Log::warning('PPP Secret baru gagal dibersihkan setelah update pelanggan gagal', ['pelanggan_id' => $pelanggan->id, 'new_router_id' => $newRouter->id, 'new_secret_id' => $newSecretId, 'message' => $cleanupError->getMessage()]);
                }
            }
            Log::error('Gagal memperbarui pelanggan', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['mikrotik' => $e->getMessage()]);
        }
    }

    public function sync()
    {
        $routers = Router::where('status', 'Aktif')->get();
        $jumlahImport = $jumlahUpdate = $jumlahKonflik = 0;

        foreach ($routers as $router) {
            $secrets = $this->mikrotik->getSecrets($router);
            foreach ($secrets as $secret) {
                $username = trim((string) ($secret['name'] ?? ''));
                if ($username === '') continue;

                $paket = Paket::where('profile_mikrotik', $secret['profile'] ?? '')->first();
                $pelanggan = Pelanggan::where('username_pppoe', $username)->first();

                if (!$pelanggan) {
                    $last = Pelanggan::orderByDesc('id')->first();
                    $nomor = $last && !empty($last->kode_pelanggan) ? (int) substr($last->kode_pelanggan, 3) + 1 : 1;
                    Pelanggan::create([
                        'kode_pelanggan' => 'GNS' . str_pad($nomor, 5, '0', STR_PAD_LEFT),
                        'nama' => $username, 'alamat' => '-', 'no_hp' => '-', 'router_id' => $router->id,
                        'paket_id' => $paket?->id, 'mikrotik_secret_id' => $secret['.id'] ?? null,
                        'username_pppoe' => $username, 'password_pppoe' => $secret['password'] ?? '',
                        'status' => ($secret['disabled'] ?? 'false') == 'true' ? 'Nonaktif' : 'Aktif',
                    ]);
                    $jumlahImport++;
                    continue;
                }

                // Sync never moves an existing customer between routers.
                if ((int) $pelanggan->router_id !== (int) $router->id) {
                    $jumlahKonflik++;
                    Log::warning('Sync PPP dilewati karena username ditemukan di router berbeda', [
                        'pelanggan_id' => $pelanggan->id, 'username_pppoe' => $username,
                        'database_router_id' => $pelanggan->router_id, 'detected_router_id' => $router->id,
                        'detected_secret_id' => $secret['.id'] ?? null,
                    ]);
                    continue;
                }

                $pelanggan->update([
                    'paket_id' => $paket?->id,
                    'mikrotik_secret_id' => $secret['.id'] ?? $pelanggan->mikrotik_secret_id,
                    'password_pppoe' => $secret['password'] ?? $pelanggan->password_pppoe,
                    'status' => ($secret['disabled'] ?? 'false') == 'true' ? 'Nonaktif' : 'Aktif',
                ]);
                $jumlahUpdate++;
            }
        }

        return redirect()->route('pelanggan.index')->with('success', "Sinkronisasi selesai.\nImport : {$jumlahImport}\nUpdate : {$jumlahUpdate}\nKonflik dilewati : {$jumlahKonflik}");
    }

    public function destroy(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        DB::beginTransaction();
        try {
            if (!empty($pelanggan->mikrotik_secret_id)) {
                $router = Router::findOrFail($pelanggan->router_id);
                $this->mikrotik->disconnectActiveSessionBySecretId($router, $pelanggan->mikrotik_secret_id);
                $this->mikrotik->deleteSecretById($router, $pelanggan->mikrotik_secret_id);
            }
            $pelanggan->delete();
            DB::commit();
            return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil dihapus.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus pelanggan', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors($e->getMessage());
        }
    }
}
