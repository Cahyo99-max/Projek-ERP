<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = [
            'lihat-sales-payment',
            'tambah-sales-payment',
            'edit-sales-payment',
            'lihat-jurnal-sales-payment',
            'batalkan-sales-payment',
            'hapus-sales-payment',
            'approve-sales-payment',
            'cetak-sales-payment',
        ];

        // Buat semua permissions jika belum ada
        foreach ($allPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 1. Super Admin & Owner: Semua permissions
        $fullRoles = Role::whereIn('name', ['Super Admin', 'Owner'])->get();
        foreach ($fullRoles as $role) {
            $role->givePermissionTo($allPermissions);
        }

        // 2. Director: Monitor, Jurnal, Cetak, Approve
        $directorRole = Role::where('name', 'Director')->first();
        if ($directorRole) {
            $directorRole->givePermissionTo([
                'lihat-sales-payment',
                'lihat-jurnal-sales-payment',
                'cetak-sales-payment',
                'approve-sales-payment',
            ]);
        }

        // 3. Manager Finance: Lengkap untuk manajemen kas/piutang
        $mgrFinanceRole = Role::where('name', 'Manager Finance')->first();
        if ($mgrFinanceRole) {
            $mgrFinanceRole->givePermissionTo([
                'lihat-sales-payment',
                'tambah-sales-payment',
                'edit-sales-payment',
                'lihat-jurnal-sales-payment',
                'batalkan-sales-payment',
                'hapus-sales-payment',
                'approve-sales-payment',
                'cetak-sales-payment',
            ]);
        }

        // 4. Finance & Admin: Input, Edit, Cetak, Lihat
        $financeRoles = Role::whereIn('name', ['Finance', 'Admin'])->get();
        foreach ($financeRoles as $fRole) {
            $fRole->givePermissionTo([
                'lihat-sales-payment',
                'tambah-sales-payment',
                'edit-sales-payment',
                'lihat-jurnal-sales-payment',
                'hapus-sales-payment',
                'cetak-sales-payment',
            ]);
        }

        // 5. Accounting: Verifikasi Jurnal, Lihat, Cetak
        $accountingRole = Role::where('name', 'Accounting')->first();
        if ($accountingRole) {
            $accountingRole->givePermissionTo([
                'lihat-sales-payment',
                'lihat-jurnal-sales-payment',
                'cetak-sales-payment',
            ]);
        }

        // 6. Sales: Input & Edit Draf Pembayaran, Cetak Kwitansi
        $salesRole = Role::where('name', 'Sales')->first();
        if ($salesRole) {
            $salesRole->givePermissionTo([
                'lihat-sales-payment',
                'tambah-sales-payment',
                'edit-sales-payment',
                'hapus-sales-payment',
                'cetak-sales-payment',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = [
            'edit-sales-payment',
            'lihat-jurnal-sales-payment',
            'batalkan-sales-payment',
            'approve-sales-payment',
        ];

        Permission::whereIn('name', $newPermissions)->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
