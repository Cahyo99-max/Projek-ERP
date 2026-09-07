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
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'lihat-dashboard', 'guard_name' => 'web']);
        
        // Auto-assign to all current roles to avoid breaking access on deployment
        $roles = \Spatie\Permission\Models\Role::all();
        foreach ($roles as $role) {
            $role->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = \Spatie\Permission\Models\Permission::where('name', 'lihat-dashboard')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
