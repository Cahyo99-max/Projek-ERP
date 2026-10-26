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

        $pettyCashPermissions = [
            'lihat-petty-cash',
            'filter-petty-cash',
            'export-excel-petty-cash',
            'cetak-pdf-petty-cash',
            'catat-petty-cash',
            'detail-petty-cash',
            'batalkan-petty-cash',
        ];

        // 1. Buat permissions jika belum ada
        foreach ($pettyCashPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 2. Berikan ke Role keuangan & operasional
        $fullRoles = ['Super Admin', 'Accounting', 'Finance', 'Keuangan', 'Manager Finance', 'Admin'];
        $roles = Role::whereIn('name', $fullRoles)->get();
        foreach ($roles as $role) {
            foreach ($pettyCashPermissions as $permName) {
                if (!$role->hasPermissionTo($permName)) {
                    $role->givePermissionTo($permName);
                }
            }
        }

        // 3. Berikan hak lihat, filter, detail, cetak & export ke Owner & Director
        $execRoles = ['Owner', 'Director'];
        $execPerms = ['lihat-petty-cash', 'filter-petty-cash', 'detail-petty-cash', 'cetak-pdf-petty-cash', 'export-excel-petty-cash'];
        $executiveRoles = Role::whereIn('name', $execRoles)->get();
        foreach ($executiveRoles as $role) {
            foreach ($execPerms as $permName) {
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

        $pettyCashPermissions = [
            'lihat-petty-cash',
            'filter-petty-cash',
            'export-excel-petty-cash',
            'cetak-pdf-petty-cash',
            'catat-petty-cash',
            'detail-petty-cash',
            'batalkan-petty-cash',
        ];

        Permission::whereIn('name', $pettyCashPermissions)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
