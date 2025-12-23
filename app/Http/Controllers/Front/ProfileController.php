<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\PermissionRequest;
use App\Models\Province;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {
        $this->middleware('auth');
    }

    /**
     * Show profile edit form
     */
    public function edit()
    {
        $user = auth()->user();
        $provinces = Province::orderBy('name')->get();
        $hasPassword = $this->profileService->hasPassword($user);
        
        // Get user's permission request status
        $permissionRequest = PermissionRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        // Get referral stats
        $referralStats = $user->getReferralStats();
        
        // Get referral details
        $referralDetails = $user->referrals()->with('referredUser')->latest()->get();

        return view('user.profile.edit', compact('user', 'provinces', 'hasPassword', 'permissionRequest', 'referralStats', 'referralDetails'));
    }

    /**
     * Update basic profile info
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'location' => 'nullable|string|max:255',
            'province_id' => 'nullable|exists:provinces,id',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'name.min' => 'Tên phải có ít nhất 3 ký tự.',
            'location.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'province_id.exists' => 'Tỉnh/thành phố không hợp lệ.',
        ]);

        $user = auth()->user();
        $this->profileService->updateBasicInfo($user, $validated);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,webp|max:2048|dimensions:max_width=2000,max_height=2000',
            'remove_avatar' => 'nullable|boolean',
        ], [
            'avatar.image' => 'File phải là hình ảnh.',
            'avatar.mimes' => 'Chỉ chấp nhận file JPG, PNG, WebP.',
            'avatar.max' => 'Kích thước file tối đa là 2MB.',
            'avatar.dimensions' => 'Kích thước hình ảnh tối đa là 2000x2000 pixels.',
        ]);

        $user = auth()->user();
        $remove = $request->boolean('remove_avatar');
        $file = $request->file('avatar');

        if ($remove) {
            $this->profileService->updateAvatar($user, null, true);
            return back()->with('success', 'Đã xóa ảnh đại diện!');
        }

        if ($file) {
            if ($this->profileService->updateAvatar($user, $file)) {
                return back()->with('success', 'Cập nhật ảnh đại diện thành công!');
            }
            return back()->with('error', 'Không thể cập nhật ảnh đại diện. Vui lòng thử lại.');
        }

        return back();
    }

    /**
     * Update email
     */
    public function updateEmail(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password_email' => 'required|string',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'current_password_email.required' => 'Vui lòng nhập mật khẩu hiện tại.',
        ]);

        if (!$this->profileService->updateEmail($user, $validated['email'], $validated['current_password_email'])) {
            return back()->withErrors(['current_password_email' => 'Mật khẩu hiện tại không đúng.']);
        }

        return back()->with('success', 'Cập nhật email thành công!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        // OAuth users without password can set new password without current password
        if (!$this->profileService->hasPassword($user)) {
            $validated = $request->validate([
                'password' => 'required|string|min:6|confirmed',
            ], [
                'password.required' => 'Vui lòng nhập mật khẩu mới.',
                'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            ]);

            $user->update(['password' => Hash::make($validated['password'])]);
            return back()->with('success', 'Đặt mật khẩu thành công!');
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if (!$this->profileService->updatePassword($user, $validated['current_password'], $validated['password'])) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
