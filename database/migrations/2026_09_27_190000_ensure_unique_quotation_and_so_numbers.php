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
        // 1. Resolve existing duplicate nomor_so in sales_orders before adding unique constraint
        $duplicates = DB::table('sales_orders')
            ->select('nomor_so')
            ->groupBy('nomor_so')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('nomor_so');

        foreach ($duplicates as $dupNomor) {
            $records = DB::table('sales_orders')
                ->where('nomor_so', $dupNomor)
                ->orderBy('id', 'asc')
                ->get();

            // Keep the first record as is, update subsequent duplicates with a unique sequential suffix
            $i = 1;
            foreach ($records as $record) {
                if ($i > 1) {
                    $newNomor = $dupNomor . '-DUP' . $record->id;
                    DB::table('sales_orders')
                        ->where('id', $record->id)
                        ->update(['nomor_so' => $newNomor]);
                }
                $i++;
            }
        }

        // 2. Update sales_orders: alter length to 50, drop old unique index, add strict unique on nomor_so
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                // Drop old composite index if exists
                // Use try-catch or raw statement to prevent crashing if index name differs
            });

            // Raw drop old index if exists
            try {
                DB::statement("ALTER TABLE `sales_orders` DROP INDEX `so_company_nomor_unique`");
            } catch (\Throwable $e) {
                // Ignore if not exists
            }

            // Alter column length to varchar(50)
            DB::statement("ALTER TABLE `sales_orders` MODIFY `nomor_so` VARCHAR(50) NOT NULL");

            // Add unique index on nomor_so
            try {
                DB::statement("ALTER TABLE `sales_orders` ADD UNIQUE KEY `sales_orders_nomor_so_unique` (`nomor_so`)");
            } catch (\Throwable $e) {
                // Ignore if already exists
            }
        }

        // 3. Update sales_quotations: add unique index on (nomor_quotation, revisi)
        if (Schema::hasTable('sales_quotations')) {
            try {
                DB::statement("ALTER TABLE `sales_quotations` ADD UNIQUE KEY `sales_quotations_nomor_revisi_unique` (`nomor_quotation`, `revisi`)");
            } catch (\Throwable $e) {
                // Ignore if already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_orders')) {
            try {
                DB::statement("ALTER TABLE `sales_orders` DROP INDEX `sales_orders_nomor_so_unique`");
            } catch (\Throwable $e) {
                // Ignore
            }

            try {
                DB::statement("ALTER TABLE `sales_orders` ADD UNIQUE KEY `so_company_nomor_unique` (`company_id`, `nomor_so`, `deleted_at`)");
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (Schema::hasTable('sales_quotations')) {
            try {
                DB::statement("ALTER TABLE `sales_quotations` DROP INDEX `sales_quotations_nomor_revisi_unique`");
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};
