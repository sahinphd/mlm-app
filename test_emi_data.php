<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmiSchedule;

$count = EmiSchedule::count();
echo "Total EMIs: $count\n";

$emis = EmiSchedule::with('user')->take(10)->get();
foreach($emis as $emi) {
    echo "EMI ID: {$emi->id}, Status: {$emi->status}, User: " . ($emi->user ? $emi->user->name : 'NULL') . ", Phone: [" . ($emi->user ? $emi->user->phone : 'N/A') . "]\n";
}
