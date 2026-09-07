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
            if (!Schema::hasColumn('chart_of_accounts', 'master_tipe_akun_id')) {
                $table->foreignId('master_tipe_akun_id')->nullable()->after('type')->constrained('master_tipe_akuns')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('chart_of_accounts', 'master_tipe_akun_id')) {
                $table->dropForeign(['master_tipe_akun_id']);
                $table->dropColumn('master_tipe_akun_id');
            }
        });
    }
};
