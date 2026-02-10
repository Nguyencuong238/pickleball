<?php
$basePath = __DIR__;
require $basePath . '/bootstrap/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$matches = \App\Models\MatchModel::where('match_date', '2025-01-03')
    ->orWhere('match_date', '2025-12-18')
    ->get();

foreach ($matches as $m) {
    echo "Date: {$m->match_date}, Time: {$m->match_time}, Status: {$m->status}" . PHP_EOL;
}
