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

        $permName = 'lihat-jurnal-pembayaran-utang';

        $permission = Permission::firstOrCreate([
            'name'       => $permName,
            'guard_name' => 'web',
        ]);

        // Default: Super Admin & Owner selalu mendapatkan hak akses penuh.
        // Role lain (Accounting, Finance, dll) dapat diaktifkan melalui UI Pengaturan Hak Akses (Role & Permission).
        $rolesToAssign = [
            'Super Admin',
            'Owner',
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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::where('name', 'lihat-jurnal-pembayaran-utang')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
