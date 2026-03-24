<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\User;
use App\Services\ClubActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubCheckinController extends Controller
{
    public function __construct(private ClubActivityService $activityService)
    {
    }

    public function show(Club $club, ClubActivity $activity, Request $request): View
    {
        $this->validateCheckinAccess($club, $activity, $request);

        return view('front.clubs.checkin', [
            'club' => $club,
            'activity' => $activity,
        ]);
    }

    public function lookup(Club $club, ClubActivity $activity, Request $request): JsonResponse
    {
        $this->validateCheckinAccess($club, $activity, $request);

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9}$/'],
        ]);

        $phone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json(['found' => false, 'user' => null]);
        }

        return response()->json([
            'found' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'oprs_level' => $user->opr_level,
            ],
        ]);
    }

    public function confirm(Club $club, ClubActivity $activity, Request $request): JsonResponse
    {
        $this->validateCheckinAccess($club, $activity, $request);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $participant = $this->activityService->checkinByPhone($activity, $user);

        // Store user_id in session for queue access
        session(['checkin_user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'redirect' => route('club.activity.queue', [$club->slug, $activity->id]),
        ]);
    }

    public function register(Club $club, ClubActivity $activity, Request $request): JsonResponse
    {
        $this->validateCheckinAccess($club, $activity, $request);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'gender' => 'nullable|in:male,female',
        ]);

        $phone = $this->normalizePhone($request->phone);

        // Check if user already exists
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $phone,
                'email' => 'guest_' . $phone . '@onepickleball.vn',
                'password' => bcrypt(str()->random(16)),
                'gender' => $request->gender,
            ]);
        }

        $participant = $this->activityService->checkinByPhone($activity, $user);

        session(['checkin_user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'redirect' => route('club.activity.queue', [$club->slug, $activity->id]),
        ]);
    }

    private function validateCheckinAccess(Club $club, ClubActivity $activity, Request $request): void
    {
        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        if (!$activity->isOpenPlay()) {
            abort(404, 'Hoạt động này không hỗ trợ check-in.');
        }

        $token = $request->query('token', $request->input('token'));
        if (!$token || $token !== $activity->qr_code) {
            abort(403, 'Mã QR không hợp lệ.');
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        if (str_starts_with($phone, '+84')) {
            $phone = '0' . substr($phone, 3);
        }

        return $phone;
    }
}
