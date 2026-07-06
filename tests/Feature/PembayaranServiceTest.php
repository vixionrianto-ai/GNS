<?php

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Router;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\MikroTikService;
use App\Services\PembayaranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Exception;

uses(RefreshDatabase::class);

it('still saves payment when MikroTik update fails', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $router = Router::create([
        'nama_router' => 'Router Test',
        'ip_router' => '192.168.88.1',
        'api_port' => 8728,
        'username' => 'admin',
        'password' => 'password',
        'lokasi' => 'Test',
        'versi_routeros' => '7.0',
        'identity' => 'Test',
        'ssl' => false,
        'status' => 'Aktif',
    ]);

    $paket = Paket::create([
        'router_id' => $router->id,
        'nama_paket' => 'Paket Test',
        'kecepatan' => '100Mbps',
        'profile_mikrotik' => 'test-profile',
        'harga' => 100000,
        'status' => 'Aktif',
        'keterangan' => null,
    ]);

    $pelanggan = Pelanggan::create([
        'kode_pelanggan' => 'GNS00001',
        'nama' => 'Test Customer',
        'alamat' => 'Test Address',
        'no_hp' => '08123456789',
        'paket_id' => $paket->id,
        'router_id' => $router->id,
        'mikrotik_secret_id' => 'abc123',
        'username_pppoe' => 'test-user',
        'password_pppoe' => 'secret',
        'status' => 'Aktif',
    ]);

    $tagihan = Tagihan::create([
        'pelanggan_id' => $pelanggan->id,
        'invoice_no' => 'INV-001',
        'periode' => '2026-07',
        'bulan' => 7,
        'tahun' => 2026,
        'tanggal_tagihan' => '2026-07-01',
        'tanggal_jatuh_tempo' => '2026-07-10',
        'nominal' => 10000,
        'denda' => 1000,
        'total' => 11000,
        'status' => Tagihan::STATUS_BELUM_BAYAR,
    ]);

    $mikrotik = new class($router) extends MikroTikService {
        public function __construct(private Router $router) {}

        public function enableSecretById(Router $router, string $id): bool
        {
            throw new Exception('router down');
        }

        public function disconnectActiveSessionBySecretId(Router $router, string $secretId): bool
        {
            throw new Exception('router down');
        }
    };

    $service = new PembayaranService($mikrotik);

    $pembayaran = $service->bayar([
        'tagihan_id' => $tagihan->id,
        'metode' => 'Cash',
        'biaya_admin' => 500,
        'dibayar' => 12000,
        'keterangan' => 'Test payment',
    ]);

    expect($pembayaran->exists)->toBeTrue();
    $tagihan->refresh();
    expect($tagihan->status)->toBe(Tagihan::STATUS_LUNAS);
    expect((float) $pembayaran->total_bayar)->toBe(11500.0);
});
