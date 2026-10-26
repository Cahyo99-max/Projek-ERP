<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AlokasiBiayaPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache Spatie Permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar Hak Akses Spesifik Alokasi Biaya (Landed Cost)
        $alokasiBiayaPermissions = [
            'lihat-alokasi-biaya',        // Melihat Halaman & Daftar Alokasi Biaya
            'tambah-alokasi-biaya',       // Membuat Alokasi Biaya Baru (Draft & Input)
            'edit-alokasi-biaya',         // Mengedit Dokumen Alokasi Biaya Status Draft
            'detail-alokasi-biaya',       // Melihat Rincian / Detail Dokumen & Kapitalisasi HPP
            'posting-alokasi-biaya',      // Posting Dokumen Alokasi ke Persediaan & Jurnal
            'lihat-jurnal-alokasi-biaya', // Melihat Jurnal Akuntansi Alokasi Biaya
            'batalkan-alokasi-biaya',     // Membatalkan Dokumen Alokasi Biaya Draft
            'hapus-alokasi-biaya',        // Menghapus Dokumen Alokasi Biaya Status Draft
        ];

        // Buat Permissions jika belum ada
        foreach ($alokasiBiayaPermissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        // 3. Berikan hak akses penuh ke role manajerial, purchasing & accounting/keuangan
        $fullAccessRoles = [
            'Super Admin',
            'Admin',
            'Purchasing',
            'Accounting',
            'Finance',
            'Manager Finance',
            'Keuangan',
            'Owner',
            'Director',
        ];

        foreach ($fullAccessRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($alokasiBiayaPermissions);
            }
        }

        // 4. Reset cache Spatie Permission setelah sinkronisasi
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if (isset($this->command)) {
            $this->command->info('Hak akses Alokasi Biaya (Landed Cost) berhasil dibuat & disinkronkan ke role terkait!');
        }
    }
}
