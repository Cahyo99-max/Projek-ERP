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
        Schema::table('kas_mutasis', function (Blueprint $table) {
            if (!Schema::hasColumn('kas_mutasis', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('created_by');
                $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('kas_mutasis', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_mutasis', function (Blueprint $table) {
            if (Schema::hasColumn('kas_mutasis', 'cancelled_by')) {
                $table->dropForeign(['cancelled_by']);
                $table->dropColumn('cancelled_by');
            }
            if (Schema::hasColumn('kas_mutasis', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
        });
    }
};
