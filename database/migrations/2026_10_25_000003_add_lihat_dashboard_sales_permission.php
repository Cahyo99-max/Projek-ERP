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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name'       => 'lihat-dashboard-sales',
            'guard_name' => 'web'
        ]);

        // Auto-assign to Super Admin, Owner, and Sales/Marketing roles by default
        $rolesToAssign = [
            'Super Admin',
            'Owner',
            'Sales',
            'Marketing',
            'Manager Sales',
            'Director Sales',
            'Sales Admin',
            'Admin Sales'
        ];

        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::where('name', 'lihat-dashboard-sales')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
