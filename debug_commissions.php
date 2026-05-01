<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Wallet;
use App\Models\Commission;

$wallet = Wallet::where('earning_balance', 276)->first();
$uid = $wallet->user_id;

$bvComms = Commission::where('user_id', $uid)->where('type', 'bv')->get();

echo "Current Time: " . now() . "\n";
foreach ($bvComms as $c) {
    echo "ID: {$c->id} | Amount: {$c->amount} | Status: [{$c->status}] | Withdrawable At: [{$c->withdrawable_at}]\n";
}
