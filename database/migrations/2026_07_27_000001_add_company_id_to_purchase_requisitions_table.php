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
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requisitions', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_pr_company_status');
                $table->index(['company_id', 'branch_id'], 'idx_pr_company_branch');
            }
        });

        // Set default company_id for existing purchase requisitions from branches table
        try {
            DB::statement("
                UPDATE purchase_requisitions pr
                INNER JOIN branches b ON pr.branch_id = b.id
                SET pr.company_id = b.company_id
                WHERE pr.company_id IS NULL AND b.company_id IS NOT NULL
            ");
        } catch (\Exception $e) {
            // Ignore if join fails
        }

        // Fallback for any remaining null company_id
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') 
                            ?? DB::table('companies')->value('id') ?? 1;

        if ($defaultCompanyId) {
            DB::table('purchase_requisitions')
              ->whereNull('company_id')
              ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requisitions', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_pr_company_status');
                $table->dropIndex('idx_pr_company_branch');
                $table->dropColumn('company_id');
            }
        });
    }
};
