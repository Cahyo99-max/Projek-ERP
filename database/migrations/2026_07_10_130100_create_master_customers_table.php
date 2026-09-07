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
        Schema::create('master_customers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_customer', 50)->unique();
            $table->string('nama_customer', 200);
            $table->string('nama_perusahaan', 200)->nullable();
            $table->enum('jenis_customer', ['Distributor', 'Agen', 'Retail', 'End User'])->default('Retail');
            $table->string('contact_person', 200)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('hp', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 200)->nullable();
            $table->string('npwp', 100)->nullable();
            $table->boolean('pkp')->default(false);
            $table->string('currency', 10)->nullable();
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->foreignId('sales_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_customers');
    }
};
