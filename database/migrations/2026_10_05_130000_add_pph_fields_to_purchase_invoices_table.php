<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PPh dipotong (withholding) atas Faktur Pembelian, misal PPh 23.
     */
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'pph_pajak_id')) {
                $table->foreignId('pph_pajak_id')->nullable()->after('pajak_nominal')->constrained('master_pajaks')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_invoices', 'tarif_pph')) {
                $table->decimal('tarif_pph', 5, 2)->default(0.00)->after('pph_pajak_id');
            }
            if (!Schema::hasColumn('purchase_invoices', 'pph_nominal')) {
                $table->decimal('pph_nominal', 15, 2)->default(0.00)->after('tarif_pph');
            }
            if (!Schema::hasColumn('purchase_invoices', 'pph_coa_id')) {
                $table->foreignId('pph_coa_id')->nullable()->after('pph_nominal')->constrained('chart_of_accounts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropForeign(['pph_pajak_id']);
            $table->dropForeign(['pph_coa_id']);
            $table->dropColumn(['pph_pajak_id', 'tarif_pph', 'pph_nominal', 'pph_coa_id']);
        });
    }
};
