<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('goods_issues')) {
            Schema::table('goods_issues', function (Blueprint $table) {
                if (!Schema::hasColumn('goods_issues', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('goods_issues', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('goods_issues')) {
            Schema::table('goods_issues', function (Blueprint $table) {
                if (Schema::hasColumn('goods_issues', 'approved_by')) {
                    $table->dropForeign(['approved_by']);
                    $table->dropColumn('approved_by');
                }
                if (Schema::hasColumn('goods_issues', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }
            });
        }
    }
};
