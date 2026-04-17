<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = app('router')->getRoutes()->getRoutes();
foreach ($routes as $r) {
    $uri = $r->uri();
    if (strpos($uri,'admin/users') !== false) {
        echo $r->methods()[0] . ' ' . $uri . ' -> ' . $r->getActionName() . PHP_EOL;
    }
}
