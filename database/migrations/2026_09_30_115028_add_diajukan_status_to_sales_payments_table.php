<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE sales_payments MODIFY COLUMN status ENUM('Draft', 'Diajukan', 'Posted', 'Cancelled') NOT NULL DEFAULT 'Draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE sales_payments MODIFY COLUMN status ENUM('Draft', 'Posted', 'Cancelled') NOT NULL DEFAULT 'Draft'");
    }
};
