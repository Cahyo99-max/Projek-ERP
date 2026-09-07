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
        if (Schema::hasTable('goods_issues') && Schema::hasColumn('goods_issues', 'tipe_pengeluaran')) {
            DB::statement("ALTER TABLE `goods_issues` MODIFY COLUMN `tipe_pengeluaran` VARCHAR(150) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('goods_issues') && Schema::hasColumn('goods_issues', 'tipe_pengeluaran')) {
            DB::statement("ALTER TABLE `goods_issues` MODIFY COLUMN `tipe_pengeluaran` ENUM('Internal', 'Retur Supplier', 'Penyesuaian', 'Lainnya') NOT NULL");
        }
    }
};
