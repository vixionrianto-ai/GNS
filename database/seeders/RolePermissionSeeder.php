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

        $permissions = [
            'dashboard.view',

            'pelanggan.view',
            'pelanggan.create',
            'pelanggan.edit',
            'pelanggan.delete',

            'paket.view',
            'paket.create',
            'paket.edit',
            'paket.delete',

            'router.view',
            'router.create',
            'router.edit',
            'router.delete',

            'tagihan.view',
            'tagihan.generate',
            'tagihan.edit',
            'tagihan.delete',

            'pembayaran.view',
            'pembayaran.create',
            'pembayaran.cancel',

            'laporan.view',
            'whatsapp.view',
            'mikrotik.view',

            'audit.view',

            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            'permission.view',
            'permission.create',
            'permission.edit',
            'permission.delete',

            'setting.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach (['Super Admin', 'Admin', 'Kasir', 'Teknisi', 'Viewer'] as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::findByName('Super Admin');
        $admin = Role::findByName('Admin');
        $kasir = Role::findByName('Kasir');
        $teknisi = Role::findByName('Teknisi');
        $viewer = Role::findByName('Viewer');

        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'dashboard.view',
            'pelanggan.view', 'pelanggan.create', 'pelanggan.edit', 'pelanggan.delete',
            'paket.view', 'paket.create', 'paket.edit', 'paket.delete',
            'router.view', 'router.create', 'router.edit', 'router.delete',
            'tagihan.view', 'tagihan.generate', 'tagihan.edit', 'tagihan.delete',
            'pembayaran.view', 'pembayaran.create', 'pembayaran.cancel',
            'laporan.view', 'whatsapp.view', 'mikrotik.view',
            'audit.view',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'setting.manage',
        ]);

        $kasir->syncPermissions([
            'dashboard.view',
            'pelanggan.view',
            'tagihan.view',
            'pembayaran.view',
            'pembayaran.create',
        ]);

        $teknisi->syncPermissions([
            'dashboard.view',
            'pelanggan.view',
            'paket.view',
            'router.view', 'router.create', 'router.edit', 'router.delete',
            'mikrotik.view',
        ]);

        $viewer->syncPermissions([
            'dashboard.view',
            'pelanggan.view',
            'paket.view',
            'router.view',
            'tagihan.view',
            'pembayaran.view',
            'laporan.view',
        ]);
    }
}
