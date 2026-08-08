<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]
            ->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            /*
            | Dashboard
            */

            'dashboard.view',

            /*
            | Pelanggan
            */

            'pelanggan.view',
            'pelanggan.create',
            'pelanggan.edit',
            'pelanggan.delete',

            /*
            | Paket
            */

            'paket.view',
            'paket.create',
            'paket.edit',
            'paket.delete',

            /*
            | Router
            */

            'router.view',
            'router.create',
            'router.edit',
            'router.delete',

            /*
            | Tagihan
            */

            'tagihan.view',
            'tagihan.generate',
            'tagihan.edit',
            'tagihan.delete',

            /*
            | Pembayaran
            */

            'pembayaran.view',
            'pembayaran.create',
            'pembayaran.cancel',

            /*
            | Audit
            */

            'audit.view',

            /*
            | User
            */

            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            /*
            | Role
            */

            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            /*
            | Permission
            */

            'permission.view',
            'permission.create',
            'permission.edit',
            'permission.delete',

            /*
            | Setting
            */

            'setting.manage',

        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([

                'name' => $permission,

                'guard_name' => 'web',

            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'Kasir',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'Teknisi',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'Viewer',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::findByName('Super Admin');

        $superAdmin->syncPermissions(
            Permission::all()
        );
    }
}