<?php
// Quick API test
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Simulate a request
$request = \Illuminate\Http\Request::create('/athlete-management/debug', 'GET');
$request->setUserResolver(function () {
    return \App\Models\User::find(1); // Or whatever user you want to test
});

$response = $kernel->handle($request);
echo $response->content();
