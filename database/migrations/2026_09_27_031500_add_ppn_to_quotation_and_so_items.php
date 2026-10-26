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
        if (Schema::hasTable('sales_quotation_items')) {
            Schema::table('sales_quotation_items', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_quotation_items', 'ppn_pajak_id')) {
                    $table->unsignedBigInteger('ppn_pajak_id')->nullable()->after('subtotal');
                    $table->foreign('ppn_pajak_id')->references('id')->on('master_pajaks')->nullOnDelete();
                }
                if (!Schema::hasColumn('sales_quotation_items', 'tarif_ppn')) {
                    $table->decimal('tarif_ppn', 5, 2)->default(0)->after('ppn_pajak_id');
                }
                if (!Schema::hasColumn('sales_quotation_items', 'nilai_ppn')) {
                    $table->decimal('nilai_ppn', 15, 2)->default(0)->after('tarif_ppn');
                }
            });
        }

        if (Schema::hasTable('sales_order_details')) {
            Schema::table('sales_order_details', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_order_details', 'ppn_pajak_id')) {
                    $table->unsignedBigInteger('ppn_pajak_id')->nullable()->after('subtotal');
                    $table->foreign('ppn_pajak_id')->references('id')->on('master_pajaks')->nullOnDelete();
                }
                if (!Schema::hasColumn('sales_order_details', 'tarif_ppn')) {
                    $table->decimal('tarif_ppn', 5, 2)->default(0)->after('ppn_pajak_id');
                }
                if (!Schema::hasColumn('sales_order_details', 'nilai_ppn')) {
                    $table->decimal('nilai_ppn', 15, 2)->default(0)->after('tarif_ppn');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_quotation_items')) {
            Schema::table('sales_quotation_items', function (Blueprint $table) {
                if (Schema::hasColumn('sales_quotation_items', 'ppn_pajak_id')) {
                    $table->dropForeign(['ppn_pajak_id']);
                    $table->dropColumn('ppn_pajak_id');
                }
                if (Schema::hasColumn('sales_quotation_items', 'tarif_ppn')) {
                    $table->dropColumn('tarif_ppn');
                }
                if (Schema::hasColumn('sales_quotation_items', 'nilai_ppn')) {
                    $table->dropColumn('nilai_ppn');
                }
            });
        }

        if (Schema::hasTable('sales_order_details')) {
            Schema::table('sales_order_details', function (Blueprint $table) {
                if (Schema::hasColumn('sales_order_details', 'ppn_pajak_id')) {
                    $table->dropForeign(['ppn_pajak_id']);
                    $table->dropColumn('ppn_pajak_id');
                }
                if (Schema::hasColumn('sales_order_details', 'tarif_ppn')) {
                    $table->dropColumn('tarif_ppn');
                }
                if (Schema::hasColumn('sales_order_details', 'nilai_ppn')) {
                    $table->dropColumn('nilai_ppn');
                }
            });
        }
    }
};
