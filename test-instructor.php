<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Kiểm tra instructors table
$instructors = DB::table('instructors')->get();

echo "Total instructors in DB: " . count($instructors) . "\n";

if (count($instructors) > 0) {
    echo "Last 3 instructors:\n";
    $last = DB::table('instructors')->latest('id')->limit(3)->get();
    foreach ($last as $instr) {
        echo "ID: {$instr->id}, Name: {$instr->name}, Created: {$instr->created_at}\n";
    }
}

echo "\nDone\n";
