<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'mulai-produksi-spk',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $rolesToSync = Role::whereIn('name', ['Super Admin', 'Owner', 'Admin', 'Manajer Produksi', 'Kepala Produksi'])->get();
        foreach ($rolesToSync as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        Permission::where('name', 'mulai-produksi-spk')->delete();
    }
};
