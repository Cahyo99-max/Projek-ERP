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

        // 1. Permission Lihat Jurnal Akuntansi Pengiriman (Delivery Order)
        $jurnalPerm = Permission::firstOrCreate([
            'name'       => 'lihat-jurnal-delivery-order',
            'guard_name' => 'web',
        ]);

        // Berikan izin lihat jurnal pengiriman ke Super Admin, Owner, Director, Supervisor Warehouse, Admin, Accounting, Finance
        $roles = Role::whereIn('name', ['Super Admin', 'Owner', 'Director', 'Supervisor Warehouse', 'Admin', 'Accounting', 'Finance'])->get();
        foreach ($roles as $role) {
            if (!$role->hasPermissionTo($jurnalPerm)) {
                $role->givePermissionTo($jurnalPerm);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $jurnalPerm = Permission::where('name', 'lihat-jurnal-delivery-order')->first();
        if ($jurnalPerm) {
            $jurnalPerm->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
