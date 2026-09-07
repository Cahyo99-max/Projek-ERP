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
            if (!Schema::hasColumn('tenders', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('tenders', 'kategori_instansi')) {
                $table->string('kategori_instansi', 50)->nullable()->default('Pemerintah / B2G')->after('customer_id');
            }
            if (!Schema::hasColumn('tenders', 'tahap_tender')) {
                $table->string('tahap_tender', 50)->nullable()->default('Draft RKS')->after('status');
            }
            if (!Schema::hasColumn('tenders', 'sales_order_id')) {
                $table->foreignId('sales_order_id')->nullable()->after('catatan')->constrained('sales_orders')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('sales_order_id');
            $table->dropColumn(['kategori_instansi', 'tahap_tender']);
        });
    }
};
