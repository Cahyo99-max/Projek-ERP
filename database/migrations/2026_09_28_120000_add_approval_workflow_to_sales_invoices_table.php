<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Draft'");

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('alasan_penolakan')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'alasan_penolakan']);
        });

        DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN status ENUM('Draft', 'Unpaid', 'Partial', 'Paid', 'Cancelled') NOT NULL DEFAULT 'Draft'");
    }
};
