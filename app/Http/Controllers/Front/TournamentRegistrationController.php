<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use Illuminate\Http\Request;

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

            // Create athlete registration with pending status
            TournamentAthlete::create([
                'tournament_id' => $tournament->id,
                'user_id' => auth()->check() ? auth()->id() : null,
                'athlete_name' => $validated['athlete_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'pending', // pending status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
