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
        Schema::table('jurnals', function (Blueprint $table) {
            if (!Schema::hasColumn('jurnals', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('branch_id')->constrained('companies')->nullOnDelete();
                $table->index(['company_id', 'tanggal_jurnal'], 'idx_jurnal_company_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            if (Schema::hasColumn('jurnals', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_jurnal_company_date');
                $table->dropColumn('company_id');
            }
        });
    }
};
