<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\MasterJenisBiaya;
use Illuminate\Database\Seeder;

class MasterJenisBiayaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coaMap = ChartOfAccount::pluck('id', 'code')->toArray();

        $items = [
            [
                'kode_biaya' => 'EXP-001',
                'nama_jenis_biaya' => 'Ongkir / Pengiriman',
                'masuk_hpp' => true,
                'alokasi_biaya' => 'Berdasarkan Nilai Barang',
                'coa_code' => '1-1300', // Persediaan Barang / Biaya Angkut
                'status' => 'Active',
                'keterangan' => 'Ongkos kirim barang pembelian'
            ],
            [
                'kode_biaya' => 'EXP-002',
                'nama_jenis_biaya' => 'Asuransi',
                'masuk_hpp' => true,
                'alokasi_biaya' => 'Berdasarkan Nilai Barang',
                'coa_code' => '1-1300',
                'status' => 'Active',
                'keterangan' => 'Asuransi pengiriman barang'
            ],
            [
                'kode_biaya' => 'EXP-003',
                'nama_jenis_biaya' => 'Bongkar Muat',
                'masuk_hpp' => true,
                'alokasi_biaya' => 'Berdasarkan Nilai Barang',
                'coa_code' => '1-1300',
                'status' => 'Active',
                'keterangan' => 'Biaya penanganan bongkar muat'
            ],
            [
                'kode_biaya' => 'EXP-004',
                'nama_jenis_biaya' => 'Packing',
                'masuk_hpp' => true,
                'alokasi_biaya' => 'Berdasarkan Qty',
                'coa_code' => '1-1300',
                'status' => 'Active',
                'keterangan' => 'Biaya pengepakan khusus'
            ],
            [
                'kode_biaya' => 'EXP-005',
                'nama_jenis_biaya' => 'Administrasi',
                'masuk_hpp' => false,
                'alokasi_biaya' => 'Tanpa Alokasi',
                'coa_code' => '6-1200', // Beban Administrasi
                'status' => 'Active',
                'keterangan' => 'Biaya administrasi bank/dokumen'
            ],
            [
                'kode_biaya' => 'EXP-006',
                'nama_jenis_biaya' => 'Materai',
                'masuk_hpp' => false,
                'alokasi_biaya' => 'Tanpa Alokasi',
                'coa_code' => '6-1200',
                'status' => 'Active',
                'keterangan' => 'Bea materai dokumen perjanjian'
            ],
        ];

        foreach ($items as $data) {
            $coaCode = $data['coa_code'];
            unset($data['coa_code']);
            
            if (isset($coaMap[$coaCode])) {
                $data['coa_id'] = $coaMap[$coaCode];
            }

            MasterJenisBiaya::updateOrCreate(
                ['nama_jenis_biaya' => $data['nama_jenis_biaya']],
                $data
            );
        }
    }
}
