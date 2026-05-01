<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Wallet;
use App\Models\Commission;
use Illuminate\Support\Facades\DB;

$stats = [
    'types' => Commission::select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
        ->groupBy('type')->get()->toArray(),
    'statuses' => Commission::select('status', DB::raw('count(*) as count'))->groupBy('status')->get()->toArray(),
    'wallets' => Wallet::select(
        DB::raw('sum(main_balance) as main'),
        DB::raw('sum(commission_balance) as commission'),
        DB::raw('sum(earning_balance) as earning')
    )->first()->toArray()
];

echo json_encode($stats, JSON_PRETTY_PRINT);
