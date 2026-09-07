<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('chart_of_accounts')) {
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('chart_of_accounts', 'company_id')) {
                    $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
                    $table->index('company_id');
                }
            });

            // Backfill existing records with company_id = 1 or active company
            DB::table('chart_of_accounts')->whereNull('company_id')->update(['company_id' => 1]);

            // Drop global unique constraint on code if exists
            try {
                Schema::table('chart_of_accounts', function (Blueprint $table) {
                    $table->dropUnique('chart_of_accounts_code_unique');
                });
            } catch (\Exception $e) {
                // Ignore if unique index name differs or already dropped
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chart_of_accounts')) {
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('chart_of_accounts', 'company_id')) {
                    $table->dropForeign(['company_id']);
                    $table->dropColumn('company_id');
                }
            });
        }
    }
};
