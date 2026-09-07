<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (!Schema::hasColumn('positions', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'department_id', 'status'], 'idx_pos_company_dept_status');
            }

            try {
                $table->dropUnique('positions_position_code_unique');
            } catch (\Exception $e) {}
        });

        // Set default company_id for existing positions (inherit from their department's company_id)
        $positions = DB::table('positions')->get();
        foreach ($positions as $pos) {
            $deptCompanyId = DB::table('departments')->where('id', $pos->department_id)->value('company_id');
            if ($deptCompanyId) {
                DB::table('positions')->where('id', $pos->id)->update(['company_id' => $deptCompanyId]);
            } else {
                $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id')
                                    ?? DB::table('companies')->value('id');
                if ($defaultCompanyId) {
                    DB::table('positions')->where('id', $pos->id)->update(['company_id' => $defaultCompanyId]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (Schema::hasColumn('positions', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_pos_company_dept_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
