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
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('chart_of_accounts', 'normal_balance')) {
                $table->enum('normal_balance', ['debit', 'credit'])->default('debit')->after('type')->comment('Saldo Normal: Debit atau Kredit');
            }
            if (!Schema::hasColumn('chart_of_accounts', 'level')) {
                $table->integer('level')->default(1)->after('is_header')->comment('Tingkat/Level Hierarki COA (1, 2, 3)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('chart_of_accounts', 'normal_balance')) {
                $table->dropColumn('normal_balance');
            }
            if (Schema::hasColumn('chart_of_accounts', 'level')) {
                $table->dropColumn('level');
            }
        });
    }
};
