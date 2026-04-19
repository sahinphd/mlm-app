<?php
// Use cookie from previous login script (cookies_admin.txt)
$cookie = __DIR__ . '/cookies_admin.txt';
$url = 'http://127.0.0.1:8000/admin/users/data?draw=1&start=0&length=10';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
echo "HTTP: " . $info['http_code'] . "\n";
echo substr($res,0,1000) . PHP_EOL;
