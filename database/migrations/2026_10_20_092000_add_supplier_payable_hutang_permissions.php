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

        $hutangPermissions = [
            'lihat-hutang',
            'cetak-hutang',
        ];

        // 1. Buat permissions jika belum ada
        foreach ($hutangPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 2. Berikan ke Role yang berwenang
        $authorizedRoles = [
            'Super Admin',
            'Owner',
            'Director',
            'Manager Finance',
            'Finance',
            'Accounting',
            'Admin',
            'Purchasing',
            'Manager Produksi',
        ];

        $roles = Role::whereIn('name', $authorizedRoles)->get();
        foreach ($roles as $role) {
            foreach ($hutangPermissions as $permName) {
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

        $hutangPermissions = [
            'lihat-hutang',
            'cetak-hutang',
        ];

        foreach ($hutangPermissions as $permName) {
            $perm = Permission::where('name', $permName)->first();
            if ($perm) {
                $perm->delete();
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
