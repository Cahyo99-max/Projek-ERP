<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::findOrCreate('lihat-nilai-opname', 'web');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::findByName('lihat-nilai-opname', 'web');
        if ($permission) {
            $permission->delete();
        }
    }
};
