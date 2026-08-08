<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OperatorService
{
    public function dashboard(): array
    {
        return [

            'operator' => [

                'id' => auth()->id(),

                'nama' => auth()->user()->name,

            ],

            'summary' => [

                'pelanggan' => Pelanggan::count(),

                'pelanggan_aktif' =>
                    Pelanggan::where('status', 'Aktif')->count(),

                'pelanggan_isolir' =>
                    Pelanggan::where('is_isolated', true)->count(),

                'tagihan_belum_lunas' =>
                    Tagihan::where('status', '!=', 'Lunas')->count(),

                'tagihan_jatuh_tempo' =>
                    Tagihan::where('status', 'Jatuh Tempo')->count(),

                'transaksi_hari_ini' =>
                    Pembayaran::whereDate(
                        'tanggal_bayar',
                        today()
                    )->count(),

                'pendapatan_hari_ini' =>
                    Pembayaran::whereDate(
                        'tanggal_bayar',
                        today()
                    )
                    ->where(
                        'status',
                        Pembayaran::STATUS_BERHASIL
                    )
                    ->sum('total_bayar'),

            ],

        ];
    }
    public function profile(): User
    {
        return auth()->user();
    }
    public function updateProfile(array $data): User
    {
        $user = auth()->user();

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        return $user->fresh();
    }
    public function changePassword(array $data): void
    {
        $user = auth()->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('Password lama tidak sesuai.');
        }

        $user->update([
            'password' => bcrypt($data['password']),
        ]);
    }
}