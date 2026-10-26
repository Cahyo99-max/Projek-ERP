<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jurnals')) {
            Schema::create('jurnals', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_jurnal', 50)->unique();
                $table->date('tanggal_jurnal');
                $table->string('referensi_tipe', 50)->nullable(); // e.g. 'Goods Receipt', 'Purchase Invoice'
                $table->unsignedBigInteger('referensi_id')->nullable();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->decimal('total_debit', 20, 2)->default(0);
                $table->decimal('total_kredit', 20, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['referensi_tipe', 'referensi_id'], 'idx_jurnal_ref');
                $table->index(['branch_id', 'tanggal_jurnal'], 'idx_jurnal_branch_date');
            });
        }

        if (!Schema::hasTable('jurnal_details')) {
            Schema::create('jurnal_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jurnal_id')->constrained('jurnals')->cascadeOnDelete();
                $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
                $table->decimal('debit', 20, 2)->default(0);
                $table->decimal('kredit', 20, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index('jurnal_id', 'idx_jd_jurnal_id');
                $table->index('account_id', 'idx_jd_account_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_details');
        Schema::dropIfExists('jurnals');
    }
};
