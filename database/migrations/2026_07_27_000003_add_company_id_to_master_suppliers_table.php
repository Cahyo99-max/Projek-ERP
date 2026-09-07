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
        Schema::table('master_suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('master_suppliers', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_supplier_company_status');
                $table->index(['company_id', 'kode_supplier'], 'idx_supplier_company_kode');
            }
        });

        // Set default company_id = 1 or active company for existing suppliers
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') 
                            ?? DB::table('companies')->value('id') ?? 1;

        if ($defaultCompanyId) {
            DB::table('master_suppliers')
              ->whereNull('company_id')
              ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('master_suppliers', 'company_id')) {
                try { $table->dropForeign(['company_id']); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_supplier_company_status'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_supplier_company_kode'); } catch (\Exception $e) {}
                $table->dropColumn('company_id');
            }
        });
    }
};
