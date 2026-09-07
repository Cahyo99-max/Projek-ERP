<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_po_company_status');
                $table->index(['company_id', 'branch_id'], 'idx_po_company_branch');
            }
        });

        // Backfill company_id from branch_id if available, or from related PR, or fallback to default active company
        try {
            DB::statement("
                UPDATE purchase_orders po
                INNER JOIN branches b ON po.branch_id = b.id
                SET po.company_id = b.company_id
                WHERE po.company_id IS NULL AND b.company_id IS NOT NULL
            ");
        } catch (\Exception $e) {}

        try {
            DB::statement("
                UPDATE purchase_orders po
                INNER JOIN purchase_requisitions pr ON po.pr_id = pr.id
                SET po.company_id = pr.company_id
                WHERE po.company_id IS NULL AND pr.company_id IS NOT NULL
            ");
        } catch (\Exception $e) {}

        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') 
                            ?? DB::table('companies')->value('id') ?? 1;

        if ($defaultCompanyId) {
            DB::table('purchase_orders')
              ->whereNull('company_id')
              ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'company_id')) {
                try { $table->dropForeign(['company_id']); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_po_company_status'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_po_company_branch'); } catch (\Exception $e) {}
                $table->dropColumn('company_id');
            }
        });
    }
};
