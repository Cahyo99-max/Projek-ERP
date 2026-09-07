<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify cabang_id column to be nullable in master_gudang
        Schema::table('master_gudang', function (Blueprint $table) {
            $table->unsignedBigInteger('cabang_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('master_gudang', function (Blueprint $table) {
            $table->unsignedBigInteger('cabang_id')->nullable(false)->change();
        });
    }
};
