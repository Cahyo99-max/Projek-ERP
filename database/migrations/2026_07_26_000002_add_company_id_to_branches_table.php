<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('restrict');
        });

        // Seed a default company if branches exist or none exists yet
        $companyId = DB::table('companies')->insertGetId([
            'kode_perusahaan' => 'PT-MB',
            'nama_perusahaan' => 'PT. Contoh Maju Bersama',
            'status'          => 'Active',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Link existing branches to the default company
        DB::table('branches')->whereNull('company_id')->update(['company_id' => $companyId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
