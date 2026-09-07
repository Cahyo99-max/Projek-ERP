<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_opnames', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
            if (!Schema::hasColumn('stock_opnames', 'jenis_opname')) {
                $table->string('jenis_opname', 30)->default('Full')->after('tanggal_selesai');
            }
            if (!Schema::hasColumn('stock_opnames', 'alasan_reject')) {
                $table->text('alasan_reject')->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('stock_opnames', 'diclosing_oleh')) {
                $table->foreignId('diclosing_oleh')->nullable()->after('disetujui_pada')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_opnames', 'diclosing_pada')) {
                $table->timestamp('diclosing_pada')->nullable()->after('diclosing_oleh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['diclosing_oleh']);
            $table->dropColumn(['tanggal_selesai', 'jenis_opname', 'alasan_reject', 'diclosing_oleh', 'diclosing_pada']);
        });
    }
};
