<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_tipe_akuns', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        // Drop unique global index pada kode_tipe
        Schema::table('master_tipe_akuns', function (Blueprint $table) {
            // Drop old unique index on kode_tipe
            $table->dropUnique('master_tipe_akuns_kode_tipe_unique');
            // Add compound unique per PT
            $table->unique(['company_id', 'kode_tipe'], 'master_tipe_akuns_company_kode_unique');
        });

        // Hubungkan record yang sudah ada ke Company ID 1 (PT Persada Aman Sentosa)
        $firstCompany = DB::table('companies')->orderBy('id')->first();
        $defaultCompanyId = $firstCompany ? $firstCompany->id : 1;

        DB::table('master_tipe_akuns')->whereNull('company_id')->update([
            'company_id' => $defaultCompanyId
        ]);

        // Buat tipe akun standar untuk seluruh company lainnya yang belum memiliki tipe akun
        $companies = DB::table('companies')->get();
        $defaultTypes = [
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

        $now = now();
        foreach ($companies as $company) {
            foreach ($defaultTypes as $type) {
                $exists = DB::table('master_tipe_akuns')
                    ->where('company_id', $company->id)
                    ->where('kode_tipe', $type['kode_tipe'])
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$exists) {
                    DB::table('master_tipe_akuns')->insert(array_merge($type, [
                        'company_id' => $company->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_tipe_akuns', function (Blueprint $table) {
            $table->dropUnique('master_tipe_akuns_company_kode_unique');
            $table->unique('kode_tipe', 'master_tipe_akuns_kode_tipe_unique');
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
