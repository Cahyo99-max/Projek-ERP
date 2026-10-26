<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom audit pembatalan pada tabel purchase_invoices
        if (Schema::hasTable('purchase_invoices')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_invoices', 'cancelled_by')) {
                    $table->unsignedBigInteger('cancelled_by')->nullable()->after('deleted_by');
                    $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('purchase_invoices', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
                }
                if (!Schema::hasColumn('purchase_invoices', 'alasan_pembatalan')) {
                    $table->string('alasan_pembatalan', 255)->nullable()->after('cancelled_at');
                }
            });
        }

        // 2. Daftarkan Hak Akses (Permission) untuk Pembatalan Transaksi Faktur Pembelian
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::firstOrCreate([
            'name'       => 'batalkan-purchase-invoice',
            'guard_name' => 'web',
        ]);

        // Default assign ke Super Admin & Owner (dan Admin jika ada)
        $rolesToAssign = ['Super Admin', 'Owner', 'Admin'];
        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_invoices')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_invoices', 'cancelled_by')) {
                    $table->dropForeign(['cancelled_by']);
                    $table->dropColumn('cancelled_by');
                }
                if (Schema::hasColumn('purchase_invoices', 'cancelled_at')) {
                    $table->dropColumn('cancelled_at');
                }
                if (Schema::hasColumn('purchase_invoices', 'alasan_pembatalan')) {
                    $table->dropColumn('alasan_pembatalan');
                }
            });
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::where('name', 'batalkan-purchase-invoice')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
