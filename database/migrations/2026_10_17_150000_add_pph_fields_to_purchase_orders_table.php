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
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'pph_pajak_id')) {
                $table->foreignId('pph_pajak_id')
                    ->nullable()
                    ->after('total_pajak')
                    ->constrained('master_pajaks')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('purchase_orders', 'tarif_pph')) {
                $table->decimal('tarif_pph', 5, 2)
                    ->default(0.00)
                    ->after('pph_pajak_id');
            }

            if (!Schema::hasColumn('purchase_orders', 'pph_nominal')) {
                $table->decimal('pph_nominal', 20, 2)
                    ->default(0.00)
                    ->after('tarif_pph');
            }

            if (!Schema::hasColumn('purchase_orders', 'total_pph')) {
                $table->decimal('total_pph', 20, 2)
                    ->default(0.00)
                    ->after('pph_nominal');
            }

            if (!Schema::hasColumn('purchase_orders', 'pph_coa_id')) {
                $table->foreignId('pph_coa_id')
                    ->nullable()
                    ->after('total_pph')
                    ->constrained('chart_of_accounts')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('purchase_orders', 'pph_coa_id')) {
                $table->dropForeign(['pph_coa_id']);
                $columnsToDrop[] = 'pph_coa_id';
            }
            if (Schema::hasColumn('purchase_orders', 'total_pph')) {
                $columnsToDrop[] = 'total_pph';
            }
            if (Schema::hasColumn('purchase_orders', 'pph_nominal')) {
                $columnsToDrop[] = 'pph_nominal';
            }
            if (Schema::hasColumn('purchase_orders', 'tarif_pph')) {
                $columnsToDrop[] = 'tarif_pph';
            }
            if (Schema::hasColumn('purchase_orders', 'pph_pajak_id')) {
                $table->dropForeign(['pph_pajak_id']);
                $columnsToDrop[] = 'pph_pajak_id';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
