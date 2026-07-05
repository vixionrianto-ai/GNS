<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Router;

class RouterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Router::updateOrCreate(

            [
                'nama_router' => 'Router Utama',
            ],

            [

                'lokasi' => 'Kantor',

                'ip_router' => '192.168.88.1',

                'api_port' => 8728,

                'username' => 'admin',

                'password' => 'admin',

                'ssl' => false,

                'identity' => 'MikroTik',

                'versi_routeros' => '7.x',

                'status' => 'Aktif',

            ]

        );
    }
}