<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── user_activity_logs ─────────────────────────────────────────────
        // The dashboard query: WHERE activity='Login' AND status='Failed' AND created_at >= ?
        // The AJAX endpoint: ORDER BY created_at DESC LIMIT 10
        // Single created_at index exists but no composite for activity+status+created_at
        if (!$this->indexExists('user_activity_logs', 'ual_activity_status_created_idx')) {
            Schema::table('user_activity_logs', function (Blueprint $table) {
                // Composite covering index for welcome-card failed login count
                $table->index(['activity', 'status', 'created_at'], 'ual_activity_status_created_idx');
            });
        }

        // ── users ──────────────────────────────────────────────────────────
        // active-users AJAX: WHERE status='Aktif' — full table scan without status index
        if (!$this->indexExists('users', 'users_status_idx')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('status', 'users_status_idx');
            });
        }

        // ── purchase_orders ────────────────────────────────────────────────
        // Popular suppliers query: WHERE branch_id=? GROUP BY supplier_id
        // po_dashboard_perf_idx covers (branch_id, status, tanggal_po)
        // but supplier_id grouping still needs its own covering index
        if (!$this->indexExists('purchase_orders', 'po_branch_supplier_idx')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->index(['branch_id', 'supplier_id'], 'po_branch_supplier_idx');
            });
        }

        // ── tenders ────────────────────────────────────────────────────────
        // Welcome card query: WHERE status='Pending'
        if (!$this->indexExists('tenders', 'tenders_status_idx')) {
            try {
                Schema::table('tenders', function (Blueprint $table) {
                    $table->index('status', 'tenders_status_idx');
                });
            } catch (\Exception $e) {
                // table may not exist in all environments
            }
        }
    }

    public function down(): void
    {
        Schema::table('user_activity_logs', function (Blueprint $table) {
            $table->dropIndexIfExists('ual_activity_status_created_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('users_status_idx');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndexIfExists('po_branch_supplier_idx');
        });
        try {
            Schema::table('tenders', function (Blueprint $table) {
                $table->dropIndexIfExists('tenders_status_idx');
            });
        } catch (\Exception $e) {}
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list(`{$table}`)");
            foreach ($indexes as $index) {
                if ($index->name === $indexName) {
                    return true;
                }
            }
            return false;
        }

        if ($driver === 'mysql') {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        }

        return false;
    }
};
