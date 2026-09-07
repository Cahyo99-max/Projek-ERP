<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Hapus permission yang redundan/duplikat
        $redundant = [
            'posting-pembayaran-utang',
            'ajukan-pembayaran-utang',
            'tolak-pembayaran-utang',
        ];

        foreach ($redundant as $name) {
            $perm = Permission::where('name', $name)->first();
            if ($perm) {
                $perm->delete();
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
