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
        Schema::table('spk_pengeluaran_bahans', function (Blueprint $table) {
            if (!Schema::hasColumn('spk_pengeluaran_bahans', 'penyerah_nama')) {
                $table->string('penyerah_nama', 100)->nullable()->after('penyerah_karyawan_id');
            }
            if (!Schema::hasColumn('spk_pengeluaran_bahans', 'penerima_nama')) {
                $table->string('penerima_nama', 40)->nullable()->after('penerima_karyawan_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_pengeluaran_bahans', function (Blueprint $table) {
            if (Schema::hasColumn('spk_pengeluaran_bahans', 'penyerah_nama')) {
                $table->dropColumn('penyerah_nama');
            }
            if (Schema::hasColumn('spk_pengeluaran_bahans', 'penerima_nama')) {
                $table->dropColumn('penerima_nama');
            }
        });
    }
};
