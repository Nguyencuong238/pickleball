<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Province;
use App\Models\InstructorExperience;
use App\Models\InstructorCertification;
use App\Models\InstructorTeachingMethod;
use App\Models\InstructorPackage;
use App\Models\InstructorLocation;
use App\Models\InstructorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $instructors = Instructor::with('province')->latest()->paginate(5);
        return view('admin.instructors.index', compact('instructors'));
    }

    public function create()
    {
        $provinces = Province::all();
        return view('admin.instructors.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'bio' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'student_count' => 'nullable|integer|min:0',
            'total_hours' => 'nullable|integer|min:0',
            'price_per_session' => 'nullable|numeric|min:0',
            'ward' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'zalo' => 'nullable|string|max:20',
            'province_id' => 'nullable|exists:provinces,id',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only('name', 'bio', 'description', 'experience_years', 'student_count', 
                                   'total_hours', 'price_per_session', 'ward', 'phone', 'email', 'zalo', 'province_id');
            
            // Set default values
            $data['experience_years'] = (int)($data['experience_years'] ?? 0);
            $data['student_count'] = (int)($data['student_count'] ?? 0);
            $data['total_hours'] = (int)($data['total_hours'] ?? 0);
            $data['price_per_session'] = (int)($data['price_per_session'] ?? 0);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('instructors', 'public');
            }

            $instructor = Instructor::create($data);

            // Kinh nghiệm giảng dạy
            if ($request->has('experiences')) {
                foreach ($request->experiences as $index => $exp) {
                    if (!empty($exp['title'])) {
                        InstructorExperience::create([
                            'instructor_id' => $instructor->id,
                            'title' => $exp['title'],
                            'organization' => $exp['organization'] ?? null,
                            'start_year' => $exp['start_year'] ?? null,
                            'end_year' => $exp['end_year'] ?? null,
                            'description' => $exp['description'] ?? null,
                            'is_current' => !isset($exp['end_year']) || empty($exp['end_year']) ? 1 : 0,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Chứng chỉ
            if ($request->has('certifications')) {
                foreach ($request->certifications as $index => $cert) {
                    if (!empty($cert['title'])) {
                        InstructorCertification::create([
                            'instructor_id' => $instructor->id,
                            'title' => $cert['title'],
                            'issuer' => $cert['issuer'] ?? null,
                            'year' => $cert['year'] ?? null,
                            'type' => isset($cert['is_award']) && $cert['is_award'] ? 'achievement' : 'certification',
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Phương pháp giảng dạy
            if ($request->has('teaching_methods')) {
                foreach ($request->teaching_methods as $index => $method) {
                    if (!empty($method['title'])) {
                        InstructorTeachingMethod::create([
                            'instructor_id' => $instructor->id,
                            'title' => $method['title'],
                            'description' => $method['description'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Gói học
            if ($request->has('packages')) {
                foreach ($request->packages as $index => $package) {
                    if (!empty($package['name'])) {
                        InstructorPackage::create([
                            'instructor_id' => $instructor->id,
                            'name' => $package['name'],
                            'description' => $package['description'] ?? null,
                            'price' => $package['price'] ?? 0,
                            'sessions_count' => $package['sessions_count'] ?? null,
                            'discount_percent' => $package['discount_percent'] ?? 0,
                            'is_group' => isset($package['is_group']) ? 1 : 0,
                            'max_group_size' => $package['max_group_size'] ?? null,
                            'is_popular' => isset($package['is_popular']) ? 1 : 0,
                            'is_active' => isset($package['is_active']) ? 1 : 0,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Khu vực dạy
            if ($request->has('locations')) {
                foreach ($request->locations as $index => $location) {
                    if (!empty($location['district'])) {
                        InstructorLocation::create([
                            'instructor_id' => $instructor->id,
                            'district' => $location['district'],
                            'city' => $location['city'] ?? null,
                            'venues' => $location['venues'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Lịch dạy
            if ($request->has('schedules')) {
                foreach ($request->schedules as $index => $schedule) {
                    if (!empty($schedule['days'])) {
                        InstructorSchedule::create([
                            'instructor_id' => $instructor->id,
                            'days' => $schedule['days'],
                            'time_slots' => $schedule['time_slots'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();
            
            return redirect()->route('admin.instructors.index')->with('success', 'Tạo giảng viên thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Instructor $instructor)
    {
        $provinces = Province::all();
        return view('admin.instructors.edit', compact('instructor', 'provinces'));
    }

    public function update(Request $request, Instructor $instructor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'bio' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'student_count' => 'nullable|integer|min:0',
            'total_hours' => 'nullable|integer|min:0',
            'price_per_session' => 'nullable|numeric|min:0',
            'ward' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'zalo' => 'nullable|string|max:20',
            'province_id' => 'nullable|exists:provinces,id',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only('name', 'bio', 'description', 'experience_years', 'student_count', 
                                   'total_hours', 'price_per_session', 'ward', 'phone', 'email', 'zalo', 'province_id');
            
            // Set default values
            $data['experience_years'] = (int)($data['experience_years'] ?? 0);
            $data['student_count'] = (int)($data['student_count'] ?? 0);
            $data['total_hours'] = (int)($data['total_hours'] ?? 0);
            $data['price_per_session'] = (int)($data['price_per_session'] ?? 0);

            if ($request->hasFile('image')) {
                if ($instructor->image) {
                    Storage::disk('public')->delete($instructor->image);
                }
                $data['image'] = $request->file('image')->store('instructors', 'public');
            }

            $instructor->update($data);

            // Delete existing related records
            $instructor->experiences()->delete();
            $instructor->certifications()->delete();
            $instructor->teachingMethods()->delete();
            $instructor->packages()->delete();
            $instructor->locations()->delete();
            $instructor->schedules()->delete();

            // Kinh nghiệm giảng dạy
            if ($request->has('experiences')) {
                foreach ($request->experiences as $index => $exp) {
                    if (!empty($exp['title'])) {
                        InstructorExperience::create([
                            'instructor_id' => $instructor->id,
                            'title' => $exp['title'],
                            'organization' => $exp['organization'] ?? null,
                            'start_year' => $exp['start_year'] ?? null,
                            'end_year' => $exp['end_year'] ?? null,
                            'description' => $exp['description'] ?? null,
                            'is_current' => !isset($exp['end_year']) || empty($exp['end_year']) ? 1 : 0,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Chứng chỉ
            if ($request->has('certifications')) {
                foreach ($request->certifications as $index => $cert) {
                    if (!empty($cert['title'])) {
                        InstructorCertification::create([
                            'instructor_id' => $instructor->id,
                            'title' => $cert['title'],
                            'issuer' => $cert['issuer'] ?? null,
                            'year' => $cert['year'] ?? null,
                            'type' => isset($cert['is_award']) && $cert['is_award'] ? 'achievement' : 'certification',
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Phương pháp giảng dạy
            if ($request->has('teaching_methods')) {
                foreach ($request->teaching_methods as $index => $method) {
                    if (!empty($method['title'])) {
                        InstructorTeachingMethod::create([
                            'instructor_id' => $instructor->id,
                            'title' => $method['title'],
                            'description' => $method['description'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Gói học
            if ($request->has('packages')) {
                foreach ($request->packages as $index => $package) {
                    if (!empty($package['name'])) {
                        InstructorPackage::create([
                            'instructor_id' => $instructor->id,
                            'name' => $package['name'],
                            'description' => $package['description'] ?? null,
                            'price' => $package['price'] ?? 0,
                            'sessions_count' => $package['sessions_count'] ?? null,
                            'discount_percent' => $package['discount_percent'] ?? 0,
                            'is_group' => isset($package['is_group']) ? 1 : 0,
                            'max_group_size' => $package['max_group_size'] ?? null,
                            'is_popular' => isset($package['is_popular']) ? 1 : 0,
                            'is_active' => isset($package['is_active']) ? 1 : 0,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Khu vực dạy
            if ($request->has('locations')) {
                foreach ($request->locations as $index => $location) {
                    if (!empty($location['district'])) {
                        InstructorLocation::create([
                            'instructor_id' => $instructor->id,
                            'district' => $location['district'],
                            'city' => $location['city'] ?? null,
                            'venues' => $location['venues'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Lịch dạy
            if ($request->has('schedules')) {
                foreach ($request->schedules as $index => $schedule) {
                    if (!empty($schedule['days'])) {
                        InstructorSchedule::create([
                            'instructor_id' => $instructor->id,
                            'days' => $schedule['days'],
                            'time_slots' => $schedule['time_slots'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.instructors.index')->with('success', 'Cập nhật giảng viên thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Instructor $instructor)
    {
        if ($instructor->image) {
            Storage::disk('public')->delete($instructor->image);
        }

        $instructor->delete();

        return redirect()->route('admin.instructors.index')->with('success', 'Xóa giảng viên thành công.');
    }
}
