<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('opening_stocks') && !Schema::hasColumn('opening_stocks', 'company_id')) {
            Schema::table('opening_stocks', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->index(['company_id', 'branch_id', 'status'], 'idx_os_company_branch_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('opening_stocks') && Schema::hasColumn('opening_stocks', 'company_id')) {
            Schema::table('opening_stocks', function (Blueprint $table) {
                $table->dropIndex('idx_os_company_branch_status');
                $table->dropColumn('company_id');
            });
        }
    }
};
