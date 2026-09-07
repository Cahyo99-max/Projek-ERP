<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update status column to VARCHAR(30) with default 'Draft'
        DB::statement("ALTER TABLE `boms` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'Draft'");

        // 2. Add approved_by and approved_at columns if not present
        Schema::table('boms', function (Blueprint $table) {
            if (!Schema::hasColumn('boms', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('boms', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            if (Schema::hasColumn('boms', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('boms', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });

        DB::statement("ALTER TABLE `boms` MODIFY COLUMN `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'");
    }
};
