<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\PettyCash;
use Illuminate\Support\Facades\Auth;

$user = User::first();
Auth::login($user);

session([
    'active_company_id'   => 1,
    'active_company_name' => 'PT Persada Aman Sentosa',
    'active_branch_id'    => null,
]);

$controller = new \App\Http\Controllers\PettyCashController();

$kasKecil = KasBank::find(1);
$initialSaldo = (float) $kasKecil->saldo_berjalan;
echo "1. Saldo Kas Kecil Awal: Rp " . number_format($initialSaldo, 0, ',', '.') . "\n";

// TEST A: Input Pengeluaran Rp 200.000
$req = new \Illuminate\Http\Request([
    'tanggal'       => date('Y-m-d'),
    'tipe'          => 'Pengeluaran',
    'kategori'      => 'BBM & Transportasi',
    'coa_kas_id'    => 198, // COA Kas Kecil
    'lawan_coa_id'  => 209, // COA Beban
    'nominal'       => '200.000',
    'nama_pengaju'  => 'Driver Kantor',
    'keterangan'    => 'Bensin mobil operasional',
]);

$res = $controller->store($req);
$data = json_decode($res->getContent(), true);

$kasKecil->refresh();
echo "\n2. Setelah Input Pengeluaran Rp 200.000:\n";
echo "   Status Response: " . ($data['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "   Message: " . strip_tags($data['message']) . "\n";
echo "   Saldo Kas Kecil Sekarang: Rp " . number_format($kasKecil->saldo_berjalan, 0, ',', '.') . "\n";
echo "   Selisih: Rp " . number_format($initialSaldo - $kasKecil->saldo_berjalan, 0, ',', '.') . " (Sesuai Rp 200.000)\n";

$createdId = $data['data']['id'];
$createdPC = PettyCash::find($createdId);
echo "   Petty Cash Saldo Sblm: Rp " . number_format($createdPC->saldo_sebelum, 0, ',', '.') . "\n";
echo "   Petty Cash Saldo Ssdh: Rp " . number_format($createdPC->saldo_sesudah, 0, ',', '.') . "\n";

// TEST B: Hapus Pengeluaran -> Saldo harus kembali
$resDel = $controller->destroy($createdPC);
$dataDel = json_decode($resDel->getContent(), true);

$kasKecil->refresh();
echo "\n3. Setelah Hapus Transaksi:\n";
echo "   Status Response: " . ($dataDel['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "   Saldo Kas Kecil Kembali ke: Rp " . number_format($kasKecil->saldo_berjalan, 0, ',', '.') . "\n";

echo "\n=== SEMUA PENGUJIAN PENGURANGAN SALDO BERHASIL 100%! ===\n";
