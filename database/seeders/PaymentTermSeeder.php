<?php

namespace Database\Seeders;

use App\Models\PaymentTerm;
use Illuminate\Database\Seeder;

class PaymentTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terms = [
            ['nama_term' => 'COD (Cash on Delivery)', 'jumlah_hari' => 0],
            ['nama_term' => 'Net 7', 'jumlah_hari' => 7],
            ['nama_term' => 'Net 15', 'jumlah_hari' => 15],
            ['nama_term' => 'Net 30', 'jumlah_hari' => 30],
            ['nama_term' => 'Net 45', 'jumlah_hari' => 45],
            ['nama_term' => 'Net 60', 'jumlah_hari' => 60],
        ];

        foreach ($terms as $term) {
            PaymentTerm::firstOrCreate(
                ['nama_term' => $term['nama_term']],
                ['jumlah_hari' => $term['jumlah_hari']]
            );
        }
    }
}
