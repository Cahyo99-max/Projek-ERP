<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permission 'lihat-hpp-transfer'
        $perm = Permission::firstOrCreate(['name' => 'lihat-hpp-transfer', 'guard_name' => 'web']);

        // Automatically assign permission to Super Admin, Owner, Admin, and Manager Gudang roles if they exist
        $adminRoles = Role::whereIn('name', ['Super Admin', 'Owner', 'Admin', 'Manager Gudang', 'Kepala Gudang'])->get();
        foreach ($adminRoles as $role) {
            if (!$role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $perm = Permission::where('name', 'lihat-hpp-transfer')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
