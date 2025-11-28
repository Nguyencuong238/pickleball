<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BookingInstructor;
use App\Models\InstructorPackage;
use Illuminate\Http\Request;

class BookingInstructorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'package_id' => 'required|exists:instructor_packages,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'notes' => 'nullable|string',
        ]);

        try {
            $booking = BookingInstructor::create([
                'instructor_id' => $validated['instructor_id'],
                'package_id' => $validated['package_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt lịch thành công! Chúng tôi sẽ liên hệ bạn trong thời gian sớm nhất.',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
