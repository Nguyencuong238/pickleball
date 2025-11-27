<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ---------- SHOW FORM ----------
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.login');
    }

    // ---------- REGISTER ----------
    public function register(Request $req)
    {
        $req->validate([
            'name'      => 'required|min:3',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|regex:/^\d{10,11}$/',
            'password'  => 'required|min:6|confirmed',
            'role_type' => 'required|in:user,court_owner',
            'terms'     => 'required'
        ], [
            'email.unique' => 'Email này đã được sử dụng. Vui lòng chọn email khác.',
            'phone.regex' => 'Số điện thoại phải gồm 10 hoặc 11 chữ số.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'role_type.required' => 'Vui lòng chọn loại tài khoản.',
            'terms.required' => 'Bạn phải chấp nhận Điều khoản dịch vụ.'
        ]);

        User::create([
            'name' => $req->name,
            'email' => $req->email,
            'phone' => $req->phone,
            'password' => Hash::make($req->password),
            'role_type' => $req->role_type,
            'status' => 'pending',
        ]);

        return redirect('/admin/users')->with('success', 'Đăng ký thành công! Tài khoản của bạn đang chờ duyệt.');
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

            if ($user->hasRole('home_yard')) {
                return redirect(route('homeyard.overview'));
            }

            // Redirect to user dashboard
            return redirect('/user/dashboard');
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

    // ---------- GOOGLE OAUTH ----------
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Không thể kết nối với Google. Vui lòng thử lại.']);
        }

        // Find or create user
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(str()->random(24)), // Generate random password for OAuth users
                'role_type' => 'user',
                'status' => 'approved',
            ]);
        } else {
            // Update google_id if user exists but doesn't have it
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        }

        // Log the user in
        Auth::login($user);

        // Redirect based on user role
        if ($user->hasRole('admin')) {
            return redirect('/admin/dashboard');
        }

        if ($user->hasRole('home_yard')) {
            return redirect(route('homeyard.overview'));
        }

        return redirect('/user/dashboard');
    }

    // ---------- FACEBOOK OAUTH ----------
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Không thể kết nối với Facebook. Vui lòng thử lại.']);
        }

        // Find or create user
        $user = User::where('email', $facebookUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $facebookUser->getName(),
                'email' => $facebookUser->getEmail(),
                'facebook_id' => $facebookUser->getId(),
                'password' => Hash::make(str()->random(24)), // Generate random password for OAuth users
                'role_type' => 'user',
                'status' => 'approved',
            ]);
        } else {
            // Update facebook_id if user exists but doesn't have it
            if (!$user->facebook_id) {
                $user->update(['facebook_id' => $facebookUser->getId()]);
            }
        }

        // Log the user in
        Auth::login($user);

        // Redirect based on user role
        if ($user->hasRole('admin')) {
            return redirect('/admin/dashboard');
        }

        if ($user->hasRole('home_yard')) {
            return redirect(route('homeyard.overview'));
        }

        return redirect('/user/dashboard');
    }
}
