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

        $permissions = ['lihat-gr', 'tambah-gr', 'hapus-gr'];
        $createdPermissions = [];

        foreach ($permissions as $name) {
            $createdPermissions[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $rolesToAssign = ['Super Admin', 'Owner', 'Admin', 'Warehouse', 'Supervisor Warehouse'];
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

        $permissions = ['lihat-gr', 'tambah-gr', 'hapus-gr'];
        foreach ($permissions as $name) {
            $perm = Permission::where('name', $name)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
