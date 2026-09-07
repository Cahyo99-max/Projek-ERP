<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionDetail;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\MasterBarangKonversi;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\User;

echo "========================================================\n";
echo "TEST INTEGRASI PURCHASE REQUEST (PR) MULTI-SATUAN\n";
echo "========================================================\n\n";

DB::beginTransaction();

try {
    // 1. Get or create test masters
    $satuanMeter = MasterSatuan::firstOrCreate(
        ['nama_satuan' => 'Meter TEST'],
        ['kode_satuan' => 'MTR-TST', 'singkatan' => 'm', 'status' => 'Active']
    );

    $satuanRol = MasterSatuan::firstOrCreate(
        ['nama_satuan' => 'Rol TEST'],
        ['kode_satuan' => 'ROL-TST', 'singkatan' => 'rol', 'status' => 'Active']
    );

    $dept = Department::first();
    $karyawan = Karyawan::first();
    $user = User::first();

    if (!$dept || !$karyawan || !$user) {
        throw new Exception("Master Departemen, Karyawan, atau User tidak ditemukan di DB.");
    }

    echo "[PASS] 1. Master Satuan & Dependencies siap: {$satuanMeter->nama_satuan}, {$satuanRol->nama_satuan}\n";

    $kat = \App\Models\MasterKategoriBarang::first();
    $tipeBrg = \App\Models\MasterTipeBarang::first();

    // 2. Create Master Barang with Multi-Satuan
    $barang = MasterBarang::create([
        'kode_barang'        => 'BRG-PR-TEST-' . time(),
        'nama_barang'        => 'Kain Drill PR Test',
        'tipe_item'          => 'Stok',
        'satuan_id'          => $satuanMeter->id,
        'kategori_barang_id' => $kat ? $kat->id : 1,
        'tipe_barang_id'     => $tipeBrg ? $tipeBrg->id : 1,
        'status'             => 'Active',
    ]);

    // Add conversion: 1 Rol = 50 Meter
    $konversi = MasterBarangKonversi::create([
        'barang_id'      => $barang->id,
        'satuan_id'      => $satuanRol->id,
        'faktor_pengali' => 50,
        'is_satuan_beli' => 1,
        'is_satuan_jual' => 1,
    ]);

    echo "[PASS] 2. Master Barang dengan Konversi (1 Rol = 50 Meter) berhasil dibuat (ID: {$barang->id})\n";

    // 3. Create PR with Alternate Unit (10 Rol)
    $nomorPrTest = 'PR/TEST/' . time();
    $pr = PurchaseRequisition::create([
        'company_id'         => $dept->company_id ?? 1,
        'nomor_pr'           => $nomorPrTest,
        'tanggal_pr'         => date('Y-m-d'),
        'tanggal_dibutuhkan' => date('Y-m-d', strtotime('+3 days')),
        'departemen_id'      => $dept->id,
        'diminta_oleh'       => $karyawan->id,
        'keterangan'         => 'Test PR Pengadaan Kain Rol',
        'status'             => 'Pending',
        'dibuat_oleh'        => $user->id,
    ]);

    $detail = $pr->details()->create([
        'barang_id'      => $barang->id,
        'satuan_id'      => $satuanRol->id,
        'jumlah'         => 10,
        'faktor_pengali' => 50,
        'jumlah_dasar'   => 500, // 10 * 50
        'harga_estimasi' => 250000,
        'keterangan'     => 'Untuk produksi seragam',
    ]);

    echo "[PASS] 3. PR Created: {$nomorPrTest} dengan Detail: 10 Rol (Faktor: {$detail->faktor_pengali}, Jumlah Dasar: {$detail->jumlah_dasar} Meter)\n";

    // 4. Assert Relations and Precision
    $freshPr = PurchaseRequisition::with(['details.satuan', 'details.barang.satuan', 'details.barang.konversiSatuan.satuan'])->find($pr->id);
    $freshDetail = $freshPr->details->first();

    assert($freshDetail->satuan->id === $satuanRol->id, "Satuan detail harus cocok dengan satuan Rol");
    assert((float)$freshDetail->jumlah_dasar === 500.0, "Jumlah dasar harus 500 Meter");
    assert((float)$freshDetail->faktor_pengali === 50.0, "Faktor pengali harus 50");
    assert($freshDetail->barang->satuan->id === $satuanMeter->id, "Satuan dasar master barang harus Meter");

    echo "[PASS] 4. Assertion & Relasi Eager-Loading valid (Satuan Transaksi: {$freshDetail->satuan->nama_satuan}, Satuan Dasar: {$freshDetail->barang->satuan->nama_satuan})\n";

    // 5. Test Update PR to Base Unit (50 Meter)
    $freshDetail->update([
        'satuan_id'      => $satuanMeter->id,
        'jumlah'         => 50,
        'faktor_pengali' => 1,
        'jumlah_dasar'   => 50,
    ]);

    $reloadedDetail = PurchaseRequisitionDetail::find($freshDetail->id);
    assert((float)$reloadedDetail->jumlah_dasar === 50.0, "Jumlah dasar setelah update harus 50");
    assert((float)$reloadedDetail->faktor_pengali === 1.0, "Faktor pengali setelah update harus 1");

    echo "[PASS] 5. PR Update ke Satuan Dasar sukses (50 Meter = 50 Meter dasar)\n";

    // Clean rollback so database stays pristine
    DB::rollBack();
    echo "\n========================================================\n";
    echo "SEMUA TEST PR MULTI-SATUAN BERHASIL (PASSED 5/5) & DB ROLLBACK CLEAN!\n";
    echo "========================================================\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
