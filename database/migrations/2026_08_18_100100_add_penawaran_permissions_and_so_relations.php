<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add relations to sales_orders table if not exists
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_orders', 'sales_quotation_id')) {
                    $table->foreignId('sales_quotation_id')->nullable()->after('customer_id')->constrained('sales_quotations')->nullOnDelete();
                }
                if (!Schema::hasColumn('sales_orders', 'tender_id')) {
                    $table->foreignId('tender_id')->nullable()->after('sales_quotation_id')->constrained('tenders')->nullOnDelete();
                }
            });
        }

        // 2. Register permissions
        $permissions = [
            'lihat-penawaran',
            'tambah-penawaran',
            'edit-penawaran',
            'hapus-penawaran',
            'revisi-penawaran',
            'approve-penawaran',
            'cetak-penawaran',
            'lihat-hpp-penawaran',
        ];

        foreach ($permissions as $permName) {
            Permission::findOrCreate($permName, 'web');
        }

        // Assign to Super Admin and Owner
        $roles = Role::whereIn('name', ['Super Admin', 'Owner'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (Schema::hasColumn('sales_orders', 'sales_quotation_id')) {
                    $table->dropForeign(['sales_quotation_id']);
                    $table->dropColumn('sales_quotation_id');
                }
                if (Schema::hasColumn('sales_orders', 'tender_id')) {
                    $table->dropForeign(['tender_id']);
                    $table->dropColumn('tender_id');
                }
            });
        }

        $permissions = [
            'lihat-penawaran',
            'tambah-penawaran',
            'edit-penawaran',
            'hapus-penawaran',
            'revisi-penawaran',
            'approve-penawaran',
            'cetak-penawaran',
            'lihat-hpp-penawaran',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
