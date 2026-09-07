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
        if (Schema::hasTable('goods_issues')) {
            Schema::table('goods_issues', function (Blueprint $table) {
                if (!Schema::hasColumn('goods_issues', 'company_id')) {
                    $table->foreignId('company_id')
                        ->nullable()
                        ->after('gudang_id')
                        ->constrained('companies')
                        ->onDelete('set null');
                }
                if (!Schema::hasColumn('goods_issues', 'branch_id')) {
                    $table->foreignId('branch_id')
                        ->nullable()
                        ->after('company_id')
                        ->constrained('branches')
                        ->onDelete('set null');
                }
                $table->index(['company_id', 'branch_id'], 'idx_gi_company_branch');
                $table->index(['company_id', 'status'], 'idx_gi_company_status');
            });

            // Backfill company_id and branch_id from related master_gudang table
            try {
                DB::statement("
                    UPDATE goods_issues gi
                    JOIN master_gudang g ON gi.gudang_id = g.id
                    SET gi.company_id = g.company_id,
                        gi.branch_id  = g.cabang_id
                    WHERE gi.company_id IS NULL
                ");
            } catch (\Exception $e) {
                // Ignore backfill errors on empty tables
            }

            // Fallback for remaining null company_id
            $defaultCompanyId = session('active_company_id') ?? DB::table('companies')->value('id') ?? 1;
            DB::table('goods_issues')
                ->whereNull('company_id')
                ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('goods_issues')) {
            Schema::table('goods_issues', function (Blueprint $table) {
                if (Schema::hasColumn('goods_issues', 'company_id')) {
                    try { $table->dropForeign(['company_id']); } catch (\Exception $e) {}
                    $table->dropColumn('company_id');
                }
                if (Schema::hasColumn('goods_issues', 'branch_id')) {
                    try { $table->dropForeign(['branch_id']); } catch (\Exception $e) {}
                    $table->dropColumn('branch_id');
                }
            });
        }
    }
};
