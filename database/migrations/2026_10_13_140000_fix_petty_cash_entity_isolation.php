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
        // Pastikan isolasi ketat pada data Petty Cash:
        // 1. Jika Cabang -> branch_id terisi, company_id WAJIB NULL
        DB::table('petty_cashes')
            ->whereNotNull('branch_id')
            ->update([
                'company_id'   => null,
                'tipe_entitas' => 'Cabang',
            ]);

        // 2. Jika PT Pusat -> company_id terisi, branch_id WAJIB NULL
        DB::table('petty_cashes')
            ->whereNull('branch_id')
            ->update([
                'branch_id'    => null,
                'tipe_entitas' => 'PT',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
