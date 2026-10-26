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
        Schema::table('master_tahapan_produksi', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'bom_id', 'urutan'], 'idx_tahapan_perf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_tahapan_produksi', function (Blueprint $table) {
            $table->dropIndex('idx_tahapan_perf');
        });
    }
};
