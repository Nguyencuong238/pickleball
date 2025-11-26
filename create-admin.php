<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

// Create roles if not exists
if (!Role::where('name', 'admin')->exists()) {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
}

if (!Role::where('name', 'home_yard')->exists()) {
    Role::create(['name' => 'home_yard', 'guard_name' => 'web']);
}

// Get or create admin user
$user = User::firstOrCreate(
    ['email' => 'admin@pickleball.local'],
    [
        'name' => 'Admin User',
        'password' => bcrypt('password'),
        'phone' => '0901234567'
    ]
);

// Assign role
$user->syncRoles('admin');

echo "✓ Admin user ready!\n";
echo "  Email: admin@pickleball.local\n";
echo "  Password: password\n";
echo "\n⚠️  Vui lòng đổi mật khẩu sau khi đăng nhập!\n";
