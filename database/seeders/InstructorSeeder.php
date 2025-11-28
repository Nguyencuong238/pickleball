<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instructor;
use App\Models\InstructorExperience;
use App\Models\InstructorCertification;
use App\Models\InstructorReview;
use App\Models\InstructorPackage;
use App\Models\InstructorLocation;
use App\Models\InstructorSchedule;
use App\Models\InstructorTeachingMethod;
use App\Models\Province;
use App\Models\User;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = Province::all();

        if ($provinces->isEmpty()) {
            return;
        }

        // Get HCM province for sample data
        $hcmProvince = $provinces->where('name', 'like', '%Ho Chi Minh%')->first()
            ?? $provinces->where('name', 'like', '%HCM%')->first()
            ?? $provinces->first();

        // Create sample instructors
        $instructorsData = [
            [
                'name' => 'Nguyen Van Hung',
                'bio' => 'Huan luyen vien Pickleball chuyen nghiep',
                'description' => 'Voi hon 8 nam kinh nghiem trong linh vuc Pickleball, toi da dao tao hang nghin hoc vien tu co ban den nang cao. Phuong phap giang day cua toi tap trung vao viec phat trien ky thuat vung chac va tu duy chien thuat thong minh.',
                'experience' => 'Huan luyen vien Pickleball chuyen nghiep',
                'experience_years' => 8,
                'student_count' => 500,
                'total_hours' => 2000,
                'specialties' => ['Day 1-1', 'Day nhom', 'Day tre em', 'Nang cao'],
                'phone' => '0901234567',
                'zalo' => '0901234567',
                'email' => 'hung.nguyen@pickleball.vn',
                'price_per_session' => 500000,
                'is_verified' => true,
                'is_certified' => true,
                'rating' => 4.9,
                'reviews_count' => 89,
                'ward' => 'Phuong Thao Dien',
                'province_id' => $hcmProvince->id,
            ],
            [
                'name' => 'Tran Thi Mai',
                'bio' => 'Chuyen gia Pickleball danh cho phu nu',
                'description' => 'Toi chuyen ve dao tao Pickleball cho phu nu va nguoi moi bat dau. Voi phuong phap nhe nhang nhung hieu qua, toi giup hoc vien tu tin tren san dau.',
                'experience' => 'Giang vien Pickleball danh cho phu nu',
                'experience_years' => 5,
                'student_count' => 300,
                'total_hours' => 1200,
                'specialties' => ['Day 1-1', 'Day nhom', 'Phu nu'],
                'phone' => '0912345678',
                'zalo' => '0912345678',
                'email' => 'mai.tran@pickleball.vn',
                'price_per_session' => 400000,
                'is_verified' => true,
                'is_certified' => true,
                'rating' => 4.8,
                'reviews_count' => 56,
                'ward' => 'Phuong An Phu',
                'province_id' => $hcmProvince->id,
            ],
            [
                'name' => 'Le Van Duc',
                'bio' => 'Cuu van dong vien quoc gia',
                'description' => 'La cuu thanh vien doi tuyen quoc gia, toi mang den cho hoc vien nhung ky thuat thi dau dinh cao va tinh than the thao chuyen nghiep.',
                'experience' => 'Cuu VDV doi tuyen quoc gia Pickleball',
                'experience_years' => 10,
                'student_count' => 800,
                'total_hours' => 3500,
                'specialties' => ['Thi dau', 'Nang cao', 'Chien thuat'],
                'phone' => '0923456789',
                'zalo' => '0923456789',
                'email' => 'duc.le@pickleball.vn',
                'price_per_session' => 700000,
                'is_verified' => true,
                'is_certified' => true,
                'rating' => 4.9,
                'reviews_count' => 120,
                'ward' => 'Phuong Binh Thanh',
                'province_id' => $hcmProvince->id,
            ],
        ];

        foreach ($instructorsData as $instructorData) {
            $instructor = Instructor::create($instructorData);

            // Create experiences
            $this->createExperiences($instructor);

            // Create certifications
            $this->createCertifications($instructor);

            // Create packages
            $this->createPackages($instructor);

            // Create locations
            $this->createLocations($instructor);

            // Create schedules
            $this->createSchedules($instructor);

            // Create teaching methods
            $this->createTeachingMethods($instructor);
        }

        // Create reviews for first instructor only (requires users)
        $firstInstructor = Instructor::first();
        if ($firstInstructor) {
            $this->createReviews($firstInstructor);
        }
    }

    private function createExperiences(Instructor $instructor): void
    {
        $experiences = [
            [
                'title' => 'Huan luyen vien chinh - CLB Pickleball Saigon Elite',
                'organization' => 'CLB Pickleball Saigon Elite',
                'description' => 'Phu trach dao tao cac lop nang cao va huan luyen doi tuyen thi dau cua cau lac bo.',
                'start_year' => 2021,
                'end_year' => null,
                'is_current' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Giang vien - Trung tam The thao Rach Chiec',
                'organization' => 'Trung tam The thao Rach Chiec',
                'description' => 'Giang day cac lop Pickleball tu co ban den nang cao cho nguoi lon va thieu nien.',
                'start_year' => 2018,
                'end_year' => 2021,
                'is_current' => false,
                'sort_order' => 2,
            ],
            [
                'title' => 'Tro ly huan luyen vien - CLB Pickleball District 2',
                'organization' => 'CLB Pickleball District 2',
                'description' => 'Ho tro huan luyen vien chinh trong cac buoi tap va to chuc cac giai dau noi bo.',
                'start_year' => 2017,
                'end_year' => 2018,
                'is_current' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($experiences as $experience) {
            InstructorExperience::create(array_merge($experience, [
                'instructor_id' => $instructor->id,
            ]));
        }
    }

    private function createCertifications(Instructor $instructor): void
    {
        $certifications = [
            [
                'title' => 'IPTPA Certified Coach',
                'issuer' => 'International Pickleball Teaching Professional Association',
                'year' => 2020,
                'type' => 'certification',
                'sort_order' => 1,
            ],
            [
                'title' => 'Chung chi HLV Pickleball Viet Nam',
                'issuer' => 'Lien doan Pickleball Viet Nam',
                'year' => 2019,
                'type' => 'certification',
                'sort_order' => 2,
            ],
            [
                'title' => 'Vo dich Doubles - HCM Open 2023',
                'issuer' => 'Giai dau Pickleball TP.HCM mo rong',
                'year' => 2023,
                'type' => 'achievement',
                'sort_order' => 3,
            ],
            [
                'title' => 'A quan Singles - Vietnam National 2022',
                'issuer' => 'Giai Vo dich Quoc gia Pickleball',
                'year' => 2022,
                'type' => 'achievement',
                'sort_order' => 4,
            ],
        ];

        foreach ($certifications as $certification) {
            InstructorCertification::create(array_merge($certification, [
                'instructor_id' => $instructor->id,
            ]));
        }
    }

    private function createPackages(Instructor $instructor): void
    {
        $basePrice = $instructor->price_per_session ?? 500000;

        $packages = [
            [
                'name' => 'Buoi le',
                'description' => 'Hoc thu hoac dang ky buoi le',
                'price' => $basePrice,
                'sessions_count' => 1,
                'discount_percent' => 0,
                'is_group' => false,
                'max_group_size' => null,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Goi 4 buoi',
                'description' => 'Goi hoc 4 buoi tiet kiem 10%',
                'price' => $basePrice * 4 * 0.9,
                'sessions_count' => 4,
                'discount_percent' => 10,
                'is_group' => false,
                'max_group_size' => null,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Goi 8 buoi',
                'description' => 'Goi hoc 8 buoi tiet kiem 20%',
                'price' => $basePrice * 8 * 0.8,
                'sessions_count' => 8,
                'discount_percent' => 20,
                'is_group' => false,
                'max_group_size' => null,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Lop nhom (4-6 nguoi)',
                'description' => 'Hoc nhom tiet kiem chi phi',
                'price' => $basePrice * 0.5,
                'sessions_count' => 1,
                'discount_percent' => 50,
                'is_group' => true,
                'max_group_size' => 6,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $package) {
            InstructorPackage::create(array_merge($package, [
                'instructor_id' => $instructor->id,
            ]));
        }
    }

    private function createLocations(Instructor $instructor): void
    {
        $locations = [
            [
                'district' => 'Quan 2',
                'city' => 'TP. Ho Chi Minh',
                'venues' => 'San Rach Chiec, San An Phu',
                'sort_order' => 1,
            ],
            [
                'district' => 'Thu Duc',
                'city' => 'TP. Ho Chi Minh',
                'venues' => 'San Thu Duc Sports',
                'sort_order' => 2,
            ],
            [
                'district' => 'Quan 7',
                'city' => 'TP. Ho Chi Minh',
                'venues' => 'San Phu My Hung',
                'sort_order' => 3,
            ],
        ];

        foreach ($locations as $location) {
            InstructorLocation::create(array_merge($location, [
                'instructor_id' => $instructor->id,
            ]));
        }
    }

    private function createSchedules(Instructor $instructor): void
    {
        $schedules = [
            [
                'days' => 'Thu 2 - Thu 6',
                'time_slots' => '06:00 - 08:00, 17:00 - 21:00',
                'sort_order' => 1,
            ],
            [
                'days' => 'Thu 7 - Chu nhat',
                'time_slots' => '06:00 - 21:00',
                'sort_order' => 2,
            ],
        ];

        foreach ($schedules as $schedule) {
            InstructorSchedule::create(array_merge($schedule, [
                'instructor_id' => $instructor->id,
            ]));
        }
    }

    private function createTeachingMethods(Instructor $instructor): void
    {
        $methods = [
            [
                'title' => 'Ca nhan hoa',
                'description' => 'Thiet ke chuong trinh phu hop voi trinh do va muc tieu cua tung hoc vien',
                'icon' => 'user-cog',
                'sort_order' => 1,
            ],
            [
                'title' => 'Thuc hanh nhieu',
                'description' => '70% thoi gian thuc hanh, 30% ly thuyet de toi da hoa ky nang thuc chien',
                'icon' => 'tools',
                'sort_order' => 2,
            ],
            [
                'title' => 'Phan tich video',
                'description' => 'Quay va phan tich ky thuat de hoc vien nhan ra diem can cai thien',
                'icon' => 'video',
                'sort_order' => 3,
            ],
            [
                'title' => 'Thi dau thuc te',
                'description' => 'To chuc cac tran dau tap de hoc vien ap dung kien thuc vao thuc te',
                'icon' => 'trophy',
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            InstructorTeachingMethod::create(array_merge($method, [
                'instructor_id' => $instructor->id,
            ]));
        }
    }

    private function createReviews(Instructor $instructor): void
    {
        $users = User::take(3)->get();

        if ($users->isEmpty()) {
            return;
        }

        $reviews = [
            [
                'rating' => 5,
                'content' => 'Coach day rat tan tam va de hieu. Sau 3 thang hoc, ky thuat serve cua toi da cai thien ro ret. Rat recommend cho nhung ai muon hoc Pickleball mot cach bai ban!',
                'tags' => ['Tan tam', 'De hieu', 'Kien nhan'],
            ],
            [
                'rating' => 5,
                'content' => 'Minh da tham gia lop nang cao duoc 6 thang. Phuong phap giang day rat khoa hoc, co phan tich video giup minh nhin ra loi sai rat nhanh.',
                'tags' => ['Chuyen nghiep', 'Khoa hoc'],
            ],
            [
                'rating' => 5,
                'content' => 'Ban dau em rat ngai vi chua biet gi ve Pickleball, nhung coach rat kien nhan va tao moi truong thoai mai. Gio em da co the choi cung ban be!',
                'tags' => ['Kien nhan', 'Than thien', 'Vui ve'],
            ],
        ];

        foreach ($reviews as $index => $review) {
            if (isset($users[$index])) {
                InstructorReview::create(array_merge($review, [
                    'instructor_id' => $instructor->id,
                    'user_id' => $users[$index]->id,
                    'is_approved' => true,
                ]));
            }
        }
    }
}
