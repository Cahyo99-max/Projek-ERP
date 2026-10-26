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
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipts', 'tender_id')) {
                $table->foreignId('tender_id')
                    ->nullable()
                    ->after('po_id')
                    ->constrained('tenders')
                    ->nullOnDelete();

                $table->index('tender_id', 'idx_goods_receipts_tender_id');
            }
        });

        // Backfill existing goods receipts with tender_id from their related purchase orders
        try {
            DB::statement("
                UPDATE goods_receipts gr
                JOIN purchase_orders po ON po.id = gr.po_id
                SET gr.tender_id = po.tender_id
                WHERE gr.tender_id IS NULL AND po.tender_id IS NOT NULL
            ");
        } catch (\Exception $e) {
            // Log or ignore if join is not needed
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipts', 'tender_id')) {
                $table->dropForeign(['tender_id']);
                $table->dropIndex('idx_goods_receipts_tender_id');
                $table->dropColumn('tender_id');
            }
        });
    }
};
