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
            'lihat-peminjaman',
            'tambah-peminjaman',
            'edit-peminjaman',
            'hapus-peminjaman',
            'approve-peminjaman', // Approve 1: Persetujuan Atasan / Manager (Mengetahui)
            'siapkan-peminjaman', // Approve 2: Verifikasi & Penyiapan Fisik Gudang
            'serah-terima-peminjaman', // Approve 3: Serah Terima & Pengeluaran Barang ke Pameran
            'kembalikan-peminjaman', // Pengembalian & QC Fisik Gudang
        ];

        $createdPermissions = [];
        foreach ($permissions as $name) {
            $createdPermissions[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Assign to relevant roles
        $rolesToAssign = [
            'Super Admin',
            'Owner',
            'Admin',
            'Warehouse',
            'Supervisor Warehouse',
            'Sales',
            'Marketing',
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
            'lihat-peminjaman',
            'tambah-peminjaman',
            'edit-peminjaman',
            'hapus-peminjaman',
            'approve-peminjaman',
            'siapkan-peminjaman',
            'serah-terima-peminjaman',
            'kembalikan-peminjaman',
        ];

        foreach ($permissions as $name) {
            $perm = Permission::where('name', $name)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
