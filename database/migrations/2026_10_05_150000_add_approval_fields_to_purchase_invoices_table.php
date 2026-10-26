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
        DB::statement("ALTER TABLE purchase_invoices MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Draft'");

        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('updated_by');
            }
            if (!Schema::hasColumn('purchase_invoices', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('purchase_invoices', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'alasan_penolakan')) {
                $table->dropColumn('alasan_penolakan');
            }
            if (Schema::hasColumn('purchase_invoices', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('purchase_invoices', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
        });
    }
};
