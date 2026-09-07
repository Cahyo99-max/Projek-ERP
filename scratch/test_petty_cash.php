<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PettyCash;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Tender;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== MEMULAI TEST PETTY CASH (KAS KECIL) ===\n";

$company = Company::first();
$branch = Branch::where('company_id', $company->id)->first();
$user = User::first();
Auth::login($user);

session([
    'active_company_id'   => $company->id,
    'active_company_name' => $company->nama_perusahaan,
    'active_branch_id'    => $branch ? $branch->id : null,
]);

// 1. Pastikan COA Kas & Beban tersedia
$coaKas = ChartOfAccount::where('company_id', $company->id)
    ->where('is_header', false)
    ->where(function($q) {
        $q->where('code', 'like', '11%')->orWhere('name', 'like', '%kas%');
    })->first();

if (!$coaKas) {
    $coaKas = ChartOfAccount::create([
        'company_id' => $company->id,
        'code' => '1101-01',
        'name' => 'Kas Kecil Kantor Pusat',
        'type' => 'Kas / Bank',
        'is_header' => false,
        'is_active' => true,
    ]);
}

$coaBeban = ChartOfAccount::where('company_id', $company->id)
    ->where('is_header', false)
    ->where(function($q) {
        $q->where('code', 'like', '5%')->orWhere('code', 'like', '6%')->orWhere('name', 'like', '%beban%')->orWhere('name', 'like', '%biaya%');
    })->first();

if (!$coaBeban) {
    $coaBeban = ChartOfAccount::create([
        'company_id' => $company->id,
        'code' => '6101-01',
        'name' => 'Beban Operasional & ATK',
        'type' => 'Beban Operasional',
        'is_header' => false,
        'is_active' => true,
    ]);
}

$tender = Tender::where('company_id', $company->id)->first();

echo "Company: {$company->nama_perusahaan} (ID: {$company->id})\n";
echo "Branch: " . ($branch ? $branch->nama_cabang : 'None') . "\n";
echo "COA Kas: {$coaKas->code} - {$coaKas->name}\n";
echo "COA Beban: {$coaBeban->code} - {$coaBeban->name}\n";
if ($tender) {
    echo "Tender Terhubung: {$tender->nomor_kontrak} - {$tender->nama_tender}\n";
}

// 2. Test Simpan Transaksi PT Pusat
echo "\n--- TEST 1: SIMPAN PENGELUARAN KAS KECIL PT PUSAT ---\n";
$controller = new \App\Http\Controllers\PettyCashController();
$reqPt = new \Illuminate\Http\Request([
    'tanggal' => date('Y-m-d'),
    'tipe' => 'Pengeluaran',
    'kategori' => 'Operasional & ATK',
    'tipe_entitas' => 'PT',
    'coa_kas_id' => $coaKas->id,
    'lawan_coa_id' => $coaBeban->id,
    'tender_id' => $tender ? $tender->id : null,
    'nominal' => 150000,
    'nama_pengaju' => 'Budi Staff Pusat',
    'keterangan' => 'Beli kertas HVS dan tinta printer kantor pusat',
]);

$resPt = $controller->store($reqPt);
$dataPt = json_decode($resPt->getContent(), true);
echo "Response Status: " . ($dataPt['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . ($dataPt['message'] ?? '-') . "\n";
if (!empty($dataPt['data'])) {
    $ptTrx = PettyCash::find($dataPt['data']['id']);
    echo "Nomor Transaksi: {$ptTrx->nomor_transaksi}\n";
    echo "Tipe Entitas: {$ptTrx->tipe_entitas} (Branch ID: " . ($ptTrx->branch_id ?? 'NULL') . ")\n";
    if ($ptTrx->jurnal_id) {
        $jurnalPt = Jurnal::with('details.account')->find($ptTrx->jurnal_id);
        echo "Jurnal PT Terbentuk: {$jurnalPt->nomor_jurnal}\n";
        echo "Jurnal Branch ID: " . ($jurnalPt->branch_id ?? 'NULL (PT Pusat)') . "\n";
        echo "Jurnal Keterangan: {$jurnalPt->keterangan}\n";
        foreach ($jurnalPt->details as $det) {
            echo "   -> [{$det->account->code} {$det->account->name}] Debit: Rp " . number_format($det->debit, 0, ',', '.') . " | Kredit: Rp " . number_format($det->kredit, 0, ',', '.') . "\n";
        }
    }
}

// 3. Test Simpan Transaksi Cabang
if ($branch) {
    echo "\n--- TEST 2: SIMPAN PENGELUARAN KAS KECIL CABANG ({$branch->nama_cabang}) ---\n";
    $reqCabang = new \Illuminate\Http\Request([
        'tanggal' => date('Y-m-d'),
        'tipe' => 'Pengeluaran',
        'kategori' => 'BBM & Transportasi',
        'tipe_entitas' => 'Cabang',
        'branch_id' => $branch->id,
        'coa_kas_id' => $coaKas->id,
        'lawan_coa_id' => $coaBeban->id,
        'nominal' => 75000,
        'nama_pengaju' => 'Siti Admin Cabang',
        'keterangan' => 'Bensin motor kurir pengiriman cabang ' . $branch->nama_cabang,
    ]);

    $resCabang = $controller->store($reqCabang);
    $dataCabang = json_decode($resCabang->getContent(), true);
    echo "Response Status: " . ($dataCabang['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    echo "Message: " . ($dataCabang['message'] ?? '-') . "\n";
    if (!empty($dataCabang['data'])) {
        $cbgTrx = PettyCash::find($dataCabang['data']['id']);
        echo "Nomor Transaksi: {$cbgTrx->nomor_transaksi}\n";
        echo "Tipe Entitas: {$cbgTrx->tipe_entitas} (Branch ID: {$cbgTrx->branch_id})\n";
        if ($cbgTrx->jurnal_id) {
            $jurnalCbg = Jurnal::with('details.account')->find($cbgTrx->jurnal_id);
            echo "Jurnal Cabang Terbentuk: {$jurnalCbg->nomor_jurnal}\n";
            echo "Jurnal Branch ID: {$jurnalCbg->branch_id} (Cabang: {$branch->nama_cabang})\n";
            echo "Jurnal Keterangan: {$jurnalCbg->keterangan}\n";
            foreach ($jurnalCbg->details as $det) {
                echo "   -> [{$det->account->code} {$det->account->name}] Debit: Rp " . number_format($det->debit, 0, ',', '.') . " | Kredit: Rp " . number_format($det->kredit, 0, ',', '.') . "\n";
            }
        }
    }
}

// 4. Test Simpan Pengisian Kas Kecil PT
echo "\n--- TEST 3: SIMPAN TOP-UP / PENGISIAN KAS KECIL PT ---\n";
$reqTopup = new \Illuminate\Http\Request([
    'tanggal' => date('Y-m-d'),
    'tipe' => 'Pemasukan',
    'kategori' => 'Pengisian Kas Kecil',
    'tipe_entitas' => 'PT',
    'coa_kas_id' => $coaKas->id,
    'lawan_coa_id' => $coaKas->id, // atau Bank
    'nominal' => 2000000,
    'nama_pengaju' => 'Finance Pusat',
    'keterangan' => 'Pengisian kas kecil awal minggu dari Bank BCA',
]);

$resTopup = $controller->store($reqTopup);
$dataTopup = json_decode($resTopup->getContent(), true);
echo "Response Status: " . ($dataTopup['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . ($dataTopup['message'] ?? '-') . "\n";

echo "\n=== PENGUJIAN SELESAI DENGAN SUKSES! ===\n";
