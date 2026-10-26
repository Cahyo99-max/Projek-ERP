<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PettyCashPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache Spatie Permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar 7 Hak Akses Spesifik Kas Kecil (Petty Cash)
        $pettyCashPermissions = [
            'lihat-petty-cash',        // Akses halaman awal / dashboard kas kecil
            'filter-petty-cash',       // Filter data transaksi kas kecil
            'export-excel-petty-cash', // Ekspor data kas kecil ke format Excel (.xlsx)
            'cetak-pdf-petty-cash',    // Cetak laporan kas kecil & bon voucher (PDF)
            'catat-petty-cash',        // Catat pengeluaran kas kecil (+Catat Pengeluaran)
            'detail-petty-cash',       // Lihat rincian detail voucher & jurnal akuntansi
            'batalkan-petty-cash',     // Pembatalan transaksi kas kecil (Void)
        ];

        // Buat Permissions jika belum ada
        foreach ($pettyCashPermissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        // 3. Berikan hak akses penuh ke role-role keuangan & operasional
        $fullAccessRoles = [
            'Super Admin',
            'Accounting',
            'Finance',
            'Keuangan',
            'Manager Finance',
            'Admin',
        ];

        foreach ($fullAccessRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($pettyCashPermissions);
            }
        }

        // 4. Berikan hak pantau, filter, detail & ekspor/cetak ke pimpinan eksekutif (Owner & Director)
        $executiveRoles = ['Owner', 'Director'];
        $executivePermissions = [
            'lihat-petty-cash',
            'filter-petty-cash',
            'detail-petty-cash',
            'cetak-pdf-petty-cash',
            'export-excel-petty-cash',
        ];

        foreach ($executiveRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($executivePermissions);
            }
        }

        // 5. Reset cache Spatie Permission setelah sinkronisasi
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if (isset($this->command)) {
            $this->command->info('Hak akses Petty Cash (Kas Kecil) berhasil dibuat & disinkronkan!');
        }
    }
}
