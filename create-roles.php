<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;

$roles = ['admin', 'home_yard', 'user'];

foreach ($roles as $role) {
    if (!Role::where('name', $role)->exists()) {
        Role::create(['name' => $role, 'guard_name' => 'web']);
        echo "✓ Role created: $role\n";
    } else {
        echo "✓ Role already exists: $role\n";
    }
}
