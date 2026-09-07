<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function hasIndex(string $table, string $name): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$name]);
        return count($indexes) > 0;
    }

    public function up(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            if (!$this->hasIndex('boms', 'idx_boms_7m_created')) {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'created_at'], 'idx_boms_7m_created');
            }
        });
    }

    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            if ($this->hasIndex('boms', 'idx_boms_7m_created')) {
                $table->dropIndex('idx_boms_7m_created');
            }
        });
    }
};
