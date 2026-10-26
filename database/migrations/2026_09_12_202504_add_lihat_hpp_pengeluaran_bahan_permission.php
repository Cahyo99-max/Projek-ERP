<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $perm = \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'lihat-hpp-pengeluaran-bahan',
            'guard_name' => 'web'
        ]);

        $roles = \Spatie\Permission\Models\Role::whereIn('name', ['Super Admin', 'Owner'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($perm);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Spatie\Permission\Models\Permission::where('name', 'lihat-hpp-pengeluaran-bahan')->delete();
    }
};
