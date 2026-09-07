<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateStressTestData extends Command
{
    protected $signature = 'generate:stress-test {--count=100000 : Jumlah record dummy yang ingin digenerate (Default: 100,000)}';
    protected $description = 'Generate stress-test dummy data for Master Barang, PR, PO, and Goods Receipts (Penerimaan) using raw bulk inserts';

    public function handle()
    {
        $count = (int) $this->option('count');

        if ($count <= 0) {
            $this->error('Jumlah count harus lebih besar dari 0!');
            return 1;
        }

        $this->warn("=== PERINGATAN STRESS-TEST DATA GENERATOR ===");
        $this->warn("Anda akan menghasilkan {$count} record dummy untuk masing-masing modul.");
        $this->warn("Ini berarti:");
        $this->line("- Barang: " . number_format($count) . " baris");
        $this->line("- PR (Header & Detail): " . number_format($count * 2) . " baris");
        $this->line("- PO (Header & Detail): " . number_format($count * 2) . " baris");
        $this->line("- Penerimaan / GR (Header & Detail): " . number_format($count * 2) . " baris");
        $this->warn("Total baris database yang akan dimasukkan: " . number_format($count * 7) . " baris.");
        
        if ($count >= 1000000) {
            $this->error("PERHATIAN: Menggenerate 1 juta data atau lebih dapat memakan space harddisk puluhan Gigabyte (GB) dan memakan waktu yang cukup lama.");
            if (!$this->confirm('Apakah Anda yakin disk space dan memori server Anda mencukupi?', false)) {
                $this->info('Dibatalkan oleh user.');
                return 0;
            }
        }

        // Disable logs & constraints for massive speedup
        DB::disableQueryLog();
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $this->info("Menyiapkan lookup tables & default IDs...");
        
        // Ensure default records exist so foreign keys do not fail
        $satuanId = DB::table('master_satuan')->value('id') ?: DB::table('master_satuan')->insertGetId([
            'kode_satuan' => 'PCS', 'nama_satuan' => 'Pcs', 'created_at' => now(), 'updated_at' => now()
        ]);
        $brandId = DB::table('master_brand')->value('id') ?: DB::table('master_brand')->insertGetId([
            'kode_brand' => 'GEN', 'nama_brand' => 'Generic', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()
        ]);
        $tipeBarangId = DB::table('master_tipe_barang')->value('id') ?: DB::table('master_tipe_barang')->insertGetId([
            'nama_tipe' => 'Generic Tipe', 'created_at' => now(), 'updated_at' => now()
        ]);
        $kategoriId = DB::table('master_kategori_barang')->value('id') ?: DB::table('master_kategori_barang')->insertGetId([
            'nama_kategori' => 'Generic Kategori', 'created_at' => now(), 'updated_at' => now()
        ]);
        $warnaId = DB::table('master_warna')->value('id') ?: DB::table('master_warna')->insertGetId([
            'nama_warna' => 'Generic Warna', 'created_at' => now(), 'updated_at' => now()
        ]);
        $branchId = DB::table('branches')->value('id') ?: DB::table('branches')->insertGetId([
            'nama_cabang' => 'Demo Branch', 'created_at' => now(), 'updated_at' => now()
        ]);
        $gudangId = DB::table('master_gudang')->value('id') ?: DB::table('master_gudang')->insertGetId([
            'nama_gudang' => 'Demo Warehouse', 'cabang_id' => $branchId, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()
        ]);
        $karyawanId = DB::table('karyawans')->value('id') ?: DB::table('karyawans')->insertGetId([
            'nik' => 'NIK-DEMO', 'nama_karyawan' => 'Employee Demo', 'created_at' => now(), 'updated_at' => now()
        ]);
        $supplierId = DB::table('master_suppliers')->value('id') ?: DB::table('master_suppliers')->insertGetId([
            'kode_supplier' => 'SUP-DEMO', 'nama_supplier' => 'Supplier Demo', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()
        ]);
        $departemenId = DB::table('departments')->value('id') ?: DB::table('departments')->insertGetId([
            'department_code' => 'DEM-DEP', 'department_name' => 'Demo Department', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()
        ]);
        $currencyId = DB::table('master_currencies')->value('id') ?: DB::table('master_currencies')->insertGetId([
            'kode_currency' => 'IDR', 'nama_currency' => 'Rupiah', 'simbol' => 'Rp', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()
        ]);
        $userId = DB::table('users')->value('id');

        // Disable foreign keys checks for MySQL bulk insert speedup
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $startTime = microtime(true);
        $chunkSize = 1000;
        
        // 1. Generate Master Barang
        $this->info("1. Menghasilkan dummy data Master Barang...");
        $barangsGenerated = [];
        $startBarangId = DB::table('master_barang')->max('id') ?: 0;
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        for ($i = 1; $i <= $count; $i += $chunkSize) {
            $batchSize = min($chunkSize, $count - $i + 1);
            $batchData = [];
            
            for ($j = 0; $j < $batchSize; $j++) {
                $idx = $startBarangId + $i + $j;
                $uniqueCode = "BRG-" . str_pad($idx, 8, '0', STR_PAD_LEFT) . "-" . Str::random(6);
                $batchData[] = [
                    'kode_barang' => $uniqueCode,
                    'barcode' => $uniqueCode,
                    'nama_barang' => "Dummy Goods Item Stress Test {$idx}",
                    'tipe_item' => 'Stok',
                    'kategori_barang_id' => $kategoriId,
                    'brand_id' => $brandId,
                    'tipe_barang_id' => $tipeBarangId,
                    'satuan_id' => $satuanId,
                    'warna_id' => $warnaId,
                    'status' => 'Active',
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            DB::table('master_barang')->insert($batchData);
            $progressBar->advance($batchSize);
        }
        $progressBar->finish();
        $this->newLine();
        
        // Fetch generated barang IDs
        $endBarangId = DB::table('master_barang')->max('id');
        $barangIdsRange = range($startBarangId + 1, $endBarangId);
        $barangCount = count($barangIdsRange);

        if ($barangCount === 0) {
            $this->error("Gagal mendapatkan range ID barang yang di-insert!");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return 1;
        }

        // 2. Generate Purchase Requisitions (PR)
        $this->info("2. Menghasilkan dummy data Purchase Requisitions...");
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $startPrId = DB::table('purchase_requisitions')->max('id') ?: 0;

        for ($i = 1; $i <= $count; $i += $chunkSize) {
            $batchSize = min($chunkSize, $count - $i + 1);
            $prBatch = [];
            
            for ($j = 0; $j < $batchSize; $j++) {
                $idx = $startPrId + $i + $j;
                $prBatch[] = [
                    'nomor_pr' => "PR-" . str_pad($idx, 8, '0', STR_PAD_LEFT) . "-" . Str::random(6),
                    'branch_id' => $branchId,
                    'tanggal_pr' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                    'tanggal_dibutuhkan' => now()->addDays(rand(1, 30))->format('Y-m-d'),
                    'departemen_id' => $departemenId,
                    'diminta_oleh' => $karyawanId,
                    'status' => 'Approved',
                    'dibuat_oleh' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('purchase_requisitions')->insert($prBatch);
            $progressBar->advance($batchSize);
        }
        $progressBar->finish();
        $this->newLine();

        $endPrId = DB::table('purchase_requisitions')->max('id');
        $prIdsRange = range($startPrId + 1, $endPrId);

        // Seed PR details
        $this->info("   -> Menyimpan PR detail...");
        $progressBar = $this->output->createProgressBar(count($prIdsRange));
        $progressBar->start();

        for ($i = 0; $i < count($prIdsRange); $i += $chunkSize) {
            $batchSize = min($chunkSize, count($prIdsRange) - $i);
            $details = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $prId = $prIdsRange[$i + $j];
                // Match with a pseudo-random generated barang ID
                $barangId = $barangIdsRange[($i + $j) % $barangCount];
                
                $details[] = [
                    'pr_id' => $prId,
                    'barang_id' => $barangId,
                    'jumlah' => 100.00,
                    'harga_estimasi' => 10000.00,
                    'keterangan' => 'Stress test PR detail row',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('purchase_requisition_details')->insert($details);
            $progressBar->advance($batchSize);
        }
        $progressBar->finish();
        $this->newLine();


        // 3. Generate Purchase Orders (PO)
        $this->info("3. Menghasilkan dummy data Purchase Orders...");
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $startPoId = DB::table('purchase_orders')->max('id') ?: 0;

        for ($i = 1; $i <= $count; $i += $chunkSize) {
            $batchSize = min($chunkSize, $count - $i + 1);
            $poBatch = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $idx = $startPoId + $i + $j;
                $poBatch[] = [
                    'nomor_po' => "PO-" . str_pad($idx, 8, '0', STR_PAD_LEFT) . "-" . Str::random(6),
                    'tanggal_po' => now()->subDays(rand(1, 15))->format('Y-m-d'),
                    'branch_id' => $branchId,
                    'supplier_id' => $supplierId,
                    'currency_id' => $currencyId,
                    'kurs' => 1.00,
                    'subtotal' => 1000000.00,
                    'grand_total' => 1000000.00,
                    'grand_total_idr' => 1000000.00,
                    'status' => 'Confirmed',
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('purchase_orders')->insert($poBatch);
            $progressBar->advance($batchSize);
        }
        $progressBar->finish();
        $this->newLine();

        $endPoId = DB::table('purchase_orders')->max('id');
        $poIdsRange = range($startPoId + 1, $endPoId);

        // Seed PO details
        $this->info("   -> Menyimpan PO detail...");
        $progressBar = $this->output->createProgressBar(count($poIdsRange));
        $progressBar->start();

        $startPoDetailId = DB::table('purchase_order_details')->max('id') ?: 0;

        for ($i = 0; $i < count($poIdsRange); $i += $chunkSize) {
            $batchSize = min($chunkSize, count($poIdsRange) - $i);
            $details = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $poId = $poIdsRange[$i + $j];
                $barangId = $barangIdsRange[($i + $j) % $barangCount];
                
                $details[] = [
                    'po_id' => $poId,
                    'barang_id' => $barangId,
                    'jumlah' => 100.00,
                    'harga_satuan' => 10000.00,
                    'subtotal' => 1000000.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('purchase_order_details')->insert($details);
            $progressBar->advance($batchSize);
        }
        $progressBar->finish();
        $this->newLine();

        $endPoDetailId = DB::table('purchase_order_details')->max('id');
        $poDetailIdsRange = range($startPoDetailId + 1, $endPoDetailId);


        // 4. Generate Goods Receipts (Penerimaan / GR)
        $this->info("4. Menghasilkan dummy data Goods Receipts (Penerimaan)...");
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $startGrId = DB::table('goods_receipts')->max('id') ?: 0;

        for ($i = 1; $i <= $count; $i += $chunkSize) {
            $batchSize = min($chunkSize, $count - $i + 1);
            $grBatch = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $idx = $startGrId + $i + $j;
                $poId = $poIdsRange[($i + $j - 1) % count($poIdsRange)];
                $grBatch[] = [
                    'nomor_gr' => "GR-" . str_pad($idx, 8, '0', STR_PAD_LEFT) . "-" . Str::random(6),
                    'tanggal_gr' => now()->format('Y-m-d'),
                    'po_id' => $poId,
                    'gudang_id' => $gudangId,
                    'nomor_surat_jalan' => "SJ-" . str_pad($idx, 8, '0', STR_PAD_LEFT),
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('goods_receipts')->insert($grBatch);
            $progressBar->advance($batchSize);
        }
        $progressBar->finish();
        $this->newLine();

        $endGrId = DB::table('goods_receipts')->max('id');
        $grIdsRange = range($startGrId + 1, $endGrId);

        // Seed GR details
        $this->info("   -> Menyimpan Penerimaan detail...");
        $progressBar = $this->output->createProgressBar(count($grIdsRange));
        $progressBar->start();

        for ($i = 0; $i < count($grIdsRange); $i += $chunkSize) {
            $batchSize = min($chunkSize, count($grIdsRange) - $i);
            $details = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $grId = $grIdsRange[$i + $j];
                $barangId = $barangIdsRange[($i + $j) % $barangCount];
                $poDetailId = $poDetailIdsRange[($i + $j) % count($poDetailIdsRange)];
                
                $details[] = [
                    'gr_id' => $grId,
                    'po_detail_id' => $poDetailId,
                    'barang_id' => $barangId,
                    'jumlah' => 100.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('goods_receipt_details')->insert($details);
            $progressBar->advance($batchSize);
        }
        $progressBar->finish();
        $this->newLine();


        // Restore foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $duration = round(microtime(true) - $startTime, 2);
        $this->info("=========================================");
        $this->info("BERHASIL! Stress-test data selesai dibuat.");
        $this->info("Total Waktu Eksekusi: {$duration} detik.");
        $this->info("=========================================");
        return 0;
    }
}
