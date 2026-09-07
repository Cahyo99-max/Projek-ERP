<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            if (!Schema::hasColumn('tenders', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0.00)->after('nilai_estimasi');
            }
            if (!Schema::hasColumn('tenders', 'diskon_tipe')) {
                $table->string('diskon_tipe', 20)->default('Nominal')->after('subtotal');
            }
            if (!Schema::hasColumn('tenders', 'diskon_persen')) {
                $table->decimal('diskon_persen', 5, 2)->default(0.00)->after('diskon_tipe');
            }
            if (!Schema::hasColumn('tenders', 'diskon_nominal')) {
                $table->decimal('diskon_nominal', 15, 2)->default(0.00)->after('diskon_persen');
            }
            if (!Schema::hasColumn('tenders', 'tipe_ppn')) {
                $table->string('tipe_ppn', 20)->default('Non-PPN')->after('diskon_nominal'); // Non-PPN, Include, Exclude
            }
            if (!Schema::hasColumn('tenders', 'tarif_ppn')) {
                $table->decimal('tarif_ppn', 5, 2)->default(11.00)->after('tipe_ppn');
            }
            if (!Schema::hasColumn('tenders', 'nilai_ppn')) {
                $table->decimal('nilai_ppn', 15, 2)->default(0.00)->after('tarif_ppn');
            }
            if (!Schema::hasColumn('tenders', 'tipe_pph')) {
                $table->string('tipe_pph', 20)->default('Non-PPh')->after('nilai_ppn'); // Non-PPh, PPh-22-1.5, Lainnya
            }
            if (!Schema::hasColumn('tenders', 'tarif_pph')) {
                $table->decimal('tarif_pph', 5, 2)->default(0.00)->after('tipe_pph');
            }
            if (!Schema::hasColumn('tenders', 'nilai_pph')) {
                $table->decimal('nilai_pph', 15, 2)->default(0.00)->after('tarif_pph');
            }
            if (!Schema::hasColumn('tenders', 'biaya_ekstra')) {
                $table->decimal('biaya_ekstra', 15, 2)->default(0.00)->after('nilai_pph');
            }
            if (!Schema::hasColumn('tenders', 'catatan_biaya_ekstra')) {
                $table->string('catatan_biaya_ekstra', 255)->nullable()->after('biaya_ekstra');
            }
            if (!Schema::hasColumn('tenders', 'grand_total')) {
                $table->decimal('grand_total', 15, 2)->default(0.00)->after('catatan_biaya_ekstra');
            }
            if (!Schema::hasColumn('tenders', 'total_hpp')) {
                $table->decimal('total_hpp', 15, 2)->default(0.00)->after('grand_total');
            }
            if (!Schema::hasColumn('tenders', 'estimasi_laba_kotor')) {
                $table->decimal('estimasi_laba_kotor', 15, 2)->default(0.00)->after('total_hpp');
            }
            if (!Schema::hasColumn('tenders', 'margin_persen')) {
                $table->decimal('margin_persen', 5, 2)->default(0.00)->after('estimasi_laba_kotor');
            }
        });

        Schema::table('tender_items', function (Blueprint $table) {
            if (!Schema::hasColumn('tender_items', 'tier_harga')) {
                $table->string('tier_harga', 30)->nullable()->after('harga_estimasi');
            }
            if (!Schema::hasColumn('tender_items', 'diskon_persen')) {
                $table->decimal('diskon_persen', 5, 2)->default(0.00)->after('tier_harga');
            }
            if (!Schema::hasColumn('tender_items', 'diskon_nominal')) {
                $table->decimal('diskon_nominal', 15, 2)->default(0.00)->after('diskon_persen');
            }
            if (!Schema::hasColumn('tender_items', 'hpp_satuan')) {
                $table->decimal('hpp_satuan', 15, 2)->default(0.00)->after('subtotal');
            }
            if (!Schema::hasColumn('tender_items', 'total_hpp')) {
                $table->decimal('total_hpp', 15, 2)->default(0.00)->after('hpp_satuan');
            }
            if (!Schema::hasColumn('tender_items', 'estimasi_margin_nominal')) {
                $table->decimal('estimasi_margin_nominal', 15, 2)->default(0.00)->after('total_hpp');
            }
            if (!Schema::hasColumn('tender_items', 'estimasi_margin_persen')) {
                $table->decimal('estimasi_margin_persen', 5, 2)->default(0.00)->after('estimasi_margin_nominal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_items', function (Blueprint $table) {
            $table->dropColumn([
                'tier_harga',
                'diskon_persen',
                'diskon_nominal',
                'hpp_satuan',
                'total_hpp',
                'estimasi_margin_nominal',
                'estimasi_margin_persen',
            ]);
        });

        Schema::table('tenders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'diskon_tipe',
                'diskon_persen',
                'diskon_nominal',
                'tipe_ppn',
                'tarif_ppn',
                'nilai_ppn',
                'tipe_pph',
                'tarif_pph',
                'nilai_pph',
                'biaya_ekstra',
                'catatan_biaya_ekstra',
                'grand_total',
                'total_hpp',
                'estimasi_laba_kotor',
                'margin_persen',
            ]);
        });
    }
};
