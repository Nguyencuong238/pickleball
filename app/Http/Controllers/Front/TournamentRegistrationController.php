<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TournamentRegistrationController extends Controller
{
    public function getCategories(Tournament $tournament)
    {
        try {
            $categories = $tournament->categories()
                ->orderBy('category_name')
                ->get();

            // Add actual athlete count to each category
            $categories = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'category_type' => $category->category_type,
                    'age_group' => $category->age_group,
                    'max_participants' => $category->max_participants,
                    'status' => $category->status,
                    'current_participants' => $category->athletes()->count()
                ];
            });

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Get categories error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu'
            ], 500);
        }
    }

    public function register(Request $request, Tournament $tournament)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'athlete_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'category_id' => 'required|exists:tournament_categories,id',
            ]);

            // Check if registration deadline has passed
            if ($tournament->registration_deadline <= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hạn đăng ký đã đóng'
                ], 400);
            }

            // Verify category belongs to this tournament
            $category = $tournament->categories()->find($validated['category_id']);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu không tồn tại'
                ], 400);
            }

            // Check if category is available
            if ($category->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu này đã đóng'
                ], 400);
            }

            if ($category->current_participants >= $category->max_participants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu này đã hết chỗ'
                ], 400);
            }

            // Check if tournament is full
            $currentCount = $tournament->athletes()->count();
            if ($currentCount >= $tournament->max_participants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giải đấu đã đầy. Không thể đăng ký thêm'
                ], 400);
            }

            // Check if athlete already registered with same email
            $existing = TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('email', $validated['email'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email này đã được đăng ký cho giải đấu này'
                ], 400);
            }

            // Update or create user record with registration data
            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['athlete_name'],
                    'phone' => $validated['phone'],
                ]
            );
            
            // If user is newly created, set a password
            if (!$user->password || $user->password === null) {
                $user->update(['password' => Hash::make(Str::random(16))]);
            }

            // Create athlete registration with pending status
            $athlete = TournamentAthlete::create([
                'tournament_id' => $tournament->id,
                'user_id' => $user->id,
                'category_id' => $validated['category_id'],
                'athlete_name' => $validated['athlete_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'pending',
                'registered_at' => now(),
            ]);

            // Increment current_participants in category
            $category->increment('current_participants');

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công'
            ]);

        } catch (\Exception $e) {
            \Log::error('Tournament registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi. Vui lòng thử lại.'
            ], 500);
        }
    }
}
