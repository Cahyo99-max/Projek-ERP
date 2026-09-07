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
        Schema::table('spk_penerimaan_barang_jadi_details', function (Blueprint $table) {
            if (Schema::hasColumn('spk_penerimaan_barang_jadi_details', 'hpp_satuan')) {
                $table->dropColumn('hpp_satuan');
            }
            if (Schema::hasColumn('spk_penerimaan_barang_jadi_details', 'total_nilai_hpp')) {
                $table->dropColumn('total_nilai_hpp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_penerimaan_barang_jadi_details', function (Blueprint $table) {
            if (!Schema::hasColumn('spk_penerimaan_barang_jadi_details', 'hpp_satuan')) {
                $table->decimal('hpp_satuan', 15, 4)->default(0)->after('jumlah_reject');
            }
            if (!Schema::hasColumn('spk_penerimaan_barang_jadi_details', 'total_nilai_hpp')) {
                $table->decimal('total_nilai_hpp', 15, 4)->default(0)->after('hpp_satuan');
            }
        });
    }
};
