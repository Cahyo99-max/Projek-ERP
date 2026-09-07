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
        // Panggil seeder role & user yang udah kita buat kemarin di sini cuy
        $this->call([
            UserDummySeeder::class,
            UrutanUserRoleSeeder::class,
            PermissionDetailSeeder::class,
            MasterDataSeeder::class,
            GarmentProductSeeder::class,
            MasterCustomerSeeder::class,
            TenderSeeder::class,
            ChartOfAccountSeeder::class,
            MasterTipeAkunSeeder::class,
            AccountMappingSeeder::class,
            MasterJenisBiayaSeeder::class,
        ]);
    }
}
