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
            if (!Schema::hasColumn('spk_produksi', 'is_hpp_closed')) {
                $table->boolean('is_hpp_closed')->default(false)->after('estimasi_total_biaya');
            }
            if (!Schema::hasColumn('spk_produksi', 'hpp_closed_at')) {
                $table->timestamp('hpp_closed_at')->nullable()->after('is_hpp_closed');
            }
            if (!Schema::hasColumn('spk_produksi', 'hpp_closed_by')) {
                $table->unsignedBigInteger('hpp_closed_by')->nullable()->after('hpp_closed_at');
                $table->foreign('hpp_closed_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_produksi', function (Blueprint $table) {
            if (Schema::hasColumn('spk_produksi', 'hpp_closed_by')) {
                $table->dropForeign(['hpp_closed_by']);
                $table->dropColumn('hpp_closed_by');
            }
            if (Schema::hasColumn('spk_produksi', 'hpp_closed_at')) {
                $table->dropColumn('hpp_closed_at');
            }
            if (Schema::hasColumn('spk_produksi', 'is_hpp_closed')) {
                $table->dropColumn('is_hpp_closed');
            }
        });
    }
};
