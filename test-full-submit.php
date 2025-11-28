<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\InstructorController;

// Clear logs
file_put_contents(__DIR__ . '/storage/logs/laravel.log', "[TEST START]\n");

// Tạo fake request với dữ liệu ĐẦY ĐỦ
$request = Request::create(
    '/admin/instructors',
    'POST',
    [
        'name' => 'Giảng Viên Test ' . time(),
        'bio' => 'Huấn Luyện Viên Pickleball Chuyên Nghiệp',
        'description' => 'Mô tả chi tiết về giảng viên này',
        'experience_years' => 5,
        'student_count' => 20,
        'total_hours' => 150,
        'price_per_session' => 500000,
        'ward' => 'Phường 1',
        'phone' => '0901234567',
        'email' => 'instructor' . time() . '@example.com',
        'zalo' => '0901234567',
        'province_id' => 1,
        
        // Kinh nghiệm
        'experiences' => [
            [
                'title' => 'Huấn Luyện Viên Chính',
                'organization' => 'CLB Pickleball Saigon Elite',
                'start_year' => 2020,
                'end_year' => null,
                'description' => 'Quản lý và huấn luyện các học viên'
            ]
        ],
        
        // Chứng chỉ
        'certifications' => [
            [
                'title' => 'IPTPA Certified Coach',
                'issuer' => 'International Pickleball Association',
                'year' => 2022,
                'is_award' => null
            ]
        ],
        
        // Phương pháp giảng dạy
        'teaching_methods' => [
            [
                'title' => 'Cá Nhân Hóa',
                'description' => 'Tập trung vào nhu cầu riêng của từng học viên'
            ]
        ],
        
        // Gói học
        'packages' => [
            [
                'name' => 'Buổi Lẻ',
                'description' => 'Học một buổi',
                'price' => 500000,
                'discount_percent' => 0,
                'sessions_count' => 1,
                'is_active' => '1'
            ],
            [
                'name' => 'Gói 4 Buổi',
                'description' => 'Học 4 buổi liên tiếp',
                'price' => 1800000,
                'discount_percent' => 10,
                'sessions_count' => 4,
                'is_active' => '1'
            ]
        ],
        
        // Khu vực dạy
        'locations' => [
            [
                'district' => 'Quận 2',
                'city' => 'TP. HCM',
                'venues' => 'Sân Rạch Chiếc, Sân An Phú'
            ]
        ],
        
        // Lịch dạy
        'schedules' => [
            [
                'days' => 'Thứ 2 - Thứ 6',
                'time_slots' => '06:00 - 08:00, 17:00 - 21:00'
            ]
        ]
    ]
);

// Fake user auth
$user = \App\Models\User::find(1);
auth()->setUser($user);

echo "=== FULL INSTRUCTOR SUBMIT TEST ===\n";
echo "User: " . auth()->user()->name . "\n";
echo "Total form fields: " . count($request->all()) . "\n\n";

// Call controller
$controller = new InstructorController();
$response = $controller->store($request);

echo "\n=== Response Status ===\n";
echo "Status: " . $response->getStatusCode() . "\n";
echo "Location: " . $response->headers->get('location') . "\n";

// Check DB
echo "\n=== CHECK DATABASE ===\n";

$lastInstructor = DB::table('instructors')->latest('id')->first();
if ($lastInstructor) {
    echo "✓ INSTRUCTOR CREATED: ID {$lastInstructor->id}, Name: {$lastInstructor->name}\n";
    
    $expCount = DB::table('instructor_experiences')->where('instructor_id', $lastInstructor->id)->count();
    echo "  - Experiences: {$expCount}\n";
    
    $certCount = DB::table('instructor_certifications')->where('instructor_id', $lastInstructor->id)->count();
    echo "  - Certifications: {$certCount}\n";
    
    $methodCount = DB::table('instructor_teaching_methods')->where('instructor_id', $lastInstructor->id)->count();
    echo "  - Teaching Methods: {$methodCount}\n";
    
    $pkgCount = DB::table('instructor_packages')->where('instructor_id', $lastInstructor->id)->count();
    echo "  - Packages: {$pkgCount}\n";
    
    $locCount = DB::table('instructor_locations')->where('instructor_id', $lastInstructor->id)->count();
    echo "  - Locations: {$locCount}\n";
    
    $schedCount = DB::table('instructor_schedules')->where('instructor_id', $lastInstructor->id)->count();
    echo "  - Schedules: {$schedCount}\n";
} else {
    echo "✗ NO INSTRUCTOR CREATED\n";
}

echo "\n=== LOGS ===\n";
echo file_get_contents(__DIR__ . '/storage/logs/laravel.log');

echo "\n\nDone!\n";
