<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Router;
use App\Models\Paket;
use App\Models\Tagihan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PelangganService
{
    protected MikroTikService $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        $query = Pelanggan::with([
            'paket',
            'router',
            'saldo',
        ])
        ->withCount([
            'tagihans as tagihan_belum_lunas' => function ($q) {
                $q->where('status', '!=', 'Lunas');
            }
        ]);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_pelanggan', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('nama');

        return $query->paginate(
            $filters['per_page'] ?? 15
        );
    }

    public function getDetail(int $id)
    {
        return Pelanggan::with([
            'paket',
            'router',
            'saldo',
        ])->findOrFail($id);
    }

    public function getTagihan(int $pelangganId)
    {
        return \App\Models\Tagihan::where('pelanggan_id', $pelangganId)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();
    }

    public function getPembayaran(int $pelangganId)
    {
        return \App\Models\Pembayaran::with([
                'tagihan',
                'user',
            ])
            ->whereHas('tagihan', function ($query) use ($pelangganId) {
                $query->where('pelanggan_id', $pelangganId);
            })
            ->latest('tanggal_bayar')
            ->get();
    }

    public function create(array $data): Pelanggan
    {
        // 1. Simpan data pelanggan ke database MySQL
        $pelanggan = Pelanggan::create($data);

        // 2. Otomatis buat secret di MikroTik
        if (!empty($data['username_pppoe']) && !empty($data['password_pppoe']) && !empty($data['router_id'])) {
            $router = Router::find($data['router_id']);
            $paket = Paket::find($data['paket_id']);

            if ($router && $paket) {
                $this->mikrotik->createSecret(
                    $router,
                    $data['username_pppoe'],
                    $data['password_pppoe'],
                    $paket->profile_mikrotik,
                    'pppoe'
                );
            }
        }

        // 3. OTOMATIS BUAT TAGIHAN PERTAMA (LANGSUNG KE DATABASE)
        try {
            $paket = Paket::find($pelanggan->paket_id);
            if ($paket) {
                Tagihan::create([
                    'pelanggan_id' => $pelanggan->id,
                    'paket_id'     => $pelanggan->paket_id,
                    'bulan'        => date('m'),
                    'tahun'        => date('Y'),
                    'jumlah'       => $paket->harga ?? 0,
                    'status'       => 'Belum Bayar',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Gagal membuat tagihan otomatis untuk Pelanggan ID {$pelanggan->id}: " . $e->getMessage());
        }

        return $pelanggan;
    }

    public function update(int $id, array $data): Pelanggan
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $oldUsername = trim((string) $pelanggan->username_pppoe);
        $oldRouterId = $pelanggan->router_id;

        $pelanggan->update($data);

        if (!empty($data['username_pppoe']) && !empty($data['password_pppoe']) && !empty($data['router_id'])) {
            try {
                $router = Router::find($data['router_id']);
                $paket = Paket::find($data['paket_id']);

                if ($router && $paket) {
                    $profile = trim((string) $paket->profile_mikrotik);

                    if ($profile !== '') {
                        if ($oldUsername !== '') {
                            $oldRouter = Router::find($oldRouterId) ?: $router;
                            $secret = $this->mikrotik->getSecretByName($oldRouter, $oldUsername);

                            if ($secret && !empty($secret['.id'])) {
                                $this->mikrotik->updateSecretById(
                                    $router,
                                    $secret['.id'],
                                    $data['username_pppoe'],
                                    $data['password_pppoe'],
                                    $profile,
                                    'pppoe'
                                );
                            } else {
                                $this->mikrotik->createSecret(
                                    $router,
                                    $data['username_pppoe'],
                                    $data['password_pppoe'],
                                    $profile,
                                    'pppoe'
                                );
                            }
                        } else {
                            $this->mikrotik->createSecret(
                                $router,
                                $data['username_pppoe'],
                                $data['password_pppoe'],
                                $profile,
                                'pppoe'
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gagal update PPP Secret di MikroTik untuk Pelanggan ID {$pelanggan->id}: " . $e->getMessage());
            }
        }

        return $pelanggan->fresh();
    }

    public function delete(int $id): bool
    {
        $pelanggan = Pelanggan::findOrFail($id);

        if (!empty($pelanggan->username_pppoe) && !empty($pelanggan->router_id)) {
            try {
                $router = Router::find($pelanggan->router_id);
                if ($router) {
                    $this->mikrotik->deleteSecretById($router, $pelanggan->username_pppoe);
                }
            } catch (\Exception $e) {
                Log::error("Gagal menghapus PPP Secret di MikroTik untuk Pelanggan ID {$id}: " . $e->getMessage());
            }
        }

        return $pelanggan->delete();
    }
}
