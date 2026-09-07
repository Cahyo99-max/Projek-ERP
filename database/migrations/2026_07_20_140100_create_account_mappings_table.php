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
        Schema::create('account_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->comment('Grup Mapping: default, inventory_category, tax, payment_method, freight, expense');
            $table->string('mapping_key')->comment('Key unik: e.g. default_inventory, cat_1_inventory, tax_ppn_in, freight_in');
            $table->foreignId('coa_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->string('description')->nullable()->comment('Keterangan kegunaan mapping');
            $table->timestamps();

            $table->unique(['group', 'mapping_key']);
            $table->index(['group', 'mapping_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_mappings');
    }
};
