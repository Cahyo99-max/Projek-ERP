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
            if (!Schema::hasColumn('tenders', 'payment_term_id')) {
                $table->foreignId('payment_term_id')->nullable()->after('priority')->constrained('payment_terms')->nullOnDelete();
            }
            if (!Schema::hasColumn('tenders', 'tipe_dp')) {
                $table->enum('tipe_dp', ['Persen', 'Nominal'])->default('Persen')->after('payment_term_id');
            }
            if (!Schema::hasColumn('tenders', 'dp_persen')) {
                $table->decimal('dp_persen', 5, 2)->default(0.00)->after('tipe_dp');
            }
            if (!Schema::hasColumn('tenders', 'dp_nominal')) {
                $table->decimal('dp_nominal', 18, 2)->default(0.00)->after('dp_persen');
            }
            if (!Schema::hasColumn('tenders', 'sisa_tagihan')) {
                $table->decimal('sisa_tagihan', 18, 2)->default(0.00)->after('dp_nominal');
            }
            if (!Schema::hasColumn('tenders', 'catatan_pembayaran')) {
                $table->string('catatan_pembayaran', 255)->nullable()->after('sisa_tagihan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            if (Schema::hasColumn('tenders', 'payment_term_id')) {
                $table->dropForeign(['payment_term_id']);
                $table->dropColumn('payment_term_id');
            }
            $columnsToDrop = ['tipe_dp', 'dp_persen', 'dp_nominal', 'sisa_tagihan', 'catatan_pembayaran'];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('tenders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
