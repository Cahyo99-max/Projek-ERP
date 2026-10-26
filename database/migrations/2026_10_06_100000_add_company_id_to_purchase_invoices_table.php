<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('supplier_id')->constrained('companies')->onDelete('cascade');
                $table->index(['company_id', 'status'], 'idx_pi_company_status');
            }
        });

        // Populate company_id for existing purchase_invoices:
        // If branch_id is set, company_id is NULL (cabang input).
        // If branch_id is NULL, assign company_id (PT input).
        $invoices = DB::table('purchase_invoices')->get();
        foreach ($invoices as $inv) {
            if (!empty($inv->branch_id)) {
                DB::table('purchase_invoices')->where('id', $inv->id)->update(['company_id' => null]);
            } else {
                $companyId = null;
                if (!empty($inv->gr_id)) {
                    $gr = DB::table('goods_receipts')->where('id', $inv->gr_id)->first();
                    $companyId = $gr?->company_id;
                }
                if (!$companyId && !empty($inv->po_id)) {
                    $po = DB::table('purchase_orders')->where('id', $inv->po_id)->first();
                    $companyId = $po?->company_id;
                }
                if (!$companyId) {
                    $defaultCompany = DB::table('companies')->first();
                    $companyId = $defaultCompany?->id ?? 1;
                }
                DB::table('purchase_invoices')->where('id', $inv->id)->update(['company_id' => $companyId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_pi_company_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
