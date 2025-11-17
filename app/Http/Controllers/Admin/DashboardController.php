<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $totalUsers = User::count();
        $adminUsers = User::role('admin')->count();
        $normalUsers = User::role('user')->count();
        $homeYardUsers = User::role('home_yard')->count();
        $users = User::all();
        $roles = Role::all();

        return view('admin.dashboard', compact(
            'totalUsers',
            'adminUsers',
            'normalUsers',
            'homeYardUsers',
            'users',
            'roles'
        ));
    }
}
