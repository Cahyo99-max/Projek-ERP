<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class KasBankPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache Spatie Permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar Permission Khusus Modul Kas & Bank / Keuangan
        $kasBankPermissions = [
            'lihat-kas-bank',           // Melihat Dashboard & Tabel Rekening Kas & Bank
            'detail-kas-bank',          // Melihat Rincian / Detail Akun Rekening Kas & Bank
            'tambah-kas-bank',          // Menambah Akun/Rekening Kas & Bank Baru
            'edit-kas-bank',            // Mengubah Data Akun/Rekening Kas & Bank
            'lihat-mutasi-kas-bank',    // Melihat Buku Kas & Rekening Koran (Ledger)
            'tambah-kas-masuk',         // Menginput Transaksi Kas Masuk (Cash In)
            'tambah-kas-keluar',        // Menginput Transaksi Kas Keluar (Cash Out)
            'transfer-kas-bank',        // Melakukan Transfer Antar Rekening Kas & Bank
            'batalkan-mutasi-kas-bank', // Membatalkan / Void Transaksi Mutasi Kas & Bank
            'cetak-rekening-koran',     // Mencetak Buku Kas & Rekening Koran (Print Ledger)
        ];

        // Buat Permissions jika belum ada
        foreach ($kasBankPermissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        // 3. Buat / Pastikan Role "Keuangan" Ada
        $keuanganRole = Role::firstOrCreate(
            ['name' => 'Keuangan', 'guard_name' => 'web']
        );

        // Berikan permissions Kas & Bank serta hak operasional Keuangan dasar
        $keuanganPermissions = array_merge([
            'akses-accounting',
            'lihat-piutang',
            'cetak-piutang',
            'lihat-sales-payment',
            'tambah-sales-payment',
            'edit-sales-payment',
            'cetak-sales-payment',
            'lihat-pembayaran-utang',
            'tambah-pembayaran-utang',
            'detail-pembayaran-utang',
            'cetak-pembayaran-utang',
            'lihat-pajak',
            'lihat-currency',
            'lihat-payment-term',
            'lihat-payment-method',
        ], $kasBankPermissions);

        // Filter hanya permission yang benar-benar ada di database
        $existingKeuanganPerms = Permission::whereIn('name', $keuanganPermissions)->pluck('name')->toArray();
        $keuanganRole->syncPermissions($existingKeuanganPerms);

        // 4. Update Role-role finansial (Full Operasional Kas & Bank: Lihat, Detail, Tambah, Edit)
        $financialRoles = ['Super Admin', 'Finance', 'Accounting', 'Manager Finance'];

        foreach ($financialRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($kasBankPermissions);
            }
        }

        // 5. Update Role Eksekutif (Owner & Director: Hak Pantau & Lihat Detail, tanpa Edit data rekening)
        $executiveRoles = ['Owner', 'Director'];
        $executivePermissions = ['akses-accounting', 'lihat-kas-bank', 'detail-kas-bank', 'lihat-mutasi-kas-bank'];

        foreach ($executiveRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $validExecPerms = Permission::whereIn('name', $executivePermissions)->pluck('name')->toArray();
                $role->givePermissionTo($validExecPerms);
            }
        }

        // 6. Bersihkan akses yang tidak sesuai jika ada (misal Manager Produksi tidak boleh edit kas & bank)
        $managerProduksi = Role::where('name', 'Manager Produksi')->first();
        if ($managerProduksi) {
            $managerProduksi->revokePermissionTo(['edit-kas-bank', 'tambah-kas-bank']);
        }

        // Reset cache kembali setelah sync
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Role Keuangan dan Permission Kas & Bank berhasil dibuat & disinkronkan!');
    }
}
