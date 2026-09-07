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
        if (!Schema::hasTable('master_tipe_pengeluaran')) {
            Schema::create('master_tipe_pengeluaran', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
                $table->string('kode_tipe', 50)->nullable();
                $table->string('nama_tipe', 150);
                $table->text('deskripsi')->nullable();
                $table->foreignId('coa_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
                $table->enum('status', ['Active', 'Inactive'])->default('Active');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tipe_pengeluaran');
    }
};
