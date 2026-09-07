<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$allOk = true;

// ─── 1. MIGRATION CHECK ──────────────────────────────────────────────────────
echo "=== DB COLUMNS (company_id) ===\n";
$tables = [
    'payment_methods', 'payment_terms', 'master_currencies', 'master_pajaks', 'chart_of_accounts', 'account_mappings', 'master_jenis_biayas', 'karyawans',
    'positions', 'departments', 'master_lokasi_rak', 'master_gudang', 'master_jenis_gudang',
    'master_warna', 'master_tipe_barang', 'master_brand', 'master_satuan',
    'master_kategori_barang', 'master_barang',
];
foreach ($tables as $t) {
    $ok = Schema::hasColumn($t, 'company_id');
    echo "  {$t}.company_id: " . ($ok ? '✓ OK' : '✗ MISSING') . "\n";
    if (!$ok) $allOk = false;
}

// ─── 2. MODEL SCOPE + FILLABLE CHECK ─────────────────────────────────────────
echo "\n=== MODEL SCOPES & FILLABLE ===\n";
$models = [
    'App\Models\PaymentMethod',
    'App\Models\PaymentTerm',
    'App\Models\MasterCurrency',
    'App\Models\MasterPajak',
    'App\Models\ChartOfAccount',
    'App\Models\AccountMapping',
    'App\Models\MasterJenisBiaya',
    'App\Models\Karyawan',
    'App\Models\Position',
    'App\Models\Department',
    'App\Models\MasterLokasiRak',
    'App\Models\MasterGudang',
    'App\Models\MasterJenisGudang',
    'App\Models\MasterWarna',
    'App\Models\MasterTipeBarang',
    'App\Models\MasterBrand',
    'App\Models\MasterSatuan',
    'App\Models\MasterKategoriBarang',
    'App\Models\MasterBarang',
];
foreach ($models as $m) {
    $obj = new $m;
    $hasScope    = method_exists($m, 'scopeForActiveCompany');
    $hasFillable = in_array('company_id', $obj->getFillable());
    $line = "  " . class_basename($m)
        . " → scope: " . ($hasScope    ? '✓' : '✗ MISSING')
        . " | fillable: " . ($hasFillable ? '✓' : '✗ MISSING');
    echo $line . "\n";
    if (!$hasScope || !$hasFillable) $allOk = false;
}

// ─── 3. RECORD COUNTS ────────────────────────────────────────────────────────
echo "\n=== RECORD COUNTS ===\n";
$countTables = ['payment_methods', 'payment_terms', 'master_currencies', 'master_pajaks', 'chart_of_accounts', 'account_mappings', 'master_jenis_biayas', 'karyawans', 'positions', 'departments', 'master_barang', 'master_gudang'];
foreach ($countTables as $t) {
    $q = DB::table($t);
    if (Schema::hasColumn($t, 'deleted_at')) $q->whereNull('deleted_at');
    echo "  {$t}: " . $q->count() . " rows\n";
}

// ─── 4. NULL company_id CHECK ─────────────────────────────────────────────────
echo "\n=== NULL company_id ROWS (active records) ===\n";
$nullTables = ['payment_methods', 'payment_terms', 'master_currencies', 'master_pajaks', 'chart_of_accounts', 'account_mappings', 'master_jenis_biayas', 'karyawans', 'positions', 'departments', 'master_lokasi_rak', 'master_gudang', 'master_barang'];
foreach ($nullTables as $t) {
    $hasDel = Schema::hasColumn($t, 'deleted_at');
    $q = DB::table($t)->whereNull('company_id');
    if ($hasDel) $q->whereNull('deleted_at');
    $nullCount = $q->count();
    $status = $nullCount === 0 ? '✓ OK' : "⚠  {$nullCount} rows without company_id";
    echo "  {$t}: {$status}\n";
    if ($nullCount > 0) $allOk = false;
}

// ─── FINAL RESULT ─────────────────────────────────────────────────────────────
echo "\n" . str_repeat('─', 55) . "\n";
echo $allOk
    ? "✅  SEMUA CHECK PASSED — Sistem stabil & aman!\n"
    : "❌  ADA MASALAH — Cek output di atas!\n";
