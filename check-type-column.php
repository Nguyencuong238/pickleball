<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Check type column
$result = DB::select("SHOW COLUMNS FROM instructor_certifications WHERE Field='type'");

foreach ($result as $col) {
    echo "Field: {$col->Field}\n";
    echo "Type: {$col->Type}\n";
    echo "Null: {$col->Null}\n";
    echo "Default: {$col->Default}\n";
    echo "Extra: {$col->Extra}\n";
}

echo "\n";

// Check what's in the enum
$fullSchema = DB::select("SHOW FULL COLUMNS FROM instructor_certifications");
foreach ($fullSchema as $col) {
    if ($col->Field === 'type') {
        echo "Full Type Definition: {$col->Type}\n";
        echo "Collation: {$col->Collation}\n";
        echo "Comment: {$col->Comment}\n";
    }
}
