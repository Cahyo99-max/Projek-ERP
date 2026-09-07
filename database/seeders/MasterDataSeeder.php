<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Helpers\CodeGeneratorHelper;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds for initial master data.
     */
    public function run(): void
    {
        // 1. Seed Master Satuan
        $satuans = [
            ['singkatan' => 'Pcs', 'nama_satuan' => 'Pieces', 'deskripsi' => 'Satuan hitungan lembar/buah'],
            ['singkatan' => 'Kg', 'nama_satuan' => 'Kilogram', 'deskripsi' => 'Satuan berat standar'],
            ['singkatan' => 'Ltr', 'nama_satuan' => 'Liter', 'deskripsi' => 'Satuan volume cairan'],
            ['singkatan' => 'Mtr', 'nama_satuan' => 'Meter', 'deskripsi' => 'Satuan panjang'],
            ['singkatan' => 'Box', 'nama_satuan' => 'Box/Kardus', 'deskripsi' => 'Satuan kemasan kotak'],
        ];

        foreach ($satuans as $satuan) {
            $kode = CodeGeneratorHelper::generate('master_satuan', 'STN-', 'kode_satuan');
            DB::table('master_satuan')->insert(array_merge($satuan, [
                'kode_satuan' => $kode,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 2. Seed Master Brand
        $brands = [
            ['nama_brand' => 'Samsung', 'deskripsi' => 'Brand elektronik Samsung'],
            ['nama_brand' => 'Apple', 'deskripsi' => 'Brand premium Apple Inc'],
            ['nama_brand' => 'Toyota', 'deskripsi' => 'Brand otomotif Toyota'],
            ['nama_brand' => 'Unilever', 'deskripsi' => 'Brand barang konsumsi'],
            ['nama_brand' => 'General', 'deskripsi' => 'Brand umum/non-merk'],
        ];

        foreach ($brands as $brand) {
            $kode = CodeGeneratorHelper::generate('master_brand', 'BRD-', 'kode_brand');
            DB::table('master_brand')->insert(array_merge($brand, [
                'kode_brand' => $kode,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 3. Seed Master Tipe Barang
        $tipes = [
            ['nama_tipe' => 'Bahan Baku', 'deskripsi' => 'Raw material untuk kebutuhan produksi'],
            ['nama_tipe' => 'Barang Setengah Jadi', 'deskripsi' => 'WIP (Work In Progress) barang rakitan'],
            ['nama_tipe' => 'Barang Jadi', 'deskripsi' => 'Finished good yang siap dijual ke customer'],
            ['nama_tipe' => 'Suku Cadang', 'deskripsi' => 'Sparepart mesin pabrik atau peralatan'],
        ];

        foreach ($tipes as $tipe) {
            $kode = CodeGeneratorHelper::generate('master_tipe_barang', 'TPE-', 'kode_tipe');
            DB::table('master_tipe_barang')->insert(array_merge($tipe, [
                'kode_tipe' => $kode,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 4. Seed Master Warna
        $warnas = [
            ['nama_warna' => 'Hitam', 'kode_hex' => '#000000', 'deskripsi' => 'Warna dasar hitam'],
            ['nama_warna' => 'Putih', 'kode_hex' => '#FFFFFF', 'deskripsi' => 'Warna dasar putih'],
            ['nama_warna' => 'Merah', 'kode_hex' => '#FF0000', 'deskripsi' => 'Warna merah cerah'],
            ['nama_warna' => 'Biru', 'kode_hex' => '#0000FF', 'deskripsi' => 'Warna biru laut'],
            ['nama_warna' => 'Silver', 'kode_hex' => '#C0C0C0', 'deskripsi' => 'Warna perak metalik'],
        ];

        foreach ($warnas as $warna) {
            $kode = CodeGeneratorHelper::generate('master_warna', 'WRN-', 'kode_warna');
            DB::table('master_warna')->insert(array_merge($warna, [
                'kode_warna' => $kode,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 5. Seed Master Jenis Gudang
        $jenisGudangs = [
            ['nama_jenis_gudang' => 'Gudang Utama', 'deskripsi' => 'Gudang penyimpanan barang jadi utama', 'urutan' => 1],
            ['nama_jenis_gudang' => 'Gudang Bahan Baku', 'deskripsi' => 'Gudang penyimpanan material awal', 'urutan' => 2],
            ['nama_jenis_gudang' => 'Gudang Transit', 'deskripsi' => 'Gudang perantara antar cabang', 'urutan' => 3],
        ];

        foreach ($jenisGudangs as $jg) {
            $kode = CodeGeneratorHelper::generate('master_jenis_gudang', 'JGD-', 'kode_jenis_gudang');
            DB::table('master_jenis_gudang')->insert(array_merge($jg, [
                'kode_jenis_gudang' => $kode,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
