<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$admins = User::where('role','admin')->get(['id','name','email','phone','role','created_at','updated_at']);
if ($admins->isEmpty()) {
    echo "No admin users found\n";
    exit;
}
foreach ($admins as $a) {
    echo "ID: " . $a->id . PHP_EOL;
    echo "Name: " . $a->name . PHP_EOL;
    echo "Email: " . $a->email . PHP_EOL;
    echo "Phone: " . ($a->phone ?? '(none)') . PHP_EOL;
    echo "Role: " . $a->role . PHP_EOL;
    echo "Created: " . $a->created_at . PHP_EOL;
    echo "Updated: " . $a->updated_at . PHP_EOL;
    echo str_repeat('-',40) . PHP_EOL;
}
