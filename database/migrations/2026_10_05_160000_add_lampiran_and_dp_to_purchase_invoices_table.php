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
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'lampiran_faktur')) {
                $table->string('lampiran_faktur')->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('purchase_invoices', 'uang_muka')) {
                $table->decimal('uang_muka', 15, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('purchase_invoices', 'uang_muka_coa_id')) {
                $table->unsignedBigInteger('uang_muka_coa_id')->nullable()->after('uang_muka');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'lampiran_faktur')) {
                $table->dropColumn('lampiran_faktur');
            }
            if (Schema::hasColumn('purchase_invoices', 'uang_muka')) {
                $table->dropColumn('uang_muka');
            }
            if (Schema::hasColumn('purchase_invoices', 'uang_muka_coa_id')) {
                $table->dropColumn('uang_muka_coa_id');
            }
        });
    }
};
