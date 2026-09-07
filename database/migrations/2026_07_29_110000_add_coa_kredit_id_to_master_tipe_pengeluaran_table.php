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
        if (Schema::hasTable('master_tipe_pengeluaran') && !Schema::hasColumn('master_tipe_pengeluaran', 'coa_kredit_id')) {
            Schema::table('master_tipe_pengeluaran', function (Blueprint $table) {
                $table->foreignId('coa_kredit_id')->nullable()->after('coa_id')->constrained('chart_of_accounts')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_tipe_pengeluaran') && Schema::hasColumn('master_tipe_pengeluaran', 'coa_kredit_id')) {
            Schema::table('master_tipe_pengeluaran', function (Blueprint $table) {
                $table->dropForeign(['coa_kredit_id']);
                $table->dropColumn('coa_kredit_id');
            });
        }
    }
};
