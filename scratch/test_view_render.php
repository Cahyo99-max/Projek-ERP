<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::first();
Auth::login($user);

session([
    'active_company_id'   => 1,
    'active_company_name' => 'PT Persada Aman Sentosa',
    'active_branch_id'    => null,
]);

$controller = new \App\Http\Controllers\PettyCashController();
$req = new \Illuminate\Http\Request();
$response = $controller->index($req);

echo "View Name: " . $response->name() . "\n";
$html = $response->render();
echo "Rendered successfully! Total HTML Length: " . strlen($html) . " bytes\n";
file_put_contents(__DIR__ . '/rendered_petty_cash.html', $html);
echo "Saved to scratch/rendered_petty_cash.html\n";
