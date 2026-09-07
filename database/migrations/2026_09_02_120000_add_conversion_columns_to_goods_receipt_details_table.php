<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('goods_receipt_details', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipt_details', 'satuan_id')) {
                $table->foreignId('satuan_id')
                    ->nullable()
                    ->after('barang_id')
                    ->constrained('master_satuan')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('goods_receipt_details', 'faktor_pengali')) {
                $table->decimal('faktor_pengali', 15, 4)
                    ->default(1.0000)
                    ->after('satuan_id');
            }

            if (!Schema::hasColumn('goods_receipt_details', 'jumlah_dasar')) {
                $table->decimal('jumlah_dasar', 15, 4)
                    ->default(0.0000)
                    ->after('jumlah');
            }

            if (!Schema::hasColumn('goods_receipt_details', 'jumlah_dasar_reject')) {
                $table->decimal('jumlah_dasar_reject', 15, 4)
                    ->default(0.0000)
                    ->after('jumlah_reject');
            }
        });

        // Backfill existing records
        $details = DB::table('goods_receipt_details')->get();
        foreach ($details as $d) {
            $poDetail = null;
            if ($d->po_detail_id) {
                $poDetail = DB::table('purchase_order_details')->where('id', $d->po_detail_id)->first();
            }

            $barang = null;
            if ($d->barang_id) {
                $barang = DB::table('master_barang')->where('id', $d->barang_id)->first();
            }

            $satuanId = $poDetail ? $poDetail->satuan_id : ($barang ? $barang->satuan_id : null);
            $faktor = $poDetail && floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
            $jumlahDasar = floatval($d->jumlah) * $faktor;
            $rejectDasar = floatval($d->jumlah_reject) * $faktor;

            DB::table('goods_receipt_details')
                ->where('id', $d->id)
                ->update([
                    'satuan_id'           => $satuanId,
                    'faktor_pengali'      => $faktor,
                    'jumlah_dasar'        => $jumlahDasar,
                    'jumlah_dasar_reject' => $rejectDasar,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipt_details', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipt_details', 'satuan_id')) {
                $table->dropForeign(['satuan_id']);
                $table->dropColumn('satuan_id');
            }
            if (Schema::hasColumn('goods_receipt_details', 'faktor_pengali')) {
                $table->dropColumn('faktor_pengali');
            }
            if (Schema::hasColumn('goods_receipt_details', 'jumlah_dasar')) {
                $table->dropColumn('jumlah_dasar');
            }
            if (Schema::hasColumn('goods_receipt_details', 'jumlah_dasar_reject')) {
                $table->dropColumn('jumlah_dasar_reject');
            }
        });
    }
};
