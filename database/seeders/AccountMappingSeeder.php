<?php

namespace Database\Seeders;

use App\Models\AccountMapping;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class AccountMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = \App\Models\Company::pluck('id')->toArray();
        if (empty($companies)) {
            $companies = [1];
        }

        $defaultMappings = [
            // 1. DEFAULT ACCOUNTS
            [
                'group' => 'default',
                'mapping_key' => 'default_inventory',
                'code' => '1105',
                'description' => 'Akun persediaan default (Barang Dagang)',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'default_cogs',
                'code' => '5101',
                'description' => 'Akun HPP default (HPP Barang Dagang)',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'default_sales',
                'code' => '4101',
                'description' => 'Akun penjualan default',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'default_ap',
                'code' => '2101',
                'description' => 'Akun utang dagang supplier default',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'accounts_payable',
                'code' => '2101',
                'description' => 'Akun utang dagang supplier (AP)',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'unbilled_ap',
                'code' => '2102',
                'description' => 'Akun penerimaan barang belum ditagih (GR/IR)',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'default_ar',
                'code' => '1104',
                'description' => 'Akun piutang usaha customer default',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'default_gr_clearing',
                'code' => '2102',
                'description' => 'Akun penerimaan barang belum ditagih (GR/IR)',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'default_stock_adjustment',
                'code' => '5104',
                'description' => 'Akun selisih stock opname / adjustment',
            ],
            [
                'group' => 'default',
                'mapping_key' => 'default_opening_balance',
                'code' => '3202',
                'description' => 'Akun penyeimbang saldo awal persediaan / equity',
            ],

            // 2. FREIGHT & ONGKIR
            [
                'group' => 'freight',
                'mapping_key' => 'freight_in',
                'code' => '5103',
                'description' => 'Ongkir Pembelian Barang / Landed Cost (Biaya Angkut Pembelian)',
            ],
            [
                'group' => 'freight',
                'mapping_key' => 'freight_out',
                'code' => '6104',
                'description' => 'Ongkir Penjualan Barang / Kirim Customer (Beban Pengiriman Penjualan)',
            ],

            // 3. TAX (PAJAK)
            [
                'group' => 'tax',
                'mapping_key' => 'tax_ppn_masukan',
                'code' => '1107',
                'description' => 'Pajak Pertambahan Nilai Masukan (Pembelian)',
            ],
            [
                'group' => 'tax',
                'mapping_key' => 'tax_ppn_keluaran',
                'code' => '2103',
                'description' => 'Pajak Pertambahan Nilai Keluaran (Penjualan)',
            ],

            // 4. PAYMENT METHOD DEFAULTS
            [
                'group' => 'payment_method',
                'mapping_key' => 'pm_cash',
                'code' => '1101',
                'description' => 'Pembayaran Tunai / Kas Kecil',
            ],
            [
                'group' => 'payment_method',
                'mapping_key' => 'pm_bca',
                'code' => '1102',
                'description' => 'Pembayaran Transfer BCA',
            ],
            [
                'group' => 'payment_method',
                'mapping_key' => 'pm_mandiri',
                'code' => '1103',
                'description' => 'Pembayaran Transfer Mandiri',
            ],
        ];

        foreach ($companies as $companyId) {
            $coaMap = ChartOfAccount::where('company_id', $companyId)->pluck('id', 'code')->toArray();

            foreach ($defaultMappings as $mapping) {
                $code = $mapping['code'];
                if (isset($coaMap[$code])) {
                    AccountMapping::firstOrCreate(
                        [
                            'company_id'  => $companyId,
                            'group'       => $mapping['group'],
                            'mapping_key' => $mapping['mapping_key'],
                        ],
                        [
                            'coa_id'      => $coaMap[$code],
                            'description' => $mapping['description'],
                        ]
                    );
                }
            }
        }
    }
}
