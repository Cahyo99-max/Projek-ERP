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

        $permissions = [
            'lihat-delivery-order',
            'tambah-delivery-order',
            'edit-delivery-order',
            'cetak-delivery-order',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);
        }

        // Grant 'lihat-delivery-order' and 'cetak-delivery-order'
        $viewPrintRoles = Role::whereIn('name', [
            'Super Admin', 'Owner', 'Director', 'Supervisor Warehouse', 'Warehouse', 'Sales', 'Admin', 'Staff'
        ])->get();
        foreach ($viewPrintRoles as $role) {
            $role->givePermissionTo(['lihat-delivery-order', 'cetak-delivery-order']);
        }

        // Grant 'tambah-delivery-order'
        $createRoles = Role::whereIn('name', [
            'Super Admin', 'Owner', 'Director', 'Supervisor Warehouse', 'Warehouse', 'Sales', 'Admin'
        ])->get();
        foreach ($createRoles as $role) {
            $role->givePermissionTo('tambah-delivery-order');
        }

        // Grant 'edit-delivery-order'
        $editRoles = Role::whereIn('name', [
            'Super Admin', 'Owner', 'Director', 'Supervisor Warehouse', 'Warehouse', 'Admin'
        ])->get();
        foreach ($editRoles as $role) {
            $role->givePermissionTo('edit-delivery-order');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', [
            'lihat-delivery-order',
            'tambah-delivery-order',
            'edit-delivery-order',
            'cetak-delivery-order',
        ])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
