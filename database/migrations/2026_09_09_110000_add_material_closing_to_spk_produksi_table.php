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
            if (!Schema::hasColumn('spk_produksi', 'is_material_closed')) {
                $table->boolean('is_material_closed')->default(false)->after('status');
            }
            if (!Schema::hasColumn('spk_produksi', 'material_closed_at')) {
                $table->dateTime('material_closed_at')->nullable()->after('is_material_closed');
            }
            if (!Schema::hasColumn('spk_produksi', 'material_closed_by')) {
                $table->unsignedBigInteger('material_closed_by')->nullable()->after('material_closed_at');
                $table->foreign('material_closed_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_produksi', function (Blueprint $table) {
            if (Schema::hasColumn('spk_produksi', 'material_closed_by')) {
                $table->dropForeign(['material_closed_by']);
                $table->dropColumn('material_closed_by');
            }
            if (Schema::hasColumn('spk_produksi', 'material_closed_at')) {
                $table->dropColumn('material_closed_at');
            }
            if (Schema::hasColumn('spk_produksi', 'is_material_closed')) {
                $table->dropColumn('is_material_closed');
            }
        });
    }
};
