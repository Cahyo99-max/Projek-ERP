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
        // 1. Buat permission 'lihat-dashboard-keuangan' jika belum ada
        $perm = Permission::firstOrCreate(['name' => 'lihat-dashboard-keuangan', 'guard_name' => 'web']);

        // 2. Assign ke Super Admin dan Owner
        $roles = ['Super Admin', 'Owner', 'Finance', 'Accounting', 'Manager Keuangan'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role && !$role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $perm = Permission::where('name', 'lihat-dashboard-keuangan')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
