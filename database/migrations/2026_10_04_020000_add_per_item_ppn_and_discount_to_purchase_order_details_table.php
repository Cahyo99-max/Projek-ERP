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
        if (Schema::hasTable('purchase_order_details')) {
            Schema::table('purchase_order_details', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_order_details', 'diskon_persen')) {
                    $table->decimal('diskon_persen', 5, 2)->default(0)->after('diskon_item');
                }
                if (!Schema::hasColumn('purchase_order_details', 'diskon_tipe')) {
                    $table->string('diskon_tipe', 10)->default('percent')->after('diskon_persen');
                }
                if (!Schema::hasColumn('purchase_order_details', 'ppn_pajak_id')) {
                    $table->unsignedBigInteger('ppn_pajak_id')->nullable()->after('subtotal');
                    $table->foreign('ppn_pajak_id')->references('id')->on('master_pajaks')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_order_details', 'tarif_ppn')) {
                    $table->decimal('tarif_ppn', 5, 2)->default(0)->after('ppn_pajak_id');
                }
                if (!Schema::hasColumn('purchase_order_details', 'ppn_nominal')) {
                    $table->decimal('ppn_nominal', 15, 2)->default(0)->after('tarif_ppn');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_order_details')) {
            Schema::table('purchase_order_details', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_order_details', 'ppn_pajak_id')) {
                    $table->dropForeign(['ppn_pajak_id']);
                    $table->dropColumn('ppn_pajak_id');
                }
                if (Schema::hasColumn('purchase_order_details', 'tarif_ppn')) {
                    $table->dropColumn('tarif_ppn');
                }
                if (Schema::hasColumn('purchase_order_details', 'ppn_nominal')) {
                    $table->dropColumn('ppn_nominal');
                }
                if (Schema::hasColumn('purchase_order_details', 'diskon_tipe')) {
                    $table->dropColumn('diskon_tipe');
                }
                if (Schema::hasColumn('purchase_order_details', 'diskon_persen')) {
                    $table->dropColumn('diskon_persen');
                }
            });
        }
    }
};
