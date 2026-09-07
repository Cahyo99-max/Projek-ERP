<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\PettyCash;
use Illuminate\Support\Facades\Auth;

$user = User::first();
Auth::login($user);

$controller = new \App\Http\Controllers\PettyCashController();

// 1. Test Session PT Pusat
session([
    'active_company_id'   => 1,
    'active_company_name' => 'PT Persada Aman Sentosa',
    'active_branch_id'    => null,
]);

$reqPt = new \Illuminate\Http\Request();
$resPt = $controller->index($reqPt);
$dataPt = $resPt->getData();
echo "1. Session PT Pusat:\n";
echo "   Entity: {$dataPt['activeEntityName']}\n";
echo "   Saldo Kas Kecil: Rp " . number_format($dataPt['saldoKasKecil'], 0, ',', '.') . "\n";
echo "   Transactions: {$dataPt['transactions']->total()} Data\n";

// 2. Test Session Cabang Limo (ID: 1)
session([
    'active_company_id'   => 1,
    'active_company_name' => 'PT Persada Aman Sentosa',
    'active_branch_id'    => 1,
    'active_branch_name'  => 'Cabang Limo',
]);

$reqCbg = new \Illuminate\Http\Request();
$resCbg = $controller->index($reqCbg);
$dataCbg = $resCbg->getData();
echo "\n2. Session Cabang Limo:\n";
echo "   Entity: {$dataCbg['activeEntityName']}\n";
echo "   Saldo Kas Kecil: Rp " . number_format($dataCbg['saldoKasKecil'], 0, ',', '.') . "\n";
echo "   Transactions: {$dataCbg['transactions']->total()} Data\n";

// 3. Test Detail
$sample = PettyCash::first();
if ($sample) {
    $resDetail = $controller->show($sample);
    $dataDetail = json_decode($resDetail->getContent(), true);
    echo "\n3. Show Detail ID {$sample->id} -> Status: " . ($dataDetail['success'] ? 'SUCCESS' : 'FAILED') . "\n";
}

echo "\n=== ALL SIMPLIFIED SESSION TESTS PASSED! ===\n";
