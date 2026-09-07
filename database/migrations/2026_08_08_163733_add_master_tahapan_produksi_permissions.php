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
            'lihat-master-tahapan',
            'tambah-master-tahapan',
            'edit-master-tahapan',
            'hapus-master-tahapan',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Berikan semua permission ke Super Admin & Owner
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $owner      = Role::where('name', 'Owner')->first();

        if ($superAdmin) $superAdmin->givePermissionTo($permissions);
        if ($owner)      $owner->givePermissionTo($permissions);
    }

    public function down(): void
    {
        $permissions = [
            'lihat-master-tahapan',
            'tambah-master-tahapan',
            'edit-master-tahapan',
            'hapus-master-tahapan',
        ];

        foreach ($permissions as $perm) {
            Permission::where('name', $perm)->delete();
        }
    }
};
