<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PengaturanAplikasiPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache Spatie Permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar Hak Akses Pengaturan Aplikasi
        $permissions = [
            'lihat-pengaturan-aplikasi', // Hak akses melihat menu & halaman pengaturan aplikasi
            'edit-pengaturan-aplikasi',  // Hak akses mengubah nama aplikasi, logo, dan pengaturan sistem
        ];

        // Buat Permissions jika belum ada
        foreach ($permissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        // 3. Berikan hak akses penuh ke Super Admin & Owner
        $fullRoles = ['Super Admin', 'Owner'];
        foreach ($fullRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // 4. Berikan hak akses ke Admin (view & edit)
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // 5. Reset cache lagi agar instan berlaku
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
