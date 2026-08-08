<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $user = User::updateOrCreate(

        [
            'email' => 'admin@gns.local',
        ],

        [
            'name' => 'Administrator',

            'password' => Hash::make('admin123'),
        ]

    );

    /*
    |--------------------------------------------------------------------------
    | Assign Super Admin Role
    |--------------------------------------------------------------------------
    */

    if (Role::where('name', 'Super Admin')->exists()) {

        $user->syncRoles(['Super Admin']);

    }
}
}