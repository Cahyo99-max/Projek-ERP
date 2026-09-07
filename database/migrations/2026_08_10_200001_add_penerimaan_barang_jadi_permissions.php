<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations for Penerimaan Barang Jadi permissions.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'lihat-penerimaan-barang-jadi',
            'tambah-penerimaan-barang-jadi',
            'detail-penerimaan-barang-jadi',
            'edit-penerimaan-barang-jadi',
            'hapus-penerimaan-barang-jadi',
            'cetak-penerimaan-barang-jadi',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign to Super Admin & Owner roles if exist
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $owner = Role::where('name', 'Owner')->first();
        if ($owner) {
            $owner->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'lihat-penerimaan-barang-jadi',
            'tambah-penerimaan-barang-jadi',
            'detail-penerimaan-barang-jadi',
            'edit-penerimaan-barang-jadi',
            'hapus-penerimaan-barang-jadi',
            'cetak-penerimaan-barang-jadi',
        ];

        foreach ($permissions as $perm) {
            $p = Permission::where('name', $perm)->first();
            if ($p) {
                $p->delete();
            }
        }
    }
};
