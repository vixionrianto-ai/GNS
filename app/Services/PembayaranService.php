<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class PembayaranService
{
    protected MikroTikService $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Proses pembayaran tagihan
     */
    public function bayar(array $data): Pembayaran
    {
        return DB::transaction(function () use ($data) {

            $tagihan = Tagihan::with([
                'pelanggan.router'
            ])->findOrFail($data['tagihan_id']);

            /*
            |--------------------------------------------------------------------------
            | SUDAH LUNAS?
            |--------------------------------------------------------------------------
            */

            if ($tagihan->status === Tagihan::STATUS_LUNAS) {

                throw new Exception(
                    'Tagihan sudah lunas.'
                );

            }

            /*
            |--------------------------------------------------------------------------
            | HITUNG TOTAL
            |--------------------------------------------------------------------------
            */

            $total = $tagihan->nominal + $tagihan->denda;

            if ($data['dibayar'] < $total) {

                throw new Exception(
                    'Nominal pembayaran kurang.'
                );

            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN PEMBAYARAN
            |--------------------------------------------------------------------------
            */

            $pembayaran = Pembayaran::create([

                'tagihan_id'   => $tagihan->id,

                'user_id'      => Auth::id(),

                'tanggal_bayar'=> now(),

                'metode'       => $data['metode'],

                'nominal'      => $tagihan->nominal,

                'biaya_admin'  => $data['biaya_admin'] ?? 0,

                'total_bayar'  => $total,

                'dibayar'      => $data['dibayar'],

                'kembalian'    => $data['dibayar'] - $total,

                'status'       => Pembayaran::STATUS_BERHASIL,

                'keterangan'   => $data['keterangan'] ?? null,

            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE TAGIHAN
            |--------------------------------------------------------------------------
            */

            $tagihan->update([

                'status' => Tagihan::STATUS_LUNAS,

                'tanggal_bayar' => now(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | AKTIFKAN SECRET MIKROTIK
            |--------------------------------------------------------------------------
            */

            $pelanggan = $tagihan->pelanggan;

            if (
                $pelanggan &&
                $pelanggan->mikrotik_secret_id
            ) {

                $this->mikrotik->enableSecretById(

                    $pelanggan->router,

                    $pelanggan->mikrotik_secret_id

                );

                /*
                |--------------------------------------------------------------------------
                | PUTUSKAN SESSION AGAR LOGIN ULANG
                |--------------------------------------------------------------------------
                */

                $this->mikrotik
                    ->disconnectActiveSessionBySecretId(

                        $pelanggan->router,

                        $pelanggan->mikrotik_secret_id

                    );

            }

            return $pembayaran;

        });
    }
}