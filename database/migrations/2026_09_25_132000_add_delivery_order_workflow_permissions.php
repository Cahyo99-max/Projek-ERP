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

        // 1. Permission Approval Pengiriman Surat Jalan
        $approvePerm = Permission::firstOrCreate([
            'name'       => 'approve-delivery-order',
            'guard_name' => 'web',
        ]);

        // 2. Permission Konfirmasi Barang Diterima / Selesaikan Pengiriman
        $completePerm = Permission::firstOrCreate([
            'name'       => 'selesai-delivery-order',
            'guard_name' => 'web',
        ]);

        // Berikan izin approve ke Super Admin, Owner, Director, Supervisor Warehouse
        $approverRoles = Role::whereIn('name', ['Super Admin', 'Owner', 'Director', 'Supervisor Warehouse'])->get();
        foreach ($approverRoles as $role) {
            if (!$role->hasPermissionTo($approvePerm)) {
                $role->givePermissionTo($approvePerm);
            }
        }

        // Berikan izin selesai pengiriman ke Super Admin, Owner, Director, Supervisor Warehouse, Warehouse, Sales, Admin
        $completerRoles = Role::whereIn('name', ['Super Admin', 'Owner', 'Director', 'Supervisor Warehouse', 'Warehouse', 'Sales', 'Admin'])->get();
        foreach ($completerRoles as $role) {
            if (!$role->hasPermissionTo($completePerm)) {
                $role->givePermissionTo($completePerm);
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

        $completePerm = Permission::where('name', 'selesai-delivery-order')->first();
        if ($completePerm) {
            $completePerm->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
