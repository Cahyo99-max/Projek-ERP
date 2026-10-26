<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipts', 'alasan_pembatalan')) {
                $table->text('alasan_pembatalan')->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('goods_receipts', 'canceled_by')) {
                $table->foreignId('canceled_by')->nullable()->after('alasan_pembatalan')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('goods_receipts', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('canceled_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipts', 'canceled_at')) {
                $table->dropColumn('canceled_at');
            }
            if (Schema::hasColumn('goods_receipts', 'canceled_by')) {
                $table->dropForeign(['canceled_by']);
                $table->dropColumn('canceled_by');
            }
            if (Schema::hasColumn('goods_receipts', 'alasan_pembatalan')) {
                $table->dropColumn('alasan_pembatalan');
            }
        });
    }
};
