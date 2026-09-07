<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_transfers')) {
            if (!Schema::hasColumn('warehouse_transfers', 'company_id')) {
                Schema::table('warehouse_transfers', function (Blueprint $table) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('status');
                    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                    $table->index(['company_id', 'branch_id'], 'idx_wt_company_branch');
                });
            }

            // Backfill company_id from master_gudang (gudang_asal_id) or branch_id
            DB::statement("
                UPDATE warehouse_transfers wt
                JOIN master_gudang g ON wt.gudang_asal_id = g.id
                SET wt.company_id = g.company_id
                WHERE wt.company_id IS NULL AND g.company_id IS NOT NULL;
            ");

            DB::statement("
                UPDATE warehouse_transfers wt
                JOIN branches b ON wt.branch_id = b.id
                SET wt.company_id = b.company_id
                WHERE wt.company_id IS NULL AND b.company_id IS NOT NULL;
            ");

            // Fallback default
            $defaultCompanyId = session('active_company_id') ?? DB::table('companies')->value('id') ?? 1;
            DB::table('warehouse_transfers')
                ->whereNull('company_id')
                ->update(['company_id' => $defaultCompanyId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('warehouse_transfers') && Schema::hasColumn('warehouse_transfers', 'company_id')) {
            Schema::table('warehouse_transfers', function (Blueprint $table) {
                try {
                    $table->dropForeign(['company_id']);
                } catch (\Exception $e) {}
                try {
                    $table->dropIndex('idx_wt_company_branch');
                } catch (\Exception $e) {}
                $table->dropColumn('company_id');
            });
        }
    }
};
