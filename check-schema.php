<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = [
    'instructors',
    'instructor_experiences',
    'instructor_certifications',
    'instructor_teaching_methods',
    'instructor_packages',
    'instructor_locations',
    'instructor_schedules'
];

foreach ($tables as $table) {
    echo "\n===== TABLE: $table =====\n";
    $columns = Schema::getColumnListing($table);
    
    foreach ($columns as $col) {
        $type = Schema::getColumnType($table, $col);
        echo "  • $col ($type)\n";
    }
}

echo "\n\nDone!\n";
