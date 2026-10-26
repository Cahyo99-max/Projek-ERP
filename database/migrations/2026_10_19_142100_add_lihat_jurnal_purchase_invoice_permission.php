<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

        // Register permission
        $permission = Permission::firstOrCreate(['name' => 'lihat-jurnal-purchase-invoice', 'guard_name' => 'web']);

        // Assign to Super Admin, Owner, Accounting, Finance, Manager Finance, Director
        $roles = Role::whereIn('name', ['Super Admin', 'Owner', 'Accounting', 'Finance', 'Manager Finance', 'Director'])->get();
        foreach ($roles as $role) {
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::where('name', 'lihat-jurnal-purchase-invoice')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
