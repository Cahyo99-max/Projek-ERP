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
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipts', 'company_id')) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('goods_receipts', 'branch_id')) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('company_id')
                    ->constrained('branches')
                    ->nullOnDelete();
            }

            $table->index(['company_id', 'branch_id'], 'idx_gr_company_branch');
            $table->index(['company_id', 'status'], 'idx_gr_company_status');
        });

        // Backfill company_id and branch_id from related purchase_orders table
        try {
            DB::statement("
                UPDATE goods_receipts gr
                JOIN purchase_orders po ON gr.po_id = po.id
                SET gr.company_id = po.company_id,
                    gr.branch_id = po.branch_id
                WHERE gr.company_id IS NULL;
            ");
        } catch (\Exception $e) {
            // Ignore if join update fails
        }

        // Fallback: set default company_id = 1 for remaining rows
        $defaultCompanyId = session('active_company_id') ?? DB::table('companies')->value('id') ?? 1;
        DB::table('goods_receipts')
            ->whereNull('company_id')
            ->update(['company_id' => $defaultCompanyId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipts', 'branch_id')) {
                try { $table->dropForeign(['branch_id']); } catch (\Exception $e) {}
                $table->dropColumn('branch_id');
            }

            if (Schema::hasColumn('goods_receipts', 'company_id')) {
                try { $table->dropForeign(['company_id']); } catch (\Exception $e) {}
                $table->dropColumn('company_id');
            }
        });
    }
};
