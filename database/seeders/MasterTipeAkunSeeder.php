<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\MasterTipeAkun;
use Illuminate\Database\Seeder;

class MasterTipeAkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'kode_tipe' => 'AST',
                'nama_tipe' => 'Aset / Aktiva',
                'kategori_laporan' => 'neraca',
                'saldo_normal_default' => 'debit',
                'status' => 'Active',
                'keterangan' => 'Kas, Bank, Piutang, Persediaan, dan Aset Tetap'
            ],
            [
                'kode_tipe' => 'LIAB',
                'nama_tipe' => 'Kewajiban / Utang',
                'kategori_laporan' => 'neraca',
                'saldo_normal_default' => 'credit',
                'status' => 'Active',
                'keterangan' => 'Hutang Dagang, PPN Keluaran, Utang Pajak'
            ],
            [
                'kode_tipe' => 'EQT',
                'nama_tipe' => 'Ekuitas / Modal',
                'kategori_laporan' => 'neraca',
                'saldo_normal_default' => 'credit',
                'status' => 'Active',
                'keterangan' => 'Modal Disetor, Laba Ditahan'
            ],
            [
                'kode_tipe' => 'REV',
                'nama_tipe' => 'Pendapatan / Penjualan',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'credit',
                'status' => 'Active',
                'keterangan' => 'Penjualan Produk dan Jasa Utama'
            ],
            [
                'kode_tipe' => 'COGS',
                'nama_tipe' => 'Harga Pokok Penjualan (HPP)',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'debit',
                'status' => 'Active',
                'keterangan' => 'Modal Pokok Barang/Jasa Terjual & Ongkir Beli'
            ],
            [
                'kode_tipe' => 'EXP',
                'nama_tipe' => 'Beban Operasional',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'debit',
                'status' => 'Active',
                'keterangan' => 'Gaji, Utilitas, Ongkir Jual, Sewa, Penyusutan'
            ],
            [
                'kode_tipe' => 'OREV',
                'nama_tipe' => 'Pendapatan Lain-lain',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'credit',
                'status' => 'Active',
                'keterangan' => 'Pendapatan diluar operasional utama'
            ],
            [
                'kode_tipe' => 'OEXP',
                'nama_tipe' => 'Beban Lain-lain',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'debit',
                'status' => 'Active',
                'keterangan' => 'Beban diluar operasional utama'
            ],
        ];

        $createdMap = [];
        foreach ($types as $t) {
            $master = MasterTipeAkun::updateOrCreate(
                ['kode_tipe' => $t['kode_tipe']],
                $t
            );
            $createdMap[$t['kode_tipe']] = $master;
        }

        // Auto-link existing COA records to MasterTipeAkun
        $typeMapping = [
            'asset' => 'AST',
            'liability' => 'LIAB',
            'equity' => 'EQT',
            'revenue' => 'REV',
            'cogs' => 'COGS',
            'expense' => 'EXP',
            'other_revenue' => 'OREV',
            'other_expense' => 'OEXP',
        ];

        foreach ($typeMapping as $enumType => $kodeTipe) {
            if (isset($createdMap[$kodeTipe])) {
                ChartOfAccount::where('type', $enumType)
                    ->whereNull('master_tipe_akun_id')
                    ->update(['master_tipe_akun_id' => $createdMap[$kodeTipe]->id]);
            }
        }
    }
}
