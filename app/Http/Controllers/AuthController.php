<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ---------- SHOW FORM ----------
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // ---------- REGISTER ----------
    public function register(Request $req)
    {
        $req->validate([
            'name'     => 'required|min:3',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);

        return back()->with('success', 'Đăng ký thành công! Đang chuyển sang trang đăng nhập...');
    }


    // ---------- LOGIN ----------
    public function login(Request $req)
    {
        $req->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($req->only('email', 'password'))) {
            $req->session()->regenerate();

            // Lấy user vừa login
            $user = auth()->user();

            // Redirect theo role
            if ($user->hasRole('admin')) {
                return redirect('/admin/dashboard');
            }

            // Redirect to user dashboard
            return redirect('/dashboard');
        }

        return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng!']);
    }


    // ---------- LOGOUT ----------
    public function logout(Request $req)
    {
        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return redirect('/');
    }

    // ---------- ADMIN LOGIN ----------
    public function showAdminLogin()
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $req)
    {
        $req->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($req->only('email', 'password'))) {
            $user = auth()->user();

            // Check if user is admin
            if (!$user->hasRole('admin')) {
                Auth::logout();
                return back()->withErrors(['email' => 'Tài khoản này không có quyền truy cập Admin!']);
            }

            $req->session()->regenerate();
            return redirect('/admin/dashboard');
        }

        return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng!']);
    }
}
