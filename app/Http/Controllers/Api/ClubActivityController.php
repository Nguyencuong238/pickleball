<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubActivityController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index(Club $club)
    {
        $this->authorize('view', $club);

        $query = $club->activities()->whereNull('parent_activity_id');

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($type = request('type')) {
            $query->where('type', $type);
        }

        $sort = request('sort', 'date_desc');
        $query->orderBy('activity_date', $sort === 'date_asc' ? 'asc' : 'desc');

        $activities = $query->withCount('confirmedParticipants')->paginate(10);

        return response()->json([
            'data' => $activities->items(),
            'pagination' => [
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created activity.
     */
    public function store(Request $request, Club $club)
    {
        $this->authorize('manageActivity', $club);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:one_off,recurring,competition',
            'activity_date' => 'required|date_format:Y-m-d\TH:i',
            'end_time' => 'nullable|date_format:H:i,H:i:s',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'required|integer|min:2|max:200',
            'status' => 'required|in:upcoming,completed,cancelled',
            'auto_approve' => 'boolean',
            'min_skill_level' => 'nullable|numeric|min:1.0|max:6.0',
            'max_skill_level' => 'nullable|numeric|min:1.0|max:6.0|gte:min_skill_level',
            'recurrence_day' => 'required_if:type,recurring|nullable|integer|min:0|max:6',
            'competition_config' => 'nullable|array',
            'competition_config.format' => 'nullable|in:round_robin,pool_play,single_elimination',
            'competition_config.points_for_win' => 'nullable|integer|min:0',
            'competition_config.points_for_loss' => 'nullable|integer|min:0',
            'fee_gems' => 'nullable|integer|min:1|max:10000',
            'best_of' => 'nullable|integer|in:1,3',
            'points_per_set' => 'nullable|integer|in:11,15,21',
        ]);

        $validated['created_by'] = Auth::id();

        $activity = $club->activities()->create($validated);

        // Auto-create linked post in club feed
        ClubPost::create([
            'club_id' => $club->id,
            'user_id' => Auth::id(),
            'club_activity_id' => $activity->id,
            'content' => $activity->buildPostContent(),
            'visibility' => 'public',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hoạt động được tạo thành công!',
            'data' => $activity,
        ], 201);
    }

    /**
     * Display the specified activity.
     */
    public function show(Club $club, ClubActivity $activity)
    {
        $this->authorize('view', $club);

        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        $activity->loadCount(['confirmedParticipants', 'waitlistedParticipants']);
        $activity->load(['confirmedParticipants.user', 'waitlistedParticipants.user']);

        $isManagement = Auth::check() && $club->isManagement(Auth::user());
        $isMember = Auth::check() && $club->isMember(Auth::user());
        $userParticipation = Auth::check()
            ? $activity->participants()->where('user_id', Auth::id())->first()
            : null;

        return response()->json([
            'data' => $activity,
            'is_management' => $isManagement,
            'is_member' => $isMember,
            'user_participation' => $userParticipation,
        ]);
    }

    /**
     * Update the specified activity.
     */
    public function update(Request $request, Club $club, ClubActivity $activity)
    {
        $this->authorize('manageActivity', $club);

        if ($activity->club_id !== $club->id) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'required|date_format:Y-m-d\TH:i',
            'end_time' => 'nullable|date_format:H:i,H:i:s',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'required|integer|min:2|max:200',
            'status' => 'required|in:upcoming,completed,cancelled',
            'auto_approve' => 'boolean',
            'min_skill_level' => 'nullable|numeric|min:1.0|max:6.0',
            'max_skill_level' => 'nullable|numeric|min:1.0|max:6.0|gte:min_skill_level',
            'fee_gems' => 'nullable|integer|min:1|max:10000',
            'recurrence_day' => 'nullable|integer|min:0|max:6',
            'competition_config' => 'nullable|array',
            'competition_config.format' => 'nullable|in:round_robin,pool_play,single_elimination',
            'competition_config.points_for_win' => 'nullable|integer|min:0',
            'competition_config.points_for_loss' => 'nullable|integer|min:0',
            'best_of' => 'nullable|integer|in:1,3',
            'points_per_set' => 'nullable|integer|in:11,15,21',
        ]);

        // Prevent type change after creation
        unset($validated['type']);

        // Không cho sửa fee khi đã có người đăng ký confirmed
        if (
            array_key_exists('fee_gems', $validated)
            && (int) ($validated['fee_gems'] ?? 0) !== (int) ($activity->fee_gems ?? 0)
            && !$activity->isFeeEditable()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thay đổi phí khi đã có người đăng ký.',
            ], 422);
        }

        $activity->update($validated);

        // Sync linked post content with updated activity
        if ($activity->post) {
            $activity->post->update([
                'content' => $activity->buildPostContent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hoạt động được cập nhật thành công!',
            'data' => $activity->fresh(),
        ]);
    }

    /**
     * Remove the specified activity.
     */
    public function destroy(Club $club, ClubActivity $activity)
    {
        $this->authorize('manageActivity', $club);

        if ($activity->club_id !== $club->id) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }

        // Block deletion if confirmed participants have paid gems
        if ($activity->hasFee() && $activity->confirmedParticipants()->whereNotNull('gem_transaction_id')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa hoạt động có người đã thanh toán Gems. Vui lòng hủy hoạt động thay vì xóa.',
            ], 422);
        }

        // Hard-delete linked post before activity deletion
        if ($activity->post) {
            $activity->post->forceDelete();
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hoạt động được xóa thành công!',
        ]);
    }
}
