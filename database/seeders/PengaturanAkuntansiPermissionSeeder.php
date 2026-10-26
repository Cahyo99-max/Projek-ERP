<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PengaturanAkuntansiPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache Spatie Permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar Hak Akses Pengaturan Akuntansi
        $akuntansiPermissions = [
            'lihat-pengaturan-akuntansi', // Akses menu & tab pengaturan akuntansi
            'edit-pengaturan-akuntansi',  // Konfigurasi & simpan mapping akun
        ];

        // Buat Permissions jika belum ada
        foreach ($akuntansiPermissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        // 3. Berikan hak akses penuh ke role yang berwenang
        $fullAccessRoles = [
            'Super Admin',
            'Accounting',
            'Finance',
            'Keuangan',
            'Manager Finance',
            'Owner',
            'Director',
            'Admin',
        ];

        foreach ($fullAccessRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($akuntansiPermissions);
            }
        }

        // 4. Reset cache lagi agar instan berlaku
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
