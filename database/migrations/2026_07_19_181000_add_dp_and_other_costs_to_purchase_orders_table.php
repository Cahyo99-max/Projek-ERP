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
            $table->decimal('down_payment', 15, 2)->default(0)->after('grand_total_idr');
            $table->decimal('biaya_lain', 15, 2)->default(0)->after('down_payment');
            $table->string('deskripsi_biaya_lain', 255)->nullable()->after('biaya_lain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['down_payment', 'biaya_lain', 'deskripsi_biaya_lain']);
        });
    }
};
