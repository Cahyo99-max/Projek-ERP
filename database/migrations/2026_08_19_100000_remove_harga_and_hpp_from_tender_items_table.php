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
        Schema::table('tender_items', function (Blueprint $table) {
            $columnsToDrop = [
                'harga_estimasi',
                'tier_harga',
                'diskon_persen',
                'diskon_nominal',
                'subtotal',
                'hpp_satuan',
                'total_hpp',
                'estimasi_margin_nominal',
                'estimasi_margin_persen'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('tender_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_items', function (Blueprint $table) {
            if (!Schema::hasColumn('tender_items', 'harga_estimasi')) {
                $table->decimal('harga_estimasi', 15, 2)->default(0.00)->after('satuan');
            }
            if (!Schema::hasColumn('tender_items', 'tier_harga')) {
                $table->string('tier_harga', 30)->nullable()->after('harga_estimasi');
            }
            if (!Schema::hasColumn('tender_items', 'diskon_persen')) {
                $table->decimal('diskon_persen', 5, 2)->default(0.00)->after('tier_harga');
            }
            if (!Schema::hasColumn('tender_items', 'diskon_nominal')) {
                $table->decimal('diskon_nominal', 15, 2)->default(0.00)->after('diskon_persen');
            }
            if (!Schema::hasColumn('tender_items', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0.00)->after('diskon_nominal');
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
};
