<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    // List all users with their roles
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search by name
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }
        
        // Filter by role_type
        if ($request->has('role_type') && !empty($request->input('role_type'))) {
            $roleType = $request->input('role_type');
            $query->where('role_type', $roleType);
        }
        
        // Filter by roles - user only (has user role but not other roles)
        if ($request->has('role_filter') && $request->input('role_filter') === 'user_only') {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'user');
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', '!=', 'user');
            });
        }
        
        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    // Show form to edit user permissions
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    // Update user permissions
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'athlete_types' => 'nullable|array',
            'athlete_types.*' => 'in:athlete_international,athlete_vietnam',
        ]);

        $user->syncRoles($validated['roles']);
        $user->update(['athlete_types' => $validated['athlete_types'] ?? null]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User permissions updated successfully');
    }

    // Approve user registration
    public function approve(User $user)
    {
        $user->update(['status' => 'approved']);

        // Auto-assign role based on role_type
        if ($user->role_type === 'court_owner') {
            $user->assignRole('home_yard');
        } else {
            $user->assignRole('user');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Tài khoản {$user->name} đã được duyệt thành công!");
    }

    // Reject user registration
    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Tài khoản {$user->name} đã bị từ chối!");
    }

    // Delete user
    public function destroy(User $user)
    {
        $userName = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Người dùng {$userName} đã được xóa thành công!");
    }
}
