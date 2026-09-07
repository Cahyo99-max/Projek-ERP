<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\PettyCash;
use App\Models\ChartOfAccount;

echo "=== INITIAL BALANCES ===\n";
$kasKecil = KasBank::find(1);
echo "KasBank (ID: 1 - {$kasKecil->nama_akun}): Rp " . number_format($kasKecil->saldo_berjalan, 0, ',', '.') . "\n";

// Recalculate based on existing petty cash
$saldo = (float) $kasKecil->saldo_awal;
echo "Saldo Awal: Rp " . number_format($saldo, 0, ',', '.') . "\n";

foreach (PettyCash::orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get() as $pc) {
    $before = $saldo;
    if ($pc->tipe === 'Pemasukan') {
        $saldo += (float) $pc->nominal;
    } else {
        $saldo -= (float) $pc->nominal;
    }
    $pc->saldo_sebelum = $before;
    $pc->saldo_sesudah = $saldo;
    $pc->save();
    echo "Trx {$pc->nomor_transaksi} ({$pc->tipe} {$pc->nominal}): Sblm Rp " . number_format($before, 0, ',', '.') . " -> Ssdh Rp " . number_format($saldo, 0, ',', '.') . "\n";
}

$kasKecil->saldo_berjalan = $saldo;
$kasKecil->save();

echo "\nUpdated KasBank ID 1 Saldo Berjalan: Rp " . number_format($kasKecil->saldo_berjalan, 0, ',', '.') . "\n";
