<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\ChartOfAccount;
use App\Models\PettyCash;

echo "=== DAFTAR KAS & BANK ===\n";
foreach (KasBank::all() as $kb) {
    echo "ID: {$kb->id} | Nama: {$kb->nama_akun} | COA ID: {$kb->coa_id} | Saldo Berjalan: Rp " . number_format($kb->saldo_berjalan, 0, ',', '.') . " | Branch: " . ($kb->branch_id ?? 'Pusat') . "\n";
}

echo "\n=== DAFTAR COA KAS ===\n";
foreach (ChartOfAccount::where('code', 'like', '11%')->get() as $coa) {
    echo "ID: {$coa->id} | Code: {$coa->code} | Name: {$coa->name}\n";
}

echo "\n=== PETTY CASH TRANSACTIONS ===\n";
foreach (PettyCash::all() as $pc) {
    echo "ID: {$pc->id} | No: {$pc->nomor_transaksi} | Tipe: {$pc->tipe} | Nominal: {$pc->nominal} | COA Kas: {$pc->coa_kas_id} | Saldo Sblm: {$pc->saldo_sebelum} | Saldo Ssdh: {$pc->saldo_sesudah}\n";
}
