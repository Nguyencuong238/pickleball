<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\TournamentCategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TournamentRegistrationController extends Controller
{
    public function getCategories(Tournament $tournament): JsonResponse
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
            Log::error('Get categories error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu'
            ], 500);
        }
    }

    public function register(Request $request, Tournament $tournament): JsonResponse
    {
        try {
            // Verify category belongs to this tournament first
            $category = $tournament->categories()->find($request->category_id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu không tồn tại'
                ], 400);
            }

            $isDoubles = $category->isDoubles();

            // Build validation rules
            $rules = [
                'athlete_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'category_id' => 'required|exists:tournament_categories,id',
            ];

            // Add partner validation for doubles
            if ($isDoubles) {
                $rules['partner_name'] = 'required|string|max:255';
                $rules['partner_email'] = 'nullable|email|max:255';
                $rules['partner_phone'] = 'nullable|string|max:20';
            }

            $validated = $request->validate($rules);

            // Check if registration deadline has passed
            if ($tournament->registration_deadline <= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hạn đăng ký đã đóng'
                ], 400);
            }

            // Check if category is available
            if ($category->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu này đã đóng'
                ], 400);
            }

            // For doubles, we need 2 slots
            $requiredSlots = $isDoubles ? 2 : 1;
            $availableSlots = $category->max_participants - $category->current_participants;
            if ($availableSlots < $requiredSlots) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu này đã hết chỗ'
                ], 400);
            }

            // Check if tournament is full
            $currentCount = $tournament->athletes()->count();
            if (($currentCount + $requiredSlots) > $tournament->max_participants) {
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

            // For doubles, also check partner email if provided
            if ($isDoubles && !empty($validated['partner_email'])) {
                $existingPartner = TournamentAthlete::where('tournament_id', $tournament->id)
                    ->where('email', $validated['partner_email'])
                    ->first();

                if ($existingPartner) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email đồng đội đã được đăng ký cho giải đấu này'
                    ], 400);
                }
            }

            DB::transaction(function () use ($validated, $tournament, $category, $isDoubles) {
                // Update or create user record with registration data
                $user = User::where('email', $validated['email'])->first();

                if (! $user) {
                    $user = User::create([
                        'name' => $validated['athlete_name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                        'password' => Hash::make(Str::random(16)), // Random password
                    ]);
                } else {
                    // Update existing user info
                    $user->update([
                        'name' => $validated['athlete_name'],
                        'phone' => $validated['phone'],
                    ]);
                }
                
                // Create main athlete registration
                $athlete1 = TournamentAthlete::create([
                    'tournament_id' => $tournament->id,
                    'user_id' => $user->id,
                    'category_id' => $validated['category_id'],
                    'athlete_name' => $validated['athlete_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'status' => 'pending',
                ]);

                if ($isDoubles) {
                    // Create partner user if email provided
                    $partnerUser = null;
                    if (!empty($validated['partner_email'])) {
                        $partnerUser = User::where('email', $validated['partner_email'])->first();
                        if (! $partnerUser) {
                            $partnerUser = User::create([
                                'name' => $validated['partner_name'],
                                'email' => $validated['partner_email'],
                                'phone' => $validated['partner_phone'] ?? null,
                                'password' => Hash::make(Str::random(16)), // Random password
                            ]);
                        } else {
                            // Update existing partner info
                            $partnerUser->update([
                                'name' => $validated['partner_name'],
                                'phone' => $validated['partner_phone'] ?? null,
                            ]);
                        }   
                    }

                    // Create partner athlete registration
                    $athlete2 = TournamentAthlete::create([
                        'tournament_id' => $tournament->id,
                        'user_id' => $partnerUser?->id,
                        'category_id' => $validated['category_id'],
                        'athlete_name' => $validated['partner_name'],
                        'email' => $validated['partner_email'] ?? null,
                        'phone' => $validated['partner_phone'] ?? null,
                        'status' => 'pending',
                        'partner_id' => $athlete1->id,
                    ]);

                    // Link back - bidirectional relationship
                    $athlete1->update(['partner_id' => $athlete2->id]);

                    // Increment by 2 for doubles
                    $category->increment('current_participants', 2);
                } else {
                    // Increment by 1 for singles
                    $category->increment('current_participants');
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Tournament registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi. Vui lòng thử lại.'
            ], 500);
        }
    }
}
