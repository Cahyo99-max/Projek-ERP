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
        if (Schema::hasTable('stock_opnames')) {
            Schema::table('stock_opnames', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_opnames', 'company_id')) {
                    $table->foreignId('company_id')
                        ->nullable()
                        ->after('gudang_id')
                        ->constrained('companies')
                        ->onDelete('set null');
                }
                if (!Schema::hasColumn('stock_opnames', 'branch_id')) {
                    $table->foreignId('branch_id')
                        ->nullable()
                        ->after('company_id')
                        ->constrained('branches')
                        ->onDelete('set null');
                }
                try {
                    $table->index(['company_id', 'branch_id'], 'idx_so_company_branch');
                    $table->index(['company_id', 'status'], 'idx_so_company_status');
                } catch (\Exception $e) {}
            });

            // Backfill company_id and branch_id from related master_gudang table
            try {
                DB::statement("
                    UPDATE stock_opnames so
                    JOIN master_gudang g ON so.gudang_id = g.id
                    SET so.company_id = g.company_id,
                        so.branch_id  = g.cabang_id
                    WHERE so.company_id IS NULL
                ");
            } catch (\Exception $e) {
                // Ignore backfill errors on empty tables
            }

            // Fallback for remaining null company_id
            $defaultCompanyId = session('active_company_id') ?? DB::table('companies')->value('id') ?? 1;
            DB::table('stock_opnames')
                ->whereNull('company_id')
                ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_opnames')) {
            Schema::table('stock_opnames', function (Blueprint $table) {
                if (Schema::hasColumn('stock_opnames', 'company_id')) {
                    try { $table->dropForeign(['company_id']); } catch (\Exception $e) {}
                    $table->dropColumn('company_id');
                }
                if (Schema::hasColumn('stock_opnames', 'branch_id')) {
                    try { $table->dropForeign(['branch_id']); } catch (\Exception $e) {}
                    $table->dropColumn('branch_id');
                }
            });
        }
    }
};
