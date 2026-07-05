<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paket;
use App\Models\Router;

class PaketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $router = Router::first();

        if (!$router) {
            return;
        }

        $pakets = [

            [
                'nama_paket'       => '10 Mbps',
                'kecepatan'        => '10 Mbps',
                'profile_mikrotik' => 'c10',
                'harga'            => 100000,
            ],

            [
                'nama_paket'       => '20 Mbps',
                'kecepatan'        => '20 Mbps',
                'profile_mikrotik' => 'c20',
                'harga'            => 150000,
            ],

            [
                'nama_paket'       => '50 Mbps',
                'kecepatan'        => '50 Mbps',
                'profile_mikrotik' => 'c50',
                'harga'            => 250000,
            ],

        ];

        foreach ($pakets as $paket) {

            Paket::updateOrCreate(

                [
                    'nama_paket' => $paket['nama_paket'],
                ],

                [

                    'router_id' => $router->id,

                    'kecepatan' => $paket['kecepatan'],

                    'profile_mikrotik' => $paket['profile_mikrotik'],

                    'harga' => $paket['harga'],

                    'status' => 'Aktif',

                ]

            );

        }
    }
}