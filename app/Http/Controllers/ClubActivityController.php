<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubActivityController extends Controller
{
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

        if (request()->ajax()) {
            return response()->json([
                'activities' => $activities->items(),
                'hasMore' => $activities->hasMorePages(),
            ]);
        }

        $isManagement = Auth::check() && $club->isManagement(Auth::user());

        return view('clubs.activities.index', compact('club', 'activities', 'isManagement'));
    }

    public function create(Club $club)
    {
        $this->authorize('manageActivity', $club);

        return view('clubs.activities.create', compact('club'));
    }

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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hoạt động được tạo thành công!',
                'activity' => $activity,
            ]);
        }

        return redirect()->route('clubs.activities.index', $club)
            ->with('success', 'Hoạt động được tạo thành công!');
    }

    public function show(Club $club, ClubActivity $activity)
    {
        $this->authorize('view', $club);

        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        $activity->loadCount(['confirmedParticipants', 'waitlistedParticipants']);
        $activity->load(['confirmedParticipants.user', 'waitlistedParticipants.user', 'creator', 'parent']);

        $isManagement = Auth::check() && $club->isManagement(Auth::user());
        $isMember = Auth::check() && $club->isMember(Auth::user());
        $userParticipation = Auth::check()
            ? $activity->participants()->where('user_id', Auth::id())->first()
            : null;

        return view('clubs.activities.show', compact(
            'club', 'activity', 'isManagement', 'isMember', 'userParticipation'
        ));
    }

    public function edit(Club $club, ClubActivity $activity)
    {
        $this->authorize('manageActivity', $club);

        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        return view('clubs.activities.edit', compact('club', 'activity'));
    }

    public function update(Request $request, Club $club, ClubActivity $activity)
    {
        $this->authorize('manageActivity', $club);

        if ($activity->club_id !== $club->id) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Không tìm thấy'], 404);
            }
            abort(404);
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
            'recurrence_day' => 'nullable|integer|min:0|max:6',
            'competition_config' => 'nullable|array',
            'competition_config.format' => 'nullable|in:round_robin,pool_play,single_elimination',
            'competition_config.points_for_win' => 'nullable|integer|min:0',
            'competition_config.points_for_loss' => 'nullable|integer|min:0',
        ]);

        // Prevent type change after creation
        unset($validated['type']);

        $activity->update($validated);

        // Sync linked post content with updated activity
        if ($activity->post) {
            $activity->post->update([
                'content' => $activity->buildPostContent(),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hoạt động được cập nhật thành công!',
                'activity' => $activity->fresh(),
            ]);
        }

        return redirect()->route('clubs.activities.index', $club)
            ->with('success', 'Hoạt động được cập nhật thành công!');
    }

    public function generateQr(Club $club, ClubActivity $activity)
    {
        $this->authorize('manageActivity', $club);

        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        $qrCode = $activity->qr_code ?: $activity->generateQrCode();
        $checkinUrl = route('club.activity.checkin', [$club->slug, $activity->id]) . '?token=' . $qrCode;

        return response()->json([
            'success' => true,
            'qr_code' => $qrCode,
            'checkin_url' => $checkinUrl,
        ]);
    }

    public function destroy(Request $request, Club $club, ClubActivity $activity)
    {
        $this->authorize('manageActivity', $club);

        if ($activity->club_id !== $club->id) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Không tìm thấy'], 404);
            }
            abort(404);
        }

        // Hard-delete linked post before activity deletion
        if ($activity->post) {
            $activity->post->forceDelete();
        }

        $activity->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hoạt động được xóa thành công!',
            ]);
        }

        return redirect()->route('clubs.activities.index', $club)
            ->with('success', 'Hoạt động được xóa thành công!');
    }

}
