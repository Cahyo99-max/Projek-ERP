<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'lihat-dashboard-purchasing', 'guard_name' => 'web']);
        
        // Auto-assign to Super Admin and Purchasing by default
        $rolesToAssign = ['Super Admin', 'Purchasing'];
        foreach ($rolesToAssign as $roleName) {
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
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
        $permission = \Spatie\Permission\Models\Permission::where('name', 'lihat-dashboard-purchasing')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
