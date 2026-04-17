<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::select('id','email','name','role')->get();
foreach ($users as $u) {
    echo $u->id . ' | ' . $u->email . ' | role=' . ($u->role ?? '(null)') . PHP_EOL;
}
