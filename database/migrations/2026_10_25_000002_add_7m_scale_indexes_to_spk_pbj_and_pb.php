<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $name): bool
    {
        try {
            $conn = Schema::getConnection();
            $dbName = $conn->getDatabaseName();
            $result = $conn->select(
                "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$dbName, $table, $name]
            );
            return ($result[0]->cnt ?? 0) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function up(): void
    {
        if (Schema::hasTable('spk_penerimaan_barang_jadis')) {
            Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
                if (!$this->hasIndex('spk_penerimaan_barang_jadis', 'idx_pbj_7m_comp_branch_tgl_id')) {
                    $table->index(['company_id', 'branch_id', 'deleted_at', 'tanggal_penerimaan', 'id'], 'idx_pbj_7m_comp_branch_tgl_id');
                }
            });
        }

        if (Schema::hasTable('spk_pengeluaran_bahans')) {
            Schema::table('spk_pengeluaran_bahans', function (Blueprint $table) {
                if (!$this->hasIndex('spk_pengeluaran_bahans', 'idx_pb_7m_comp_branch_tgl_id')) {
                    $table->index(['company_id', 'branch_id', 'deleted_at', 'tanggal_pengeluaran', 'id'], 'idx_pb_7m_comp_branch_tgl_id');
                }
            });
        }

        // Additional extreme scale index on spk_produksi for target_selesai & updated_at
        if (Schema::hasTable('spk_produksi')) {
            Schema::table('spk_produksi', function (Blueprint $table) {
                if (!$this->hasIndex('spk_produksi', 'idx_spk_7m_comp_branch_tgt_stat')) {
                    $table->index(['company_id', 'branch_id', 'deleted_at', 'status', 'target_selesai'], 'idx_spk_7m_comp_branch_tgt_stat');
                }
                if (!$this->hasIndex('spk_produksi', 'idx_spk_7m_comp_branch_upd_stat')) {
                    $table->index(['company_id', 'branch_id', 'deleted_at', 'status', 'updated_at'], 'idx_spk_7m_comp_branch_upd_stat');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('spk_penerimaan_barang_jadis')) {
            Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
                if ($this->hasIndex('spk_penerimaan_barang_jadis', 'idx_pbj_7m_comp_branch_tgl_id')) {
                    $table->dropIndex('idx_pbj_7m_comp_branch_tgl_id');
                }
            });
        }

        if (Schema::hasTable('spk_pengeluaran_bahans')) {
            Schema::table('spk_pengeluaran_bahans', function (Blueprint $table) {
                if ($this->hasIndex('spk_pengeluaran_bahans', 'idx_pb_7m_comp_branch_tgl_id')) {
                    $table->dropIndex('idx_pb_7m_comp_branch_tgl_id');
                }
            });
        }

        if (Schema::hasTable('spk_produksi')) {
            Schema::table('spk_produksi', function (Blueprint $table) {
                if ($this->hasIndex('spk_produksi', 'idx_spk_7m_comp_branch_tgt_stat')) {
                    $table->dropIndex('idx_spk_7m_comp_branch_tgt_stat');
                }
                if ($this->hasIndex('spk_produksi', 'idx_spk_7m_comp_branch_upd_stat')) {
                    $table->dropIndex('idx_spk_7m_comp_branch_upd_stat');
                }
            });
        }
    }
};
