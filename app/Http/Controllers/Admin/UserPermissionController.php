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
    public function index()
    {
        $users = User::all();
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
        ]);

        $user->syncRoles($validated['roles']);

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
}
