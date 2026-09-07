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

        $permission = Permission::firstOrCreate(['name' => 'lihat-stok-aktual', 'guard_name' => 'web']);

        $rolesToAssign = ['Super Admin', 'Owner', 'Admin', 'Warehouse', 'Supervisor Warehouse', 'Purchasing', 'Accounting'];
        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permission);
            }
        }
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::where('name', 'lihat-stok-aktual')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
