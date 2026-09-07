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
        $dbName = DB::connection()->getDatabaseName();
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME, COLUMN_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? 
              AND TABLE_NAME = 'tenders' 
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName]);

        $columnsToDrop = [
            'payment_term_id',
            'tipe_dp',
            'dp_persen',
            'dp_nominal',
            'sisa_tagihan',
            'catatan_pembayaran',
            'subtotal',
            'diskon_tipe',
            'diskon_persen',
            'diskon_nominal',
            'ppn_pajak_id',
            'tipe_ppn',
            'tarif_ppn',
            'nilai_ppn',
            'pph_pajak_id',
            'tipe_pph',
            'tarif_pph',
            'nilai_pph',
            'biaya_ekstra',
            'catatan_biaya_ekstra',
            'biaya_ekstra_kena_ppn',
            'total_hpp',
            'estimasi_laba_kotor',
            'margin_persen',
        ];

        // Drop matching foreign keys first
        foreach ($foreignKeys as $fk) {
            if (in_array($fk->COLUMN_NAME, $columnsToDrop)) {
                DB::statement("ALTER TABLE `tenders` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }
        }

        Schema::table('tenders', function (Blueprint $table) use ($columnsToDrop) {
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('tenders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->string('tipe_dp')->nullable()->default('Persen');
            $table->decimal('dp_persen', 5, 2)->nullable()->default(0);
            $table->decimal('dp_nominal', 15, 2)->nullable()->default(0);
            $table->decimal('sisa_tagihan', 15, 2)->nullable()->default(0);
            $table->text('catatan_pembayaran')->nullable();
            $table->decimal('subtotal', 15, 2)->nullable()->default(0);
            $table->string('diskon_tipe')->nullable()->default('Nominal');
            $table->decimal('diskon_persen', 5, 2)->nullable()->default(0);
            $table->decimal('diskon_nominal', 15, 2)->nullable()->default(0);
            $table->unsignedBigInteger('ppn_pajak_id')->nullable();
            $table->string('tipe_ppn')->nullable()->default('Non-PPN');
            $table->decimal('tarif_ppn', 5, 2)->nullable()->default(0);
            $table->decimal('nilai_ppn', 15, 2)->nullable()->default(0);
            $table->unsignedBigInteger('pph_pajak_id')->nullable();
            $table->string('tipe_pph')->nullable()->default('Non-PPh');
            $table->decimal('tarif_pph', 5, 2)->nullable()->default(0);
            $table->decimal('nilai_pph', 15, 2)->nullable()->default(0);
            $table->decimal('biaya_ekstra', 15, 2)->nullable()->default(0);
            $table->string('catatan_biaya_ekstra')->nullable();
            $table->boolean('biaya_ekstra_kena_ppn')->nullable()->default(false);
            $table->decimal('total_hpp', 15, 2)->nullable()->default(0);
            $table->decimal('estimasi_laba_kotor', 15, 2)->nullable()->default(0);
            $table->decimal('margin_persen', 5, 2)->nullable()->default(0);
        });
    }
};
