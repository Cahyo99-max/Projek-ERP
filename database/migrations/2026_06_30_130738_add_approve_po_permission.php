<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // Reset Spatie permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create the permission if it doesn't already exist
        $perm = Permission::firstOrCreate(['name' => 'approve-po', 'guard_name' => 'web']);

        // Sync with roles
        $rolesToAssign = ['Super Admin', 'Owner', 'Director', 'Admin'];
        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $perm = Permission::where('name', 'approve-po')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
