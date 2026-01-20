<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\Instructor;

$instructor = Instructor::create([
    'name' => 'Giảng Viên Mẫu',
    'bio' => 'Giảng viên dạy pickleball chuyên nghiệp',
    'description' => 'Có kinh nghiệm giảng dạy pickleball',
    'experience_years' => 5,
    'student_count' => 20,
    'total_hours' => 500,
    'price_per_session' => 500000,
    'phone' => '0123456789',
    'email' => 'instructor@example.com',
    'province_id' => 1
]);

echo "✓ Tạo giảng viên thành công! ID: " . $instructor->id . "\n";
