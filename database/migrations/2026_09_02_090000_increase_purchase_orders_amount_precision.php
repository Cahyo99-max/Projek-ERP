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
            $table->decimal('kurs', 16, 4)->default(1.0000)->change();
            $table->decimal('diskon_nominal', 20, 2)->default(0.00)->change();
            $table->decimal('subtotal', 20, 2)->change();
            $table->decimal('total_diskon', 20, 2)->default(0.00)->change();
            $table->decimal('total_pajak', 20, 2)->default(0.00)->change();
            $table->decimal('grand_total', 20, 2)->change();
            $table->decimal('grand_total_idr', 20, 2)->change();
            if (Schema::hasColumn('purchase_orders', 'down_payment')) {
                $table->decimal('down_payment', 20, 2)->default(0.00)->change();
            }
            if (Schema::hasColumn('purchase_orders', 'biaya_lain')) {
                $table->decimal('biaya_lain', 20, 2)->default(0.00)->change();
            }
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->decimal('harga_satuan', 20, 2)->change();
            $table->decimal('diskon_item', 20, 2)->default(0.00)->change();
            $table->decimal('subtotal', 20, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('kurs', 12, 2)->default(1.00)->change();
            $table->decimal('diskon_nominal', 15, 2)->default(0.00)->change();
            $table->decimal('subtotal', 15, 2)->change();
            $table->decimal('total_diskon', 15, 2)->default(0.00)->change();
            $table->decimal('total_pajak', 15, 2)->default(0.00)->change();
            $table->decimal('grand_total', 15, 2)->change();
            $table->decimal('grand_total_idr', 15, 2)->change();
            if (Schema::hasColumn('purchase_orders', 'down_payment')) {
                $table->decimal('down_payment', 15, 2)->default(0.00)->change();
            }
            if (Schema::hasColumn('purchase_orders', 'biaya_lain')) {
                $table->decimal('biaya_lain', 15, 2)->default(0.00)->change();
            }
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->decimal('harga_satuan', 15, 2)->change();
            $table->decimal('diskon_item', 15, 2)->default(0.00)->change();
            $table->decimal('subtotal', 15, 2)->change();
        });
    }
};
