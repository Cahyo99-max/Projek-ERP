<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Default asset configuration fields removed from master_barang.
     * These values (metode_penyusutan, kondisi, gudang) are now set directly
     * per individual fixed_asset record when registering via PO / fixed assets module.
     *
     * Migration kept as no-op to preserve migration history ordering.
     */
    public function up(): void
    {
        // No-op: fields intentionally removed from master_barang.
        // All asset defaults are handled at the fixed_assets level.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op.
    }
};
