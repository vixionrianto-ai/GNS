<?php

namespace App\Services;

use App\Models\Pelanggan;

class SearchService
{
    public function search(string $keyword)
    {
        return Pelanggan::query()
            ->with([
                'paket',
                'saldo',
            ])
            ->withCount([
                'tagihans as tagihan_belum_lunas' => function ($q) {
                    $q->where('status', '!=', 'Lunas');
                }
            ])
            ->where(function ($q) use ($keyword) {

                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('kode_pelanggan', 'like', "%{$keyword}%")
                  ->orWhere('no_hp', 'like', "%{$keyword}%")
                  ->orWhere('username_pppoe', 'like', "%{$keyword}%");

            })
            ->orderBy('nama')
            ->limit(20)
            ->get();
    }
    public function searchAll(string $keyword): array
    {
        return [

            'pelanggan' => $this->search($keyword),

            'tagihan' => \App\Models\Tagihan::with('pelanggan')
                ->where('invoice_no', 'like', "%{$keyword}%")
                ->limit(10)
                ->get(),

        ];
    }
}