<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIXING PT & CABANG WAREHOUSE SEPARATION ===\n\n";

// 1. Ensure Gudang 1 is set to PT Pusat (cabang_id = null)
DB::table('master_gudang')->where('id', 1)->update([
    'nama_gudang' => 'Gudang Utama (PT Pusat)',
    'cabang_id'   => null,
]);
echo "✓ Updated Gudang 1: Gudang Utama (PT Pusat) -> cabang_id = NULL\n";

// 2. Ensure Gudang 2 exists for Cabang Utama Jakarta (cabang_id = 1)
$gudangCabang = DB::table('master_gudang')->where('id', 2)->first();
if (!$gudangCabang) {
    DB::table('master_gudang')->insert([
        'id'                   => 2,
        'company_id'           => 1,
        'kode_gudang'          => 'GDG-0002',
        'nama_gudang'          => 'Gudang Cabang Jakarta',
        'cabang_id'            => 1,
        'jenis_gudang_id'      => 1,
        'penanggung_jawab_id'  => null,
        'gunakan_lokasi_rak'   => 0,
        'izinkan_stok_negatif' => 0,
        'status'               => 'Active',
        'created_at'           => now(),
        'updated_at'           => now(),
    ]);
    echo "✓ Created Gudang 2: Gudang Cabang Jakarta -> cabang_id = 1\n";
} else {
    DB::table('master_gudang')->where('id', 2)->update([
        'nama_gudang' => 'Gudang Cabang Jakarta',
        'cabang_id'   => 1,
    ]);
    echo "✓ Updated Gudang 2: Gudang Cabang Jakarta -> cabang_id = 1\n";
}

// 3. Move GR 4 (GR/2026/07/0001, Cabang 1) to Gudang 2 (Cabang Gudang)
DB::table('goods_receipts')->where('id', 4)->update(['gudang_id' => 2]);
echo "✓ Updated GR/2026/07/0001 (Cabang) -> gudang_id = 2\n";

// 4. Ensure GR 5 (GR/2026/07/0002, PT) is in Gudang 1 (PT Gudang)
DB::table('goods_receipts')->where('id', 5)->update(['gudang_id' => 1]);
echo "✓ Updated GR/2026/07/0002 (PT) -> gudang_id = 1\n";

// 5. Update stok_gudangs & kartu_stoks for barang 1 (Cardigan, Cabang item) -> gudang_id = 2
DB::table('stok_gudangs')->where('barang_id', 1)->update(['gudang_id' => 2]);
DB::table('kartu_stoks')->where('barang_id', 1)->update(['gudang_id' => 2]);
echo "✓ Moved stock item 'Cardigan Rajut' -> gudang_id = 2 (Cabang Stock)\n";

// 6. Update stok_gudangs & kartu_stoks for barang 5002 (Helm, PT item) -> gudang_id = 1
DB::table('stok_gudangs')->where('barang_id', 5002)->update(['gudang_id' => 1]);
DB::table('kartu_stoks')->where('barang_id', 5002)->update(['gudang_id' => 1]);
echo "✓ Moved stock item 'helm tactical' -> gudang_id = 1 (PT Stock)\n";

echo "\n=== DATA SEPARATION COMPLETE ===\n";
