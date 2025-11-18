<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::create('/');
$response = $kernel->handle($request);

$tournaments = \App\Models\Tournament::where('status', 'active')->get();
echo "Total active tournaments: " . count($tournaments) . "\n";

if ($tournaments->count() > 0) {
    echo "\nFirst 5 tournaments:\n";
    foreach ($tournaments->take(5) as $t) {
        echo "- " . $t->name . " (start: " . ($t->start_date ? $t->start_date->format('Y-m-d') : 'null') . ", prize: " . $t->prizes . ")\n";
    }
}
