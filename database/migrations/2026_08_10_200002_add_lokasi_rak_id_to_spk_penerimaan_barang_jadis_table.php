<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for adding lokasi_rak_id to spk_penerimaan_barang_jadis.
     */
    public function up(): void
    {
        Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
            $table->foreignId('lokasi_rak_id')->nullable()->after('gudang_id')->constrained('master_lokasi_rak')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
            $table->dropForeign(['lokasi_rak_id']);
            $table->dropColumn('lokasi_rak_id');
        });
    }
};
