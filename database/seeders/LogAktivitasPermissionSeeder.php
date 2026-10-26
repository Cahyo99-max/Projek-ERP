<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LogAktivitasPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache Spatie Permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar Hak Akses Log Aktivitas User
        $permissions = [
            'lihat-log-aktivitas-user', // Hak akses melihat halaman dan log aktivitas
            'hapus-log-aktivitas-user', // Hak akses membersihkan / menghapus log secara permanen
        ];

        // Buat Permissions jika belum ada
        foreach ($permissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        // 3. Berikan hak akses penuh ke Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // 4. Berikan hak akses 'lihat-log-aktivitas-user' ke role manajemen tinggi lainnya
        $viewOnlyRoles = ['Admin', 'Director', 'Owner'];
        foreach ($viewOnlyRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo('lihat-log-aktivitas-user');
            }
        }

        // 5. Reset cache lagi agar instan berlaku
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
