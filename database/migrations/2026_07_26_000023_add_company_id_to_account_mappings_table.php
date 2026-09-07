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
        if (Schema::hasTable('account_mappings')) {
            Schema::table('account_mappings', function (Blueprint $table) {
                if (!Schema::hasColumn('account_mappings', 'company_id')) {
                    $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
                    $table->index('company_id');
                }
            });

            // Backfill existing mapping rows with company_id = 1
            DB::table('account_mappings')->whereNull('company_id')->update(['company_id' => 1]);

            // Drop old global unique constraint if exists
            try {
                Schema::table('account_mappings', function (Blueprint $table) {
                    $table->dropUnique('account_mappings_group_mapping_key_unique');
                });
            } catch (\Exception $e) {
                // Ignore if unique name differs or already dropped
            }

            // Add composite unique constraint for company_id + group + mapping_key
            try {
                Schema::table('account_mappings', function (Blueprint $table) {
                    $table->unique(['company_id', 'group', 'mapping_key'], 'acc_map_comp_grp_key_unique');
                });
            } catch (\Exception $e) {
                // Ignore if already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('account_mappings')) {
            Schema::table('account_mappings', function (Blueprint $table) {
                if (Schema::hasColumn('account_mappings', 'company_id')) {
                    $table->dropUnique('acc_map_comp_grp_key_unique');
                    $table->dropForeign(['company_id']);
                    $table->dropColumn('company_id');
                }
            });
        }
    }
};
