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
        // Columns re-enabled for real-time HPP costing system
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
            $table->decimal('hpp_satuan', 18, 4)->default(0)->after('jumlah_reject');
            $table->decimal('total_nilai_hpp', 18, 4)->default(0)->after('hpp_satuan');
        });
    }
};
