<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $piutangPermissions = [
            'lihat-piutang',
            'cetak-piutang',
        ];

        // 1. Buat permissions jika belum ada
        foreach ($piutangPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 2. Berikan ke Role yang berwenang (dapat diaktifkan/dinonaktifkan sewaktu-waktu di menu Hak Akses)
        $authorizedRoles = [
            'Super Admin',
            'Owner',
            'Director',
            'Manager Finance',
            'Finance',
            'Accounting',
            'Admin',
            'Sales',
            'Manager Produksi',
        ];

        $roles = Role::whereIn('name', $authorizedRoles)->get();
        foreach ($roles as $role) {
            foreach ($piutangPermissions as $permName) {
                if (!$role->hasPermissionTo($permName)) {
                    $role->givePermissionTo($permName);
                }
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $piutangPermissions = [
            'lihat-piutang',
            'cetak-piutang',
        ];

        foreach ($piutangPermissions as $permName) {
            $perm = Permission::where('name', $permName)->first();
            if ($perm) {
                $perm->delete();
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
