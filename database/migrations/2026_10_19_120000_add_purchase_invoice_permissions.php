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
            'lihat-purchase-invoice',
            'tambah-purchase-invoice',
            'edit-purchase-invoice',
            'hapus-purchase-invoice',
            'approve-purchase-invoice',
            'cetak-purchase-invoice',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Assign to Super Admin, Owner, Director, Admin
        $fullRoles = ['Super Admin', 'Owner', 'Director', 'Admin'];
        foreach ($fullRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Assign to Purchasing & Manager Purchasing
        $purchasingRole = Role::where('name', 'Purchasing')->first();
        if ($purchasingRole) {
            $purchasingRole->givePermissionTo([
                'lihat-purchase-invoice',
                'tambah-purchase-invoice',
                'edit-purchase-invoice',
                'cetak-purchase-invoice',
            ]);
        }

        // Assign to Accounting & Finance Roles
        $financeRoles = ['Accounting', 'Finance', 'Manager Finance', 'Manager Produksi'];
        foreach ($financeRoles as $fRole) {
            $role = Role::where('name', $fRole)->first();
            if ($role) {
                $role->givePermissionTo([
                    'lihat-purchase-invoice',
                    'tambah-purchase-invoice',
                    'edit-purchase-invoice',
                    'cetak-purchase-invoice',
                    'approve-purchase-invoice',
                ]);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'lihat-purchase-invoice',
            'tambah-purchase-invoice',
            'edit-purchase-invoice',
            'hapus-purchase-invoice',
            'approve-purchase-invoice',
            'cetak-purchase-invoice',
        ];

        Permission::whereIn('name', $permissions)->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
