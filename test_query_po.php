<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pos = App\Models\PurchaseOrder::with(['branch', 'pr.branch'])->get();
foreach ($pos as $p) {
    $poBranch = $p->branch_id ? "Cabang ID {$p->branch_id} ({$p->branch->nama_cabang})" : "NULL (PT Pusat)";
    $prInfo = $p->pr ? "PR #{$p->pr->nomor_pr} (Branch: " . ($p->pr->branch_id ? "Cabang ID {$p->pr->branch_id}" : "PT Pusat") . ")" : "Tanpa PR";
    echo "ID: {$p->id} | PO: {$p->nomor_po} | PO Branch: {$poBranch} | {$prInfo} | Status: {$p->status}\n";
}
