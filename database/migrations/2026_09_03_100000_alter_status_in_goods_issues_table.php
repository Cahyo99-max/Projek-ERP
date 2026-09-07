<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('goods_issues') && Schema::hasColumn('goods_issues', 'status')) {
            DB::statement("ALTER TABLE `goods_issues` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'Draft'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('goods_issues') && Schema::hasColumn('goods_issues', 'status')) {
            DB::statement("ALTER TABLE `goods_issues` MODIFY COLUMN `status` ENUM('Draft', 'Confirmed') NOT NULL DEFAULT 'Draft'");
        }
    }
};
