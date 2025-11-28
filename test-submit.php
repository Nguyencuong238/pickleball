<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\InstructorController;

// Tạo fake request
$request = Request::create(
    '/admin/instructors',
    'POST',
    [
        'name' => 'Test Instructor ' . time(),
        'bio' => 'Professional Coach',
        'description' => 'This is a test instructor',
        'experience_years' => 5,
        'student_count' => 20,
        'total_hours' => 100,
        'price_per_session' => 500000,
        'ward' => 'Ward 1',
        'phone' => '0123456789',
        'email' => 'test@example.com',
        'zalo' => '0123456789',
        'province_id' => 1,
    ]
);

// Fake user auth
$user = \App\Models\User::find(1);
auth()->setUser($user);

echo "=== TEST FORM SUBMISSION ===\n";
echo "User: " . auth()->user()->name . "\n";
echo "Request data: \n";
print_r($request->all());

// Call controller
$controller = new InstructorController();
$response = $controller->store($request);

echo "\n=== Response ===\n";
echo "Status: " . $response->getStatusCode() . "\n";

// Check DB
echo "\n=== Instructors in DB after test ===\n";
$instructors = DB::table('instructors')->latest('id')->limit(1)->first();
if ($instructors) {
    echo "Last instructor: ID: {$instructors->id}, Name: {$instructors->name}\n";
}

echo "\nDone\n";
