<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_transfers')) {
            DB::statement("ALTER TABLE `warehouse_transfers` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'Draft'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('warehouse_transfers')) {
            DB::statement("ALTER TABLE `warehouse_transfers` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'Draft'");
        }
    }
};
