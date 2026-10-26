<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_invoice_details')) {
            Schema::table('purchase_invoice_details', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_invoice_details', 'po_detail_id')) {
                    $table->foreignId('po_detail_id')
                        ->nullable()
                        ->after('gr_detail_id')
                        ->constrained('purchase_order_details')
                        ->nullOnDelete();
                    
                    $table->index('po_detail_id', 'idx_pid_po_detail_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_invoice_details')) {
            Schema::table('purchase_invoice_details', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_invoice_details', 'po_detail_id')) {
                    $table->dropForeign(['po_detail_id']);
                    $table->dropIndex('idx_pid_po_detail_id');
                    $table->dropColumn('po_detail_id');
                }
            });
        }
    }
};
