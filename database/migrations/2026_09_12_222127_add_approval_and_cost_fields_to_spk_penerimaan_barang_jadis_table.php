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
        Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
            $table->decimal('biaya_bahan_baku', 18, 4)->default(0)->after('total_nilai_hpp');
            $table->decimal('biaya_tenaga_kerja', 18, 4)->default(0)->after('biaya_bahan_baku');
            $table->decimal('biaya_overhead', 18, 4)->default(0)->after('biaya_tenaga_kerja');
            
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('reject_reason')->nullable()->after('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
            $table->dropColumn([
                'biaya_bahan_baku',
                'biaya_tenaga_kerja',
                'biaya_overhead',
                'approved_by',
                'approved_at',
                'rejected_by',
                'rejected_at',
                'reject_reason',
            ]);
        });
    }
};
