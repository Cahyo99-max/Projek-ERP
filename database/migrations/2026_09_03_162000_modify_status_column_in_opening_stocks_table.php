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
        if (Schema::hasTable('opening_stocks')) {
            DB::statement("ALTER TABLE opening_stocks MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('opening_stocks')) {
            DB::statement("ALTER TABLE opening_stocks MODIFY COLUMN status ENUM('Draft', 'Confirmed') NOT NULL DEFAULT 'Draft'");
        }
    }
};
