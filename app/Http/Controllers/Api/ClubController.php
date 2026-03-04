<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clubs = Club::with(['creator', 'members', 'provinces'])
            ->paginate(12);
 
        return response()->json([
            'data' => $clubs->items(),
            'pagination' => [
                'total' => $clubs->total(),
                'per_page' => $clubs->perPage(),
                'current_page' => $clubs->currentPage(),
                'last_page' => $clubs->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'founded_date' => 'required|date',
            'objectives' => 'nullable|string',
            'type' => 'required|in:club,group',
            'provinces' => 'required|array|min:1',
            'provinces.*' => 'exists:provinces,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        $club = Club::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'founded_date' => $validated['founded_date'],
            'objectives' => $validated['objectives'],
            'type' => $validated['type'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('clubs', config('filesystems.default'));
            $club->update(['image' => $path]);
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('clubs/banners', config('filesystems.default'));
            $club->update(['banner' => $path]);
        }

        // Add creator as member
        $club->members()->attach(Auth::id(), ['role' => 'creator']);

        // Add selected members
        if (!empty($validated['members'])) {
            $club->members()->attach($validated['members'], ['role' => 'member']);
        }

        // Add provinces
        $club->provinces()->attach($validated['provinces']);

        return response()->json([
            'success' => true,
            'message' => 'Câu lạc bộ/Nhóm được tạo thành công!',
            'data' => $club->load(['creator', 'members', 'provinces'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Club $club)
    {
        $club->load(['creator', 'members', 'provinces', 'activities']);

        $user = Auth::user();
        $membership = null;
        $canPost = false;

        if ($user) {
            $member = $club->members()->where('user_id', $user->id)->first();
            if ($member) {
                $membership = [
                    'role' => $member->pivot->role,
                    'joined_at' => $member->pivot->joined_at,
                ];
                $canPost = in_array($member->pivot->role, ['creator', 'admin', 'moderator']);
            }
        }

        // Get management team (creator, admin, moderator)
        $managementTeam = $club->members()
            ->whereIn('club_members.role', ['creator', 'admin', 'moderator'])
            ->get();

        return response()->json([
            'data' => $club,
            'membership' => $membership,
            'can_post' => $canPost,
            'management_team' => $managementTeam
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club)
    {
        $this->authorize('update', $club);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'founded_date' => 'required|date',
            'objectives' => 'nullable|string',
            'type' => 'required|in:club,group',
            'provinces' => 'required|array|min:1',
            'provinces.*' => 'exists:provinces,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        $club->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'founded_date' => $validated['founded_date'],
            'objectives' => $validated['objectives'],
            'type' => $validated['type'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('clubs', config('filesystems.default'));
            $club->update(['image' => $path]);
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('clubs/banners', config('filesystems.default'));
            $club->update(['banner' => $path]);
        }

        $club->provinces()->sync($validated['provinces']);
        $club->members()->sync(array_merge([Auth::id()], $validated['members'] ?? []), false);

        return response()->json([
            'success' => true,
            'message' => 'Câu lạc bộ/Nhóm được cập nhật thành công!',
            'data' => $club->load(['creator', 'members', 'provinces'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club)
    {
        $this->authorize('delete', $club);
        
        $club->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Câu lạc bộ/Nhóm được xóa thành công!'
        ]);
    }

    /**
     * Request to join club
     */
    public function requestJoin(Request $request, Club $club)
    {
        if (Auth::id() === $club->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn là người tạo câu lạc bộ/nhóm!'
            ], 403);
        }

        // Check if user is already a member
        if ($club->members()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã là thành viên của câu lạc bộ/nhóm này!'
            ], 400);
        }

        // Check if request already exists
        $existingRequest = ClubJoinRequest::where([
            'club_id' => $club->id,
            'user_id' => Auth::id()
        ])->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã gửi yêu cầu tham gia câu lạc bộ/nhóm này!'
            ], 400);
        }

        // Create join request
        $joinRequest = ClubJoinRequest::create([
            'club_id' => $club->id,
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu tham gia đã được gửi!',
            'data' => $joinRequest
        ], 201);
    }

    /**
     * Get join requests for club
     */
    public function joinRequests(Club $club)
    {
        $this->authorize('update', $club);

        $requests = $club->joinRequests()
            ->with('user')
            ->get()
            ->groupBy('status');

        return response()->json([
            'pending' => $requests->get('pending', collect()),
            'approved' => $requests->get('approved', collect()),
            'rejected' => $requests->get('rejected', collect()),
        ]);
    }

    /**
     * Approve join request
     */
    public function approveJoinRequest(Request $request, Club $club, ClubJoinRequest $joinRequest)
    {
        $this->authorize('update', $club);

        if ($joinRequest->club_id !== $club->id) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu không hợp lệ!'
            ], 400);
        }

        $joinRequest->update(['status' => 'approved']);

        // Add user to club members
        if (!$club->members()->where('user_id', $joinRequest->user_id)->exists()) {
            $club->members()->attach($joinRequest->user_id, ['role' => 'member']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã phê duyệt yêu cầu tham gia!',
            'data' => $joinRequest
        ]);
    }

    /**
     * Reject join request
     */
    public function rejectJoinRequest(Request $request, Club $club, ClubJoinRequest $joinRequest)
    {
        $this->authorize('update', $club);

        if ($joinRequest->club_id !== $club->id) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu không hợp lệ!'
            ], 400);
        }

        $joinRequest->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Đã từ chối yêu cầu tham gia!',
            'data' => $joinRequest
        ]);
    }

    /**
     * Update member role
     */
    public function updateMemberRole(Request $request, Club $club)
    {
        $this->authorize('update', $club);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,moderator,member'
        ]);

        // Cannot change creator's role
        $currentRole = $club->getMemberRole($validated['user_id']);
        if ($currentRole === 'creator') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thay đổi vai trò của chủ nhiệm'
            ], 403);
        }

        // Check if user is a member
        if (!$club->members()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không phải là thành viên'
            ], 404);
        }

        $club->members()->updateExistingPivot($validated['user_id'], [
            'role' => $validated['role']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật vai trò'
        ]);
    }

    /**
     * Remove member from club
     */
    public function removeMember(Request $request, Club $club)
    {
        $this->authorize('update', $club);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Cannot remove creator
        $memberRole = $club->getMemberRole($validated['user_id']);
        if ($memberRole === 'creator') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa chủ nhiệm khỏi CLB'
            ], 403);
        }

        // Check if user is a member
        if (!$club->members()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không phải là thành viên'
            ], 404);
        }

        $club->members()->detach($validated['user_id']);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa thành viên khỏi CLB'
        ]);
    }
}
