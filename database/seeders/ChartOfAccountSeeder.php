<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::pluck('id')->toArray();
        if (empty($companies)) {
            $companies = [1];
        }

        $accounts = [
            // ==========================================
            // 1. ASET / AKTIVA (1000)
            // ==========================================
            ['code' => '1000', 'name' => 'ASET', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true, 'parent_code' => null, 'description' => 'Induk Aset / Aktiva Utama'],
            
            ['code' => '1100', 'name' => 'Aset Lancar', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true, 'parent_code' => '1000', 'description' => 'Sub-induk Aset Lancar'],
            ['code' => '1101', 'name' => 'Kas Kecil / Kasir', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Kas Operasional Harian Toko/Kantor'],
            ['code' => '1102', 'name' => 'Bank BCA', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Rekening Bank BCA Operasional'],
            ['code' => '1103', 'name' => 'Bank Mandiri', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Rekening Bank Mandiri Operasional'],
            ['code' => '1104', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Piutang Penjualan Customer'],
            ['code' => '1105', 'name' => 'Persediaan Barang Dagang', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Persediaan Produk Utama / Inventory'],
            ['code' => '1106', 'name' => 'Persediaan Bahan Baku', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Persediaan Bahan Mentah Produksi'],
            ['code' => '1107', 'name' => 'PPN Masukan (Input VAT)', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Pajak Pertambahan Nilai Pembelian'],
            ['code' => '1108', 'name' => 'Persediaan Setengah Jadi (WIP)', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Persediaan Work In Progress'],
            ['code' => '1109', 'name' => 'Persediaan Barang Jadi', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Persediaan Finished Goods'],
            ['code' => '1110', 'name' => 'Persediaan Perlengkapan', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Supplies & Spareparts Stock'],
            ['code' => '1111', 'name' => 'Uang Muka Pembelian (DP Supplier)', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Uang Muka / Down Payment Pembelian'],
            ['code' => '1112', 'name' => 'Piutang Karyawan / Biaya Dibayar Dimuka', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100', 'description' => 'Kasbon Karyawan & Sewa Dibayar Dimuka'],

            ['code' => '1200', 'name' => 'Aset Tetap', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true, 'parent_code' => '1000', 'description' => 'Sub-induk Aset Tetap / Non-Lancar'],
            ['code' => '1201', 'name' => 'Peralatan & Mesin Kantor/Pabrik', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200', 'description' => 'Nilai Perolehan Peralatan & Mesin'],
            ['code' => '1202', 'name' => 'Kendaraan Operasional', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200', 'description' => 'Nilai Perolehan Kendaraan'],
            ['code' => '1203', 'name' => 'Bangunan & Gedung', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200', 'description' => 'Nilai Perolehan Bangunan'],
            ['code' => '1209', 'name' => 'Akumulasi Penyusutan Aset Tetap', 'type' => 'asset', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200', 'description' => 'Akumulasi Depresiasi Aset Tetap'],

            // ==========================================
            // 2. KEWAJIBAN / UTANG (2000)
            // ==========================================
            ['code' => '2000', 'name' => 'KEWAJIBAN', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true, 'parent_code' => null, 'description' => 'Induk Kewajiban / Utang'],
            
            ['code' => '2100', 'name' => 'Kewajiban Jangka Pendek', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true, 'parent_code' => '2000', 'description' => 'Sub-induk Utang Lancar'],
            ['code' => '2101', 'name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100', 'description' => 'Utang ke Supplier Pembelian'],
            ['code' => '2102', 'name' => 'Hutang Penerimaan Barang (GR/IR Clearing)', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100', 'description' => 'Penerimaan Barang Belum Ditagih Invoice'],
            ['code' => '2103', 'name' => 'PPN Keluaran (Output VAT)', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100', 'description' => 'Pajak Pertambahan Nilai Penjualan'],
            ['code' => '2104', 'name' => 'Hutang Gaji & Komisi', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100', 'description' => 'Akrual Gaji Karyawan Belum Dibayar'],
            ['code' => '2105', 'name' => 'Hutang Pajak PPh', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100', 'description' => 'Utang PPh 21 / 23 / 4(2)'],
            ['code' => '2106', 'name' => 'Uang Muka Penjualan (DP Customer)', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100', 'description' => 'Uang Muka Titipan Penjualan'],
            ['code' => '2107', 'name' => 'Retur Pembelian', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100', 'description' => 'Akun Penampung Retur Pembelian Supplier'],

            ['code' => '2200', 'name' => 'Kewajiban Jangka Panjang', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true, 'parent_code' => '2000', 'description' => 'Sub-induk Utang Jangka Panjang'],
            ['code' => '2201', 'name' => 'Hutang Bank Jangka Panjang', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2200', 'description' => 'Pinjaman Bank / Leasing Jangka Panjang'],

            // ==========================================
            // 3. EKUITAS / MODAL (3000)
            // ==========================================
            ['code' => '3000', 'name' => 'EKUITAS', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true, 'parent_code' => null, 'description' => 'Induk Ekuitas / Modal'],
            
            ['code' => '3100', 'name' => 'Modal Utama', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true, 'parent_code' => '3000', 'description' => 'Sub-induk Modal'],
            ['code' => '3101', 'name' => 'Modal Disetor', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '3100', 'description' => 'Modal Awal Disetor Pemilik'],
            ['code' => '3102', 'name' => 'Prive / Dividen Owner', 'type' => 'equity', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '3100', 'description' => 'Penarikan Dana Pemilik'],

            ['code' => '3200', 'name' => 'Laba', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true, 'parent_code' => '3000', 'description' => 'Sub-induk Akumulasi Laba'],
            ['code' => '3201', 'name' => 'Laba Ditahan (Retained Earnings)', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '3200', 'description' => 'Akumulasi Laba Tahun-tahun Lalu'],
            ['code' => '3202', 'name' => 'Saldo Awal Persediaan / Opening Balance Equity', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '3200', 'description' => 'Penyeimbang Saldo Awal / Opening Stock'],

            // ==========================================
            // 4. PENDAPATAN / PENJUALAN (4000)
            // ==========================================
            ['code' => '4000', 'name' => 'PENDAPATAN', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true, 'parent_code' => null, 'description' => 'Induk Pendapatan Usaha'],
            
            ['code' => '4100', 'name' => 'Pendapatan Usaha Utama', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true, 'parent_code' => '4000', 'description' => 'Sub-induk Penjualan'],
            ['code' => '4101', 'name' => 'Penjualan Barang Dagang / Produk', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4100', 'description' => 'Penjualan Produk Utama'],
            ['code' => '4102', 'name' => 'Penjualan Jasa / Layanan', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4100', 'description' => 'Pendapatan Jasa & Jahit'],
            ['code' => '4103', 'name' => 'Retur & Potongan Penjualan', 'type' => 'revenue', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '4100', 'description' => 'Diskon & Retur dari Customer'],

            // ==========================================
            // 5. HARGA POKOK PENJUALAN / HPP (5000)
            // ==========================================
            ['code' => '5000', 'name' => 'HARGA POKOK PENJUALAN', 'type' => 'cogs', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true, 'parent_code' => null, 'description' => 'Induk HPP'],
            
            ['code' => '5100', 'name' => 'HPP Utama', 'type' => 'cogs', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true, 'parent_code' => '5000', 'description' => 'Sub-induk Harga Pokok'],
            ['code' => '5101', 'name' => 'HPP Barang Dagang / Produk', 'type' => 'cogs', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '5100', 'description' => 'Harga Pokok Barang Terjual'],
            ['code' => '5102', 'name' => 'HPP Bahan Baku & Material', 'type' => 'cogs', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '5100', 'description' => 'Bahan Terpakai untuk Produksi'],
            ['code' => '5103', 'name' => 'Biaya Angkut Pembelian (Freight-In)', 'type' => 'cogs', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '5100', 'description' => 'Ongkir Pembelian & Landed Cost'],
            ['code' => '5104', 'name' => 'Selisih Stok Opname / Adjustment', 'type' => 'cogs', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '5100', 'description' => 'Selisih Penyesuaian Stock Opname'],
            ['code' => '5105', 'name' => 'HPP Barang Jadi & Setengah Jadi', 'type' => 'cogs', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '5100', 'description' => 'HPP Penjualan Produk Garment/Jadi'],

            // ==========================================
            // 6. BEBAN OPERASIONAL (6000)
            // ==========================================
            ['code' => '6000', 'name' => 'BEBAN OPERASIONAL', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true, 'parent_code' => null, 'description' => 'Induk Beban Operasional'],
            
            ['code' => '6100', 'name' => 'Beban Penjualan & Operasional', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true, 'parent_code' => '6000', 'description' => 'Sub-induk Beban Usaha'],
            ['code' => '6101', 'name' => 'Beban Gaji & Tunjangan Karyawan', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '6100', 'description' => 'Gaji, Bonus & Tunjangan Staff'],
            ['code' => '6102', 'name' => 'Beban Listrik, Air & Telepon', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '6100', 'description' => 'Utilitas Listrik PLN, Air & Internet'],
            ['code' => '6103', 'name' => 'Beban Sewa & Pemeliharaan Kantor/Gudang', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '6100', 'description' => 'Sewa Gedung & Perbaikan Fasilitas'],
            ['code' => '6104', 'name' => 'Beban Pengiriman Penjualan (Freight-Out)', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '6100', 'description' => 'Ongkir Kirim Barang ke Customer'],
            ['code' => '6105', 'name' => 'Beban Perlengkapan & ATK', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '6100', 'description' => 'Alat Tulis Kantor & Kertas'],
            ['code' => '6106', 'name' => 'Beban Penyusutan Aset Tetap', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '6100', 'description' => 'Depresiasi Bulanan Mesin & Kendaraan'],
            ['code' => '6107', 'name' => 'Beban Pemasaran & Promosi', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '6100', 'description' => 'Iklan, Banner & Marketing'],
            ['code' => '6108', 'name' => 'Beban Perjalanan Dinas & Entertainment', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'parent_code' => '6100', 'description' => 'BBM, Tol, Akomodasi & Jamuan'],

            // ==========================================
            // 7. PENDAPATAN & BEBAN LAIN-LAIN (7000 & 8000)
            // ==========================================
            ['code' => '7000', 'name' => 'PENDAPATAN LAIN-LAIN', 'type' => 'other_revenue', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true, 'parent_code' => null, 'description' => 'Induk Pendapatan Non-Operasional'],
            ['code' => '7101', 'name' => 'Pendapatan Bunga & Bagi Hasil Bank', 'type' => 'other_revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '7000', 'description' => 'Bunga Deposito & Jasa Giro'],
            ['code' => '7102', 'name' => 'Keuntungan Selisih Kurs / Diskon Pembayaran', 'type' => 'other_revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '7000', 'description' => 'Gain Currency & Potongan Pelunasan'],

            ['code' => '8000', 'name' => 'BEBAN LAIN-LAIN', 'type' => 'other_expense', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true, 'parent_code' => null, 'description' => 'Induk Beban Non-Operasional'],
            ['code' => '8101', 'name' => 'Beban Administrasi Bank & Pajak Bunga', 'type' => 'other_expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'parent_code' => '8000', 'description' => 'Biaya Admin Transfer & Pajak Bunga'],
            ['code' => '8102', 'name' => 'Kerugian Selisih Kurs & Penyesuaian', 'type' => 'other_expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'parent_code' => '8000', 'description' => 'Loss Foreign Exchange'],
        ];

        foreach ($companies as $companyId) {
            $createdMap = [];

            foreach ($accounts as $data) {
                $parentCode = $data['parent_code'] ?? null;
                $accountData = $data;
                unset($accountData['parent_code']);
                $accountData['company_id'] = $companyId;

                if ($parentCode && isset($createdMap[$parentCode])) {
                    $accountData['parent_id'] = $createdMap[$parentCode]->id;
                } else {
                    $accountData['parent_id'] = null;
                }

                $account = ChartOfAccount::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'code' => $accountData['code'],
                    ],
                    $accountData
                );

                $createdMap[$account->code] = $account;
            }
        }
    }
}

