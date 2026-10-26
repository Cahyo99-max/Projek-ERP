<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder Master Sistem, Hak Akses, Akuntansi & Master Data Pokok
        $this->call([
            UrutanUserRoleSeeder::class,
            PermissionDetailSeeder::class,
            KasBankPermissionSeeder::class,
            PettyCashPermissionSeeder::class,
            AlokasiBiayaPermissionSeeder::class,
            PengaturanAkuntansiPermissionSeeder::class,
            LogAktivitasPermissionSeeder::class,
            PengaturanAplikasiPermissionSeeder::class,
            MasterDataSeeder::class,
            ChartOfAccountSeeder::class,
            MasterTipeAkunSeeder::class,
            AccountMappingSeeder::class,
            MasterJenisBiayaSeeder::class,
            MasterTipePengeluaranSeeder::class,
            PaymentMethodSeeder::class,
            PaymentTermSeeder::class,
            KasKategoriMappingSeeder::class,
        ]);
    }
}
