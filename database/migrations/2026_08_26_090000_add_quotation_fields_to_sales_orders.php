<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom-kolom dari Penawaran yang diperlukan saat konversi SO.
     * Menggunakan unsignedBigInteger tanpa FK constraint untuk menghindari masalah.
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Referensi ke penawaran & tender (tanpa FK constraint agar tidak error)
            if (!Schema::hasColumn('sales_orders', 'sales_quotation_id')) {
                $table->unsignedBigInteger('sales_quotation_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('sales_orders', 'tender_id')) {
                $table->unsignedBigInteger('tender_id')->nullable()->after('sales_quotation_id');
            }
            if (!Schema::hasColumn('sales_orders', 'sales_id')) {
                $table->unsignedBigInteger('sales_id')->nullable()->after('tender_id');
            }

            // Diskon persen
            if (!Schema::hasColumn('sales_orders', 'diskon_persen')) {
                $table->decimal('diskon_persen', 5, 2)->default(0)->after('diskon_tipe');
            }

            // PPN / PPh fields dari penawaran (tanpa FK constraint)
            if (!Schema::hasColumn('sales_orders', 'ppn_pajak_id')) {
                $table->unsignedBigInteger('ppn_pajak_id')->nullable()->after('pajak_nominal');
            }
            if (!Schema::hasColumn('sales_orders', 'tipe_ppn')) {
                $table->string('tipe_ppn', 30)->nullable()->after('ppn_pajak_id');
            }
            if (!Schema::hasColumn('sales_orders', 'tarif_ppn')) {
                $table->decimal('tarif_ppn', 5, 2)->default(0)->after('tipe_ppn');
            }
            if (!Schema::hasColumn('sales_orders', 'nilai_ppn')) {
                $table->decimal('nilai_ppn', 18, 2)->default(0)->after('tarif_ppn');
            }
            if (!Schema::hasColumn('sales_orders', 'pph_pajak_id')) {
                $table->unsignedBigInteger('pph_pajak_id')->nullable()->after('nilai_ppn');
            }
            if (!Schema::hasColumn('sales_orders', 'tipe_pph')) {
                $table->string('tipe_pph', 30)->nullable()->after('pph_pajak_id');
            }
            if (!Schema::hasColumn('sales_orders', 'tarif_pph')) {
                $table->decimal('tarif_pph', 5, 2)->default(0)->after('tipe_pph');
            }
            if (!Schema::hasColumn('sales_orders', 'nilai_pph')) {
                $table->decimal('nilai_pph', 18, 2)->default(0)->after('tarif_pph');
            }

            // Biaya pengiriman
            if (!Schema::hasColumn('sales_orders', 'biaya_pengiriman')) {
                $table->decimal('biaya_pengiriman', 18, 2)->default(0)->after('nilai_pph');
            }

            // Grand Total (total akhir setelah semua komponen: subtotal - diskon + ppn - pph + pengiriman)
            if (!Schema::hasColumn('sales_orders', 'grand_total')) {
                $table->decimal('grand_total', 18, 2)->default(0)->after('biaya_pengiriman');
            }

            // HPP & Margin
            if (!Schema::hasColumn('sales_orders', 'total_hpp')) {
                $table->decimal('total_hpp', 18, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('sales_orders', 'estimasi_laba_kotor')) {
                $table->decimal('estimasi_laba_kotor', 18, 2)->default(0)->after('total_hpp');
            }
            if (!Schema::hasColumn('sales_orders', 'margin_persen')) {
                $table->decimal('margin_persen', 5, 2)->default(0)->after('estimasi_laba_kotor');
            }

            // DP / Termin
            if (!Schema::hasColumn('sales_orders', 'tipe_dp')) {
                $table->string('tipe_dp', 20)->nullable()->after('margin_persen');
            }
            if (!Schema::hasColumn('sales_orders', 'dp_persen')) {
                $table->decimal('dp_persen', 5, 2)->default(0)->after('tipe_dp');
            }
            if (!Schema::hasColumn('sales_orders', 'dp_nominal')) {
                $table->decimal('dp_nominal', 18, 2)->default(0)->after('dp_persen');
            }
            if (!Schema::hasColumn('sales_orders', 'sisa_tagihan')) {
                $table->decimal('sisa_tagihan', 18, 2)->default(0)->after('dp_nominal');
            }

            // Syarat ketentuan
            if (!Schema::hasColumn('sales_orders', 'syarat_ketentuan')) {
                $table->text('syarat_ketentuan')->nullable()->after('catatan');
            }
        });

        // Tambahkan kolom ke sales_order_details juga
        Schema::table('sales_order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_order_details', 'nama_barang_custom')) {
                $table->string('nama_barang_custom', 255)->nullable()->after('barang_id');
            }
            if (!Schema::hasColumn('sales_order_details', 'deskripsi_item')) {
                $table->text('deskripsi_item')->nullable()->after('nama_barang_custom');
            }
            if (!Schema::hasColumn('sales_order_details', 'kategori_id')) {
                $table->unsignedBigInteger('kategori_id')->nullable()->after('deskripsi_item');
            }
            if (!Schema::hasColumn('sales_order_details', 'nama_satuan')) {
                $table->string('nama_satuan', 50)->nullable()->after('satuan_id');
            }
            if (!Schema::hasColumn('sales_order_details', 'hpp_satuan')) {
                $table->decimal('hpp_satuan', 18, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('sales_order_details', 'total_hpp')) {
                $table->decimal('total_hpp', 18, 2)->default(0)->after('hpp_satuan');
            }
            if (!Schema::hasColumn('sales_order_details', 'estimasi_laba')) {
                $table->decimal('estimasi_laba', 18, 2)->default(0)->after('total_hpp');
            }
            if (!Schema::hasColumn('sales_order_details', 'margin_persen')) {
                $table->decimal('margin_persen', 5, 2)->default(0)->after('estimasi_laba');
            }
            if (!Schema::hasColumn('sales_order_details', 'urutan')) {
                $table->unsignedSmallInteger('urutan')->default(0)->after('margin_persen');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $cols = [
                'sales_quotation_id', 'tender_id', 'sales_id',
                'diskon_persen', 'ppn_pajak_id', 'tipe_ppn', 'tarif_ppn', 'nilai_ppn',
                'pph_pajak_id', 'tipe_pph', 'tarif_pph', 'nilai_pph',
                'biaya_pengiriman', 'grand_total', 'total_hpp', 'estimasi_laba_kotor',
                'margin_persen', 'tipe_dp', 'dp_persen', 'dp_nominal', 'sisa_tagihan',
                'syarat_ketentuan',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('sales_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('sales_order_details', function (Blueprint $table) {
            $cols = [
                'nama_barang_custom', 'deskripsi_item', 'kategori_id', 'nama_satuan',
                'hpp_satuan', 'total_hpp', 'estimasi_laba', 'margin_persen', 'urutan',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('sales_order_details', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
