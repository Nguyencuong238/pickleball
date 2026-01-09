<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermissionRequest;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {
        $this->middleware('auth:api');
    }

    /**
     * Get user profile info
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'location' => $user->location,
                'province_id' => $user->province_id,
                'phone' => $user->phone ?? null,
                'has_password' => $this->profileService->hasPassword($user),
                'permission_request' => $this->getPermissionRequest($user),
                'referral_stats' => $user->getReferralStats(),
            ]
        ]);
    }

    /**
     * Update basic profile info
     */
    public function updateProfile(Request $request): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công!',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,webp,jpg|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ], [
            'avatar.image' => 'File phải là hình ảnh.',
            'avatar.mimes' => 'Chỉ chấp nhận file JPG, PNG, WebP.',
            'avatar.max' => 'Kích thước file tối đa là 2MB.',
        ]);

        $user = auth()->user();
        $remove = $request->boolean('remove_avatar');

        if ($remove) {
            $this->profileService->updateAvatar($user, null, true);
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ảnh đại diện!',
                'data' => $user->fresh()
            ]);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            if ($this->profileService->updateAvatar($user, $file)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật ảnh đại diện thành công!',
                    'data' => $user->fresh()
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật ảnh đại diện. Vui lòng thử lại.'
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Vui lòng chọn ảnh hoặc chọn remove_avatar để xóa.'
        ], 400);
    }

    /**
     * Update email
     */
    public function updateEmail(Request $request): JsonResponse
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
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật email thành công!',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request): JsonResponse
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
            return response()->json([
                'success' => true,
                'message' => 'Đặt mật khẩu thành công!',
                'data' => $user->fresh()
            ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Get user's permission request status
     */
    private function getPermissionRequest($user)
    {
        $request = PermissionRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$request) {
            return null;
        }

        return [
            'id' => $request->id,
            'role' => $request->role,
            'status' => $request->status,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ];
    }
}
