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

        return $query->paginate($filters['per_page'] ?? 15);
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
        return Tagihan::where('pelanggan_id', $pelangganId)
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

    private function generateKodePelanggan(): string
    {
        $lastKode = Pelanggan::where('kode_pelanggan', 'like', 'GNS%')
            ->orderByDesc('id')
            ->value('kode_pelanggan');

        $nomor = 1;
        if ($lastKode && preg_match('/^GNS(\d+)$/', (string) $lastKode, $match)) {
            $nomor = ((int) $match[1]) + 1;
        }

        return 'GNS' . str_pad((string) $nomor, 5, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Pelanggan
    {
        // Client tidak boleh menentukan identitas kode pelanggan.
        $data['kode_pelanggan'] = $this->generateKodePelanggan();

        $router = !empty($data['router_id']) ? Router::find($data['router_id']) : null;
        $paket = !empty($data['paket_id']) ? Paket::find($data['paket_id']) : null;

        if (!empty($data['username_pppoe']) && !empty($data['password_pppoe']) && $router && $paket) {
            $this->mikrotik->createSecret(
                $router,
                $data['username_pppoe'],
                $data['password_pppoe'],
                $paket->profile_mikrotik,
                'pppoe'
            );
        }

        $pelanggan = Pelanggan::create($data);

        try {
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

        return $pelanggan->fresh([
            'paket',
            'router',
            'saldo',
        ]);
    }

    public function update(int $id, array $data): Pelanggan
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $oldUsername = trim((string) $pelanggan->username_pppoe);
        $oldRouterId = $pelanggan->router_id;

        // Kode pelanggan adalah identitas server dan tidak boleh diubah oleh client.
        unset($data['kode_pelanggan']);

        $router = !empty($data['router_id']) ? Router::find($data['router_id']) : null;
        $paket = !empty($data['paket_id']) ? Paket::find($data['paket_id']) : null;

        if (!empty($data['username_pppoe']) && !empty($data['password_pppoe']) && $router && $paket) {
            try {
                $profile = trim((string) $paket->profile_mikrotik);

                if ($profile !== '' && $oldUsername !== '') {
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
                }
            } catch (\Exception $e) {
                Log::error("Gagal update PPP Secret di MikroTik untuk Pelanggan ID {$pelanggan->id}: " . $e->getMessage());
                throw $e;
            }
        }

        $pelanggan->update($data);

        return $pelanggan->fresh([
            'paket',
            'router',
            'saldo',
        ]);
    }

    public function delete(int $id): bool
    {
        $pelanggan = Pelanggan::findOrFail($id);

        if (!empty($pelanggan->username_pppoe) && !empty($pelanggan->router_id)) {
            try {
                $router = Router::find($pelanggan->router_id);
                if ($router) {
                    $secret = $this->mikrotik->getSecretByName($router, $pelanggan->username_pppoe);

                    if ($secret && !empty($secret['.id'])) {
                        $this->mikrotik->deleteSecretById($router, $secret['.id']);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gagal menghapus PPP Secret di MikroTik untuk Pelanggan ID {$id}: " . $e->getMessage());
            }
        }

        return $pelanggan->delete();
    }
}
