<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'lihat-monitor-aktif']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'lihat-log-aktivitas-user']);

        // Auto assign to Super Admin role
        $superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(['lihat-monitor-aktif', 'lihat-log-aktivitas-user']);
        }
    }

    public function down(): void
    {
        $superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->revokePermissionTo(['lihat-monitor-aktif', 'lihat-log-aktivitas-user']);
        }

        \Spatie\Permission\Models\Permission::whereIn('name', ['lihat-monitor-aktif', 'lihat-log-aktivitas-user'])->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
