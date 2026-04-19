<?php
$base = 'http://127.0.0.1:8000';
$cookie = __DIR__ . '/cookies_admin.txt';

function get($url, $cookie){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

function post($url, $data, $cookie){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

list($loginPage, $info) = get($base . '/login', $cookie);
if (!preg_match('/name="_token" value="([^"]+)"/', $loginPage, $m)){
    echo "CSRF token not found\n";
    exit(1);
}
$token = $m[1];

$creds = [
    '_token' => $token,
    'email' => 'admin@example.com',
    'password' => 'password'
];

list($resp, $info) = post($base . '/login', $creds, $cookie);

// Now fetch /admin
list($adminPage, $ainfo) = get($base . '/admin', $cookie);
file_put_contents(__DIR__.'/admin_page.html', $adminPage);
echo "Admin page HTTP: " . $ainfo['http_code'] . " URL: " . ($ainfo['url'] ?? '') . "\n";
echo "Saved to scripts/admin_page.html\n";
