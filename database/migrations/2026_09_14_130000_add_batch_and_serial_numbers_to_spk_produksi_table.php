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
        Schema::table('spk_produksi', function (Blueprint $table) {
            if (!Schema::hasColumn('spk_produksi', 'nomor_batch')) {
                $table->string('nomor_batch', 50)->nullable()->after('satuan_id')->index();
            }
            if (!Schema::hasColumn('spk_produksi', 'serial_numbers')) {
                $table->mediumText('serial_numbers')->nullable()->after('nomor_batch');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_produksi', function (Blueprint $table) {
            if (Schema::hasColumn('spk_produksi', 'serial_numbers')) {
                $table->dropColumn('serial_numbers');
            }
            if (Schema::hasColumn('spk_produksi', 'nomor_batch')) {
                $table->dropColumn('nomor_batch');
            }
        });
    }
};
