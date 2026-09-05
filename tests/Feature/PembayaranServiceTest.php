<?php

use App\Models\AlokasiPembayaran;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Router;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\MikroTikService;
use App\Services\PembayaranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        'dibayar' => 0,
        'sisa' => 11000,
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

    $service = new PembayaranService($mikrotik, new InvoiceService());

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
    expect((float) $tagihan->dibayar)->toBe(11500.0);
    expect((float) $tagihan->sisa)->toBe(0.0);
    expect((float) $pembayaran->total_bayar)->toBe(11500.0);
});

it('combines legacy direct payments with allocated payments without double counting', function () {
    $tagihan = Tagihan::create([
        'pelanggan_id' => Pelanggan::factory()->create()->id,
        'invoice_no' => 'INV-MIXED-001',
        'periode' => '2026-09',
        'bulan' => 9,
        'tahun' => 2026,
        'tanggal_tagihan' => '2026-09-05',
        'tanggal_jatuh_tempo' => '2026-09-15',
        'nominal' => 100000,
        'denda' => 0,
        'total' => 100000,
        'dibayar' => 0,
        'sisa' => 100000,
        'status' => Tagihan::STATUS_BELUM_BAYAR,
    ]);

    $legacy = Pembayaran::create([
        'invoice_no' => 'PAY-LEGACY-001',
        'invoice_date' => now(),
        'tagihan_id' => $tagihan->id,
        'user_id' => User::factory()->create()->id,
        'tanggal_bayar' => now(),
        'metode' => 'Cash',
        'nominal' => 40000,
        'biaya_admin' => 0,
        'total_bayar' => 40000,
        'dibayar' => 40000,
        'kembalian' => 0,
        'status' => Pembayaran::STATUS_BERHASIL,
    ]);

    $allocatedPayment = Pembayaran::create([
        'invoice_no' => 'PAY-ALLOC-001',
        'invoice_date' => now(),
        'tagihan_id' => $tagihan->id,
        'user_id' => User::factory()->create()->id,
        'tanggal_bayar' => now(),
        'metode' => 'Transfer',
        'nominal' => 60000,
        'biaya_admin' => 0,
        'total_bayar' => 60000,
        'dibayar' => 60000,
        'kembalian' => 0,
        'status' => Pembayaran::STATUS_BERHASIL,
    ]);

    AlokasiPembayaran::create([
        'pembayaran_id' => $allocatedPayment->id,
        'tagihan_id' => $tagihan->id,
        'nominal' => 60000,
    ]);

    expect($tagihan->getTotalDibayar())->toBe(100000.0);
    $tagihan->refreshStatus();
    expect($tagihan->fresh()->status)->toBe(Tagihan::STATUS_LUNAS);
});
