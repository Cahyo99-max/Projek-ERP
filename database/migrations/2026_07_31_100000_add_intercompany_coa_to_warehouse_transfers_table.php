<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_transfers')) {
            Schema::table('warehouse_transfers', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouse_transfers', 'jenis_transfer')) {
                    $table->enum('jenis_transfer', ['Intra-PT', 'Inter-PT'])->default('Intra-PT')->after('status');
                }
                if (!Schema::hasColumn('warehouse_transfers', 'coa_debit_id')) {
                    $table->unsignedBigInteger('coa_debit_id')->nullable()->after('jenis_transfer');
                    $table->foreign('coa_debit_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
                }
                if (!Schema::hasColumn('warehouse_transfers', 'coa_kredit_id')) {
                    $table->unsignedBigInteger('coa_kredit_id')->nullable()->after('coa_debit_id');
                    $table->foreign('coa_kredit_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('warehouse_transfers')) {
            Schema::table('warehouse_transfers', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_transfers', 'coa_debit_id')) {
                    try { $table->dropForeign(['coa_debit_id']); } catch (\Exception $e) {}
                    $table->dropColumn('coa_debit_id');
                }
                if (Schema::hasColumn('warehouse_transfers', 'coa_kredit_id')) {
                    try { $table->dropForeign(['coa_kredit_id']); } catch (\Exception $e) {}
                    $table->dropColumn('coa_kredit_id');
                }
                if (Schema::hasColumn('warehouse_transfers', 'jenis_transfer')) {
                    $table->dropColumn('jenis_transfer');
                }
            });
        }
    }
};
