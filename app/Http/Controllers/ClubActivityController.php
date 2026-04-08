<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubPost;
use App\Services\GemWalletService;
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

        $statusCounts = $club->activities()->whereNull('parent_activity_id')
            ->selectRaw("status, count(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('clubs.activities.index', compact('club', 'activities', 'isManagement', 'statusCounts'));
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
            'type' => 'required|in:one_off,recurring,competition,open_play',
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
            // Open play fields
            'courts_count' => 'required_if:type,open_play|nullable|integer|min:1|max:20',
            'avg_match_duration' => 'nullable|integer|min:5|max:60',
            'rotation_mode' => 'nullable|in:round_robin,oprs_based,random',
            'oprs_weight' => 'nullable|numeric|min:0|max:1',
            'best_of' => 'nullable|integer|in:1,3',
            'points_per_set' => 'nullable|integer|in:11,15,21',
        ]);

        $validated['created_by'] = Auth::id();

        $activity = $club->activities()->create($validated);

        // Auto-generate QR code for open_play activities
        if ($activity->isOpenPlay()) {
            $activity->generateQrCode();
        }

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

        $userGemBalance = Auth::check()
            ? app(GemWalletService::class)->getBalance(Auth::user())
            : 0;
        $exchangeRate = config('gems.exchange_rate');

        return view('clubs.activities.show', compact(
            'club', 'activity', 'isManagement', 'isMember', 'userParticipation',
            'userGemBalance', 'exchangeRate'
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

        // Khong cho sua fee khi da co nguoi dang ky confirmed
        if (
            array_key_exists('fee_gems', $validated)
            && (int) ($validated['fee_gems'] ?? 0) !== (int) ($activity->fee_gems ?? 0)
            && !$activity->isFeeEditable()
        ) {
            $error = 'Không thể thay đổi phí khi đã có người đăng ký.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return back()->withErrors(['fee_gems' => $error]);
        }

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

        // Block deletion if confirmed participants have paid gems
        if ($activity->hasFee() && $activity->confirmedParticipants()->whereNotNull('gem_transaction_id')->exists()) {
            $error = 'Không thể xóa hoạt động có người đã thanh toán Gems. Vui lòng hủy hoạt động thay vì xóa.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return back()->withErrors(['delete' => $error]);
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
