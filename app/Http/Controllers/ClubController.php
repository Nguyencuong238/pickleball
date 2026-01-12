<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\Province;
use App\Models\User;
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
        
        return view('clubs.index', compact('clubs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::all();
        $users = User::where('id', '!=', Auth::id())->get();
        
        return view('clubs.create', compact('provinces', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
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
            $path = $request->file('image')->store('clubs', 'public');
            $club->update(['image' => $path]);
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('clubs/banners', 'public');
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

        return redirect()->route('clubs.show', $club)
            ->with('success', 'Câu lạc bộ/Nhóm được tạo thành công!');
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

        return view('clubs.show', compact('club', 'membership', 'canPost', 'managementTeam'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Club $club)
    {
        $this->authorize('update', $club);
        
        $provinces = Province::all();
        $users = User::where('id', '!=', Auth::id())->get();
        $selectedProvinces = $club->provinces->pluck('id')->toArray();
        $selectedMembers = $club->members->pluck('id')->toArray();
        
        return view('clubs.edit', compact('club', 'provinces', 'users', 'selectedProvinces', 'selectedMembers'));
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
            'image' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
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
            $path = $request->file('image')->store('clubs', 'public');
            $club->update(['image' => $path]);
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('clubs/banners', 'public');
            $club->update(['banner' => $path]);
        }

        $club->provinces()->sync($validated['provinces']);
        $club->members()->sync(array_merge([Auth::id()], $validated['members'] ?? []), false);

        return redirect()->route('clubs.show', $club)
            ->with('success', 'Câu lạc bộ/Nhóm được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club)
    {
        $this->authorize('delete', $club);
        
        $club->delete();
        
        return redirect()->route('clubs.index')
            ->with('success', 'Câu lạc bộ/Nhóm được xóa thành công!');
    }

    /**
     * Join request to club
     */
    public function requestJoin(Club $club)
    {
        if (Auth::id() === $club->user_id) {
            return redirect()->route('clubs.show', $club)
                ->with('error', 'Bạn là người tạo câu lạc bộ/nhóm!');
        }

        // Check if user is already a member
        if ($club->members()->where('user_id', Auth::id())->exists()) {
            return redirect()->route('clubs.show', $club)
                ->with('error', 'Bạn đã là thành viên của câu lạc bộ/nhóm này!');
        }

        // Check if request already exists
        $existingRequest = ClubJoinRequest::where([
            'club_id' => $club->id,
            'user_id' => Auth::id()
        ])->first();

        if ($existingRequest) {
            return redirect()->route('clubs.show', $club)
                ->with('error', 'Bạn đã gửi yêu cầu tham gia câu lạc bộ/nhóm này!');
        }

        // Create join request
        ClubJoinRequest::create([
            'club_id' => $club->id,
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]);

        return redirect()->route('clubs.show', $club)
            ->with('success', 'Yêu cầu tham gia đã được gửi!');
    }

    /**
     * Show join requests for club
     */
    public function joinRequests(Club $club)
    {
        $this->authorize('update', $club);

        $pendingRequests = $club->joinRequests()
            ->where('status', 'pending')
            ->with('user')
            ->get();

        $approvedRequests = $club->joinRequests()
            ->where('status', 'approved')
            ->with('user')
            ->get();

        $rejectedRequests = $club->joinRequests()
            ->where('status', 'rejected')
            ->with('user')
            ->get();

        return view('clubs.join-requests', compact('club', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Approve join request
     */
    public function approveJoinRequest(Club $club, ClubJoinRequest $joinRequest)
    {
        $this->authorize('update', $club);

        if ($joinRequest->club_id !== $club->id) {
            return redirect()->back()->with('error', 'Yêu cầu không hợp lệ!');
        }

        $joinRequest->update(['status' => 'approved']);

        // Add user to club members
        if (!$club->members()->where('user_id', $joinRequest->user_id)->exists()) {
            $club->members()->attach($joinRequest->user_id, ['role' => 'member']);
        }

        return redirect()->back()->with('success', 'Đã phê duyệt yêu cầu tham gia!');
    }

    /**
     * Reject join request
     */
    public function rejectJoinRequest(Club $club, ClubJoinRequest $joinRequest)
    {
        $this->authorize('update', $club);

        if ($joinRequest->club_id !== $club->id) {
            return redirect()->back()->with('error', 'Yêu cầu không hợp lệ!');
        }

        $joinRequest->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Đã từ chối yêu cầu tham gia!');
    }

    /**
     * Update member role (AJAX)
     */
    public function updateMemberRole(Request $request, Club $club, User $user)
    {
        $this->authorize('update', $club);

        $validated = $request->validate([
            'role' => 'required|in:admin,moderator,member'
        ]);

        // Cannot change creator's role
        $currentRole = $club->getMemberRole($user);
        if ($currentRole === 'creator') {
            return response()->json(['message' => 'Không thể thay đổi vai trò của chủ nhiệm'], 403);
        }

        // Check if user is a member
        if (!$club->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Người dùng không phải là thành viên'], 404);
        }

        $club->members()->updateExistingPivot($user->id, [
            'role' => $validated['role']
        ]);

        return response()->json(['success' => true, 'message' => 'Đã cập nhật vai trò']);
    }

    /**
     * Remove member from club (AJAX)
     */
    public function removeMember(Club $club, User $user)
    {
        $this->authorize('update', $club);

        // Cannot remove creator
        $memberRole = $club->getMemberRole($user);
        if ($memberRole === 'creator') {
            return response()->json(['message' => 'Không thể xóa chủ nhiệm khỏi CLB'], 403);
        }

        // Check if user is a member
        if (!$club->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Người dùng không phải là thành viên'], 404);
        }

        $club->members()->detach($user->id);

        return response()->json(['success' => true, 'message' => 'Đã xóa thành viên khỏi CLB']);
    }
}
