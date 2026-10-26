<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KasKategoriMapping;
use App\Models\ChartOfAccount;
use App\Models\Company;

class KasKategoriMappingSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        if ($companies->isEmpty()) {
            $this->seedForCompany(null);
        } else {
            foreach ($companies as $comp) {
                $this->seedForCompany($comp->id);
            }
        }
    }

    private function seedForCompany($companyId)
    {
        // Helper to find COA by code for this company or fallback
        $findCoa = function ($code) use ($companyId) {
            $query = ChartOfAccount::where('code', $code);
            if ($companyId) {
                $coa = (clone $query)->where('company_id', $companyId)->first();
                if ($coa) return $coa->id;
            }
            $fallback = $query->first();
            return $fallback ? $fallback->id : null;
        };

        $mappings = [
            // KAS MASUK
            [
                'tipe'          => 'Masuk',
                'nama_kategori' => 'Pembayaran Pelanggan',
                'coa_code'      => '1104', // Piutang Usaha
                'deskripsi'     => 'Penerimaan pelunasan piutang atau tagihan penjualan dari pelanggan',
            ],
            [
                'tipe'          => 'Masuk',
                'nama_kategori' => 'Penerimaan Penjualan Tunai',
                'coa_code'      => '4101', // Penjualan Barang Dagang / Produk
                'deskripsi'     => 'Penerimaan penjualan barang atau jasa secara tunai langsung',
            ],
            [
                'tipe'          => 'Masuk',
                'nama_kategori' => 'Penerimaan Modal / Investor',
                'coa_code'      => '3101', // Modal Disetor
                'deskripsi'     => 'Setoran modal dari pemilik usaha atau investor perusahaan',
            ],
            [
                'tipe'          => 'Masuk',
                'nama_kategori' => 'Pendapatan Jasa / Lainnya',
                'coa_code'      => '4102', // Penjualan Jasa / Layanan
                'deskripsi'     => 'Penerimaan dari komisi, jasa maklon/jahit, atau pendapatan sampingan',
            ],
            [
                'tipe'          => 'Masuk',
                'nama_kategori' => 'Pendapatan Bunga Bank',
                'coa_code'      => '7101', // Pendapatan Bunga & Bagi Hasil Bank
                'deskripsi'     => 'Penerimaan bunga tabungan atau jasa giro bank',
            ],
            [
                'tipe'          => 'Masuk',
                'nama_kategori' => 'Pengembalian Uang Muka / Piutang',
                'coa_code'      => '1111', // Uang Muka Pembelian
                'deskripsi'     => 'Pengembalian kelebihan bayar, retur deposit, atau kasbon karyawan',
            ],

            // KAS KELUAR
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Pembayaran Supplier / Vendor',
                'coa_code'      => '2101', // Hutang Usaha
                'deskripsi'     => 'Pelunasan faktur atau hutang pembelian ke pemasok/vendor',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Biaya Gaji & Upah Karyawan',
                'coa_code'      => '6101', // Beban Gaji & Tunjangan
                'deskripsi'     => 'Pembayaran gaji pokok, lembur, bonus, dan tunjangan staf/operator',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Biaya Listrik, Air & Internet',
                'coa_code'      => '6102', // Beban Listrik, Air & Telepon
                'deskripsi'     => 'Pembayaran tagihan utilitas PLN, PAM, telepon, dan WiFi kantor/pabrik',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Biaya Sewa & Pemeliharaan',
                'coa_code'      => '6103', // Beban Sewa & Pemeliharaan
                'deskripsi'     => 'Biaya sewa gedung, service AC, renovasi, dan perbaikan mesin',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Biaya Logistik & Ongkos Kirim',
                'coa_code'      => '6104', // Beban Pengiriman Penjualan
                'deskripsi'     => 'Biaya ekspedisi pengiriman paket, kurir, dan ongkir ke customer',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Biaya Perlengkapan & ATK',
                'coa_code'      => '6105', // Beban Perlengkapan & ATK
                'deskripsi'     => 'Pembelian kertas, tinta printer, alat tulis, dan perlengkapan kantor',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Biaya Pemasaran & Promosi',
                'coa_code'      => '6107', // Beban Pemasaran & Promosi
                'deskripsi'     => 'Biaya iklan medsos, ads, banner, endorsement, dan promosi penjualan',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Biaya Perjalanan Dinas & Operasional',
                'coa_code'      => '6108', // Beban Perjalanan Dinas
                'deskripsi'     => 'Biaya bensin, tol, tiket transportasi, makan, dan jamuan tamu',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Biaya Administrasi Bank',
                'coa_code'      => '8101', // Beban Administrasi Bank
                'deskripsi'     => 'Biaya administrasi bulanan rekening bank dan biaya transfer antar bank',
            ],
            [
                'tipe'          => 'Keluar',
                'nama_kategori' => 'Pengeluaran Kas Kecil (Petty Cash)',
                'coa_code'      => '1101', // Kas Kecil / Kasir
                'deskripsi'     => 'Pengisian kembali dana kas kecil atau pengeluaran insidental kasir',
            ],
        ];

        foreach ($mappings as $item) {
            $coaId = $findCoa($item['coa_code']);
            KasKategoriMapping::updateOrCreate(
                [
                    'company_id'    => $companyId,
                    'tipe'          => $item['tipe'],
                    'nama_kategori' => $item['nama_kategori'],
                ],
                [
                    'coa_id'     => $coaId,
                    'deskripsi'  => $item['deskripsi'],
                    'is_active'  => true,
                ]
            );
        }
    }
}
