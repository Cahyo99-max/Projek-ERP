<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'lihat-spk',
            'tambah-spk',
            'detail-spk',
            'edit-spk',
            'hapus-spk',
            'approve-spk',
            'cetak-spk',
            'input-realisasi-produksi',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign permissions to key roles automatically
        $rolesToSync = Role::whereIn('name', ['Super Admin', 'Owner', 'Admin', 'Manajer Produksi', 'Kepala Produksi'])->get();
        foreach ($rolesToSync as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        $permissions = [
            'detail-spk',
            'input-realisasi-produksi',
        ];

        foreach ($permissions as $perm) {
            Permission::where('name', $perm)->delete();
        }
    }
};
