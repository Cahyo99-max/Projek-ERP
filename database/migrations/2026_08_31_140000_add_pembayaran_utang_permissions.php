<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'lihat-pembayaran-utang',
            'tambah-pembayaran-utang',
            'detail-pembayaran-utang',
            'edit-pembayaran-utang',
            'posting-pembayaran-utang',
            'batalkan-pembayaran-utang',
            'cetak-pembayaran-utang',
        ];

        $createdPermissions = [];
        foreach ($permissions as $permName) {
            $createdPermissions[] = Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);
        }

        // Hanya Super Admin & Owner yang otomatis mendapat semua permission (pemilik sistem).
        // Semua role lain (Admin, Director, Finance, Accounting, Purchasing, dst)
        // dimulai dari NOLL dan dikonfigurasi secara fleksibel via UI Pengaturan Hak Akses.
        // Mekanisme: siapapun yang diberi permission 'lihat-pembayaran-utang' = bisa akses,
        // yang tidak diberi = tidak bisa akses sama sekali, berlaku untuk semua role.
        $rolesToAssign = [
            'Super Admin',
            'Owner',
        ];

        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($createdPermissions as $perm) {
                    $role->givePermissionTo($perm);
                }
            }
        }
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'lihat-pembayaran-utang',
            'tambah-pembayaran-utang',
            'detail-pembayaran-utang',
            'edit-pembayaran-utang',
            'posting-pembayaran-utang',
            'batalkan-pembayaran-utang',
            'cetak-pembayaran-utang',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::where('name', $permName)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
