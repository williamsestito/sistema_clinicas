<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// 1) GET login page to grab CSRF token + session cookie
$loginPage = \Illuminate\Http\Request::create('/login', 'GET');
$loginResp = $kernel->handle($loginPage);
$kernel->terminate($loginPage, $loginResp);

$html = $loginResp->getContent();
preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/', $html, $m);
$token = $m[1] ?? '';

$sessionCookies = [];
foreach ($loginResp->headers->getCookies() as $c) {
    $sessionCookies[$c->getName()] = $c->getValue();
}

// 2) POST login
$postLogin = \Illuminate\Http\Request::create('/login', 'POST', [
    '_token'   => $token,
    'email'    => 'admin@admin.com',
    'password' => '123123',
]);
foreach ($sessionCookies as $k => $v) {
    $postLogin->cookies->set($k, $v);
}
$postResp = $kernel->handle($postLogin);
$kernel->terminate($postLogin, $postResp);

foreach ($postResp->headers->getCookies() as $c) {
    $sessionCookies[$c->getName()] = $c->getValue();
}
echo "Login: " . $postResp->getStatusCode() . PHP_EOL;

// 3) Test pages
$pages = [
    'admin/dashboard',
    'admin/patients/search-json?q=a',
    'admin/patients/search-json?q=ma',
];

foreach ($pages as $p) {
    $req = \Illuminate\Http\Request::create('/' . $p, 'GET');
    foreach ($sessionCookies as $k => $v) {
        $req->cookies->set($k, $v);
    }
    $resp = $kernel->handle($req);
    $code = $resp->getStatusCode();
    echo "$p: $code";
    if ($code === 200 && str_contains($p, 'search-json')) {
        echo " => " . $resp->getContent();
    }
    echo PHP_EOL;
    $kernel->terminate($req, $resp);
}

echo "Done." . PHP_EOL;
