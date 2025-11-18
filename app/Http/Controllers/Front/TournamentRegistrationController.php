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
    public function register(Request $request, Tournament $tournament)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'athlete_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
            ]);

            // Check if registration deadline has passed
            if ($tournament->registration_deadline <= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hạn đăng ký đã đóng'
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
            TournamentAthlete::create([
                'tournament_id' => $tournament->id,
                'user_id' => $user->id,
                'athlete_name' => $validated['athlete_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'pending',
            ]);

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
