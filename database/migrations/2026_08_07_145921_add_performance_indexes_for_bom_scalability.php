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
            if (!$this->hasIndex('boms', 'idx_boms_7m_filter')) {
                $table->index(['company_id', 'deleted_at', 'status', 'tipe_bom'], 'idx_boms_7m_filter');
            }
            if (!$this->hasIndex('boms', 'idx_boms_7m_kode')) {
                $table->index(['company_id', 'deleted_at', 'kode_bom'], 'idx_boms_7m_kode');
            }
            if (!$this->hasIndex('boms', 'idx_boms_7m_hpp')) {
                $table->index(['company_id', 'deleted_at', 'total_estimasi_hpp'], 'idx_boms_7m_hpp');
            }
            if (!$this->hasIndex('boms', 'idx_boms_7m_created')) {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'created_at'], 'idx_boms_7m_created');
            }
        });

        Schema::table('bom_items', function (Blueprint $table) {
            if (!$this->hasIndex('bom_items', 'idx_bom_items_7m_lookup')) {
                $table->index(['bom_id', 'barang_mentah_id'], 'idx_bom_items_7m_lookup');
            }
        });

        Schema::table('master_barang', function (Blueprint $table) {
            if (!$this->hasIndex('master_barang', 'idx_mb_7m_lookup')) {
                $table->index(['company_id', 'status', 'tipe_item', 'deleted_at'], 'idx_mb_7m_lookup');
            }
        });
    }

    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            if ($this->hasIndex('boms', 'idx_boms_7m_filter')) $table->dropIndex('idx_boms_7m_filter');
            if ($this->hasIndex('boms', 'idx_boms_7m_kode')) $table->dropIndex('idx_boms_7m_kode');
            if ($this->hasIndex('boms', 'idx_boms_7m_hpp')) $table->dropIndex('idx_boms_7m_hpp');
            if ($this->hasIndex('boms', 'idx_boms_7m_created')) $table->dropIndex('idx_boms_7m_created');
        });

        Schema::table('bom_items', function (Blueprint $table) {
            if ($this->hasIndex('bom_items', 'idx_bom_items_7m_lookup')) $table->dropIndex('idx_bom_items_7m_lookup');
        });

        Schema::table('master_barang', function (Blueprint $table) {
            if ($this->hasIndex('master_barang', 'idx_mb_7m_lookup')) $table->dropIndex('idx_mb_7m_lookup');
        });
    }
};
