<?php
$base = 'http://127.0.0.1:8000';
$cookie = __DIR__ . '/cookies.txt';

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
    'email' => 'test@example.com',
    'password' => 'password'
];

list($resp, $info) = post($base . '/login', $creds, $cookie);

echo "HTTP Code: " . $info['http_code'] . "\n";
echo "Effective URL: " . ($info['url'] ?? '') . "\n";
// Show small excerpt of body
echo "Body excerpt:\n" . substr($resp,0,400) . "\n";

// Check if redirected to /dashboard
if (strpos($info['url'], '/dashboard') !== false) {
    echo "Login redirected to /dashboard — OK\n";
} else {
    echo "Did not redirect to /dashboard.\n";
}
