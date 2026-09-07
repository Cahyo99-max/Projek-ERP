<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$columns = DB::select('SHOW COLUMNS FROM master_gudang');
foreach ($columns as $c) {
    echo "Column: {$c->Field} | Type: {$c->Type} | Nullable: {$c->Null}\n";
}
