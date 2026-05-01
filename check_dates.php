<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Commission;

$comms = Commission::orderBy('created_at', 'desc')->limit(10)->get();

foreach ($comms as $c) {
    echo "ID: {$c->id} | Type: {$c->type} | Status: {$c->status} | Amount: {$c->amount} | Created: {$c->created_at} | Withdrawable: {$c->withdrawable_at}\n";
}
