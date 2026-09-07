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
        if (Schema::hasTable('master_jenis_biayas')) {
            Schema::table('master_jenis_biayas', function (Blueprint $table) {
                if (!Schema::hasColumn('master_jenis_biayas', 'company_id')) {
                    $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
                    $table->index('company_id');
                }
            });

            // Backfill existing rows with company_id = 1
            DB::table('master_jenis_biayas')->whereNull('company_id')->update(['company_id' => 1]);

            // Drop old global unique constraint if exists
            try {
                Schema::table('master_jenis_biayas', function (Blueprint $table) {
                    $table->dropUnique('master_jenis_biayas_kode_biaya_unique');
                });
            } catch (\Exception $e) {
                // Ignore if unique name differs or already dropped
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_jenis_biayas')) {
            Schema::table('master_jenis_biayas', function (Blueprint $table) {
                if (Schema::hasColumn('master_jenis_biayas', 'company_id')) {
                    $table->dropForeign(['company_id']);
                    $table->dropColumn('company_id');
                }
            });
        }
    }
};
