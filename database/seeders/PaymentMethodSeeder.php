<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['kode_metode' => 'CASH', 'nama_metode' => 'Tunai (Cash)', 'keterangan' => 'Pembayaran tunai langsung'],
            ['kode_metode' => 'TRANSFER', 'nama_metode' => 'Transfer Bank', 'keterangan' => 'Pembayaran melalui transfer rekening bank'],
            ['kode_metode' => 'CC', 'nama_metode' => 'Kartu Kredit (Credit Card)', 'keterangan' => 'Pembayaran menggunakan kartu kredit'],
            ['kode_metode' => 'QRIS', 'nama_metode' => 'QRIS / E-Wallet', 'keterangan' => 'Pembayaran digital melalui QRIS, GoPay, OVO, ShopeePay'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['kode_metode' => $method['kode_metode']],
                [
                    'nama_metode' => $method['nama_metode'],
                    'keterangan' => $method['keterangan'],
                    'status' => 'Active',
                ]
            );
        }
    }
}
