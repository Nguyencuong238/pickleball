<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PermissionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = PermissionRequest::with('user', 'reviewer');

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get counts for each status
        $pendingCount = PermissionRequest::where('status', 'pending')->count();
        $approvedCount = PermissionRequest::where('status', 'approved')->count();
        $rejectedCount = PermissionRequest::where('status', 'rejected')->count();

        return view('admin.permission-requests.index', compact('requests', 'status', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    public function show(PermissionRequest $permissionRequest)
    {
        $permissionRequest->load('user', 'reviewer');
        return view('admin.permission-requests.show', compact('permissionRequest'));
    }

    public function approve(Request $request, PermissionRequest $permissionRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $user = $permissionRequest->user;

        // Assign roles based on requested permissions
        foreach ($permissionRequest->permissions as $permission) {
            $role = $this->getRole($permission);
            if ($role) {
                $user->assignRole($role);
            }
        }

        // Update permission request
        $permissionRequest->update([
            'status' => 'approved',
            'admin_notes' => $request->input('admin_notes'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);

        return redirect()->route('admin.permission-requests.index')
            ->with('success', 'Yêu cầu cấp quyền đã được phê duyệt.');
    }

    public function reject(Request $request, PermissionRequest $permissionRequest)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000'
        ]);

        $permissionRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);

        return redirect()->route('admin.permission-requests.index')
            ->with('success', 'Yêu cầu cấp quyền đã bị từ chối.');
    }

    private function getRole(string $permission)
     {
         return match($permission) {
             'home_yard' => Role::findByName('home_yard'),
             'referee' => Role::findByName('referee'),
             default => null
         };
     }
}
