<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan soft delete dan deleted_by ke tabel existing
     * agar konsisten dengan standar ERP Enterprise.
     */
    public function up(): void
    {
        // === USERS ===
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('deleted_by')->nullable()->after('updated_by')
                  ->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('deleted_by');
        });

        // === DEPARTMENTS ===
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('deleted_by')->nullable()->after('updated_by')
                  ->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('deleted_by');
        });

        // === POSITIONS ===
        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('deleted_by')->nullable()->after('updated_by')
                  ->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('deleted_by');
        });

        // === BRANCHES ===
        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('deleted_by')->nullable()->after('updated_by')
                  ->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deleted_at']);
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deleted_at']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deleted_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deleted_at']);
        });
    }
};
