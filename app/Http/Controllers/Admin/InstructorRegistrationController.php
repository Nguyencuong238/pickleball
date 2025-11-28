<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingInstructor;
use Illuminate\Http\Request;

class InstructorRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $registrations = BookingInstructor::with('instructor', 'package')
            ->latest()
            ->paginate(10);
        
        return view('admin.instructor-registrations.index', compact('registrations'));
    }

    public function destroy(BookingInstructor $instructor_registration)
    {
        $instructor_registration->delete();
        
        return redirect()->route('admin.instructor-registrations.index')
            ->with('success', 'Xóa đăng ký học thành công.');
    }
}
