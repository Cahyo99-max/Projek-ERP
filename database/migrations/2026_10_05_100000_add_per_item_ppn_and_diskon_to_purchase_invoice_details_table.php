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
        if (Schema::hasTable('purchase_invoice_details')) {
            Schema::table('purchase_invoice_details', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_invoice_details', 'diskon_item')) {
                    $table->decimal('diskon_item', 18, 2)->default(0)->after('harga_satuan');
                }
                if (!Schema::hasColumn('purchase_invoice_details', 'ppn_pajak_id')) {
                    $table->unsignedBigInteger('ppn_pajak_id')->nullable()->after('subtotal');
                    $table->foreign('ppn_pajak_id')->references('id')->on('master_pajaks')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_invoice_details', 'tarif_ppn')) {
                    $table->decimal('tarif_ppn', 5, 2)->default(0)->after('ppn_pajak_id');
                }
                if (!Schema::hasColumn('purchase_invoice_details', 'ppn_nominal')) {
                    $table->decimal('ppn_nominal', 18, 2)->default(0)->after('tarif_ppn');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_invoice_details')) {
            Schema::table('purchase_invoice_details', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_invoice_details', 'ppn_pajak_id')) {
                    $table->dropForeign(['ppn_pajak_id']);
                    $table->dropColumn('ppn_pajak_id');
                }
                if (Schema::hasColumn('purchase_invoice_details', 'tarif_ppn')) {
                    $table->dropColumn('tarif_ppn');
                }
                if (Schema::hasColumn('purchase_invoice_details', 'ppn_nominal')) {
                    $table->dropColumn('ppn_nominal');
                }
                if (Schema::hasColumn('purchase_invoice_details', 'diskon_item')) {
                    $table->dropColumn('diskon_item');
                }
            });
        }
    }
};
