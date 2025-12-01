<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstructorReview;
use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorReviewController extends Controller
{
    /**
     * Store a new review for an instructor
     */
    public function store(Request $request)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đánh giá giảng viên',
            ], 401);
        }

        // Validate input
        try {
            $validated = $request->validate([
                'instructor_id' => 'required|exists:instructors,id',
                'rating' => 'required|integer|between:1,5',
                'content' => 'nullable|string|max:1000',
                'tags' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = auth()->user();
        $instructor_id = $validated['instructor_id'];

        // Check if user already reviewed this instructor
        $existingReview = InstructorReview::where('user_id', $user->id)
            ->where('instructor_id', $instructor_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá giảng viên này rồi',
            ], 422);
        }

        try {
            // Process tags
            $tags = null;
            if (!empty($validated['tags'])) {
                if (is_string($validated['tags']) && strlen($validated['tags']) > 0) {
                    $tags = array_filter(array_map('trim', explode(',', $validated['tags'])));
                    $tags = !empty($tags) ? $tags : null;
                } elseif (is_array($validated['tags']) && count($validated['tags']) > 0) {
                    $tags = array_filter($validated['tags']);
                    $tags = !empty($tags) ? $tags : null;
                }
            }

            // Create review
            $review = InstructorReview::create([
                'instructor_id' => $instructor_id,
                'user_id' => $user->id,
                'rating' => $validated['rating'],
                'content' => $validated['content'] ?? null,
                'tags' => $tags,
                'is_approved' => true,
            ]);

            // Update instructor rating and review count
            $this->updateInstructorRating($instructor_id);

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá của bạn đã được gửi thành công!',
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'content' => $review->content,
                    'user_name' => $user->name,
                    'created_at' => $review->created_at->diffForHumans(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu đánh giá: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, InstructorReview $review)
    {
        // Check if user owns this review
        if ($review->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật đánh giá này',
            ], 403);
        }

        // Validate input
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'content' => 'nullable|string|max:1000',
            'tags' => 'nullable|string',
        ]);

        try {
            // Process tags
            $tags = null;
            if (!empty($validated['tags'])) {
                if (is_string($validated['tags']) && strlen($validated['tags']) > 0) {
                    $tags = array_filter(array_map('trim', explode(',', $validated['tags'])));
                    $tags = !empty($tags) ? $tags : null;
                } elseif (is_array($validated['tags']) && count($validated['tags']) > 0) {
                    $tags = array_filter($validated['tags']);
                    $tags = !empty($tags) ? $tags : null;
                }
            }

            // Update review
            $review->update([
                'rating' => $validated['rating'],
                'content' => $validated['content'] ?? null,
                'tags' => $tags,
            ]);

            // Update instructor rating
            $this->updateInstructorRating($review->instructor_id);

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá của bạn đã được cập nhật thành công!',
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'content' => $review->content,
                    'updated_at' => $review->updated_at->diffForHumans(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy(InstructorReview $review)
    {
        // Check if user owns this review
        if ($review->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa đánh giá này',
            ], 403);
        }

        $instructor_id = $review->instructor_id;
        
        try {
            $review->delete();

            // Update instructor rating
            $this->updateInstructorRating($instructor_id);

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá của bạn đã được xóa thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get reviews for an instructor with pagination
     */
    public function getByInstructor(Request $request, $instructorId)
    {
        $sort = $request->input('sort', 'recent'); // recent, helpful, rating_high, rating_low
        $perPage = $request->input('per_page', 10);

        $query = InstructorReview::where('instructor_id', $instructorId)
            ->where('is_approved', true)
            ->with('user');

        // Sort
        switch ($sort) {
            case 'rating_high':
                $query->orderBy('rating', 'desc')
                    ->orderBy('created_at', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc')
                    ->orderBy('created_at', 'desc');
                break;
            default: // recent
                $query->orderBy('created_at', 'desc');
        }

        $reviews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'has_next_page' => $reviews->hasMorePages(),
            ],
        ], 200);
    }

    /**
     * Update instructor's average rating and review count
     */
    private function updateInstructorRating($instructorId)
    {
        $instructor = Instructor::findOrFail($instructorId);
        
        $reviews = $instructor->reviews()->get();
        
        if ($reviews->count() > 0) {
            $averageRating = round($reviews->avg('rating'), 1);
            $reviewCount = $reviews->count();
            
            $instructor->update([
                'rating' => $averageRating,
                'reviews_count' => $reviewCount,
            ]);
        }
    }
}
