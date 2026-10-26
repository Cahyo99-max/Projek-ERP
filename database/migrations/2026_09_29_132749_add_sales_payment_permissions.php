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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'lihat-sales-payment',
            'tambah-sales-payment',
            'hapus-sales-payment',
            'cetak-sales-payment',
        ];

        $roles = Role::whereIn('name', ['Super Admin', 'Owner'])->get();

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            foreach ($roles as $role) {
                if (!$role->hasPermissionTo($perm)) {
                    $role->givePermissionTo($perm);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'lihat-sales-payment',
            'tambah-sales-payment',
            'hapus-sales-payment',
            'cetak-sales-payment',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
