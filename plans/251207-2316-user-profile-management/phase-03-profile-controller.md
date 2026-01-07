# Phase 03: Profile Controller & Service

## Context Links

- Parent: [plan.md](./plan.md)
- Previous: [phase-02-user-model-update.md](./phase-02-user-model-update.md)
- Docs: [code-standards.md](../../docs/code-standards.md)

## Overview

**Date**: 2025-12-07
**Description**: Create ProfileController with edit/update methods and ProfileService for business logic
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Key Insights

- Follow existing controller pattern in Front/ directory
- Service layer for complex operations (password hashing, file upload)
- Separate update methods: profile info, avatar, password
- Current password verification required for sensitive changes
- Use Laravel Storage facade for avatar upload (public disk)

## Requirements

### Functional
- Show profile edit form
- Update basic info (name, location, province)
- Update avatar (upload new, remove) using Laravel Storage
- Update email (with password confirmation)
- Update password (with current password verification)

### Non-Functional
- Vietnamese flash messages
- Follow existing service patterns (OprsService, EloService)
- Type hints for parameters and returns

## Architecture

```
ProfileController
├── edit() -> Show edit form
├── updateProfile() -> Update name, location, province
├── updateAvatar() -> Upload/remove avatar (Laravel Storage)
├── updateEmail() -> Change email (requires password)
└── updatePassword() -> Change password (requires current)

ProfileService
├── updateBasicInfo(User, array) -> bool
├── updateAvatar(User, UploadedFile|null, bool) -> bool
├── updateEmail(User, string, string) -> bool
├── updatePassword(User, string, string) -> bool
└── verifyPassword(User, string) -> bool
```

## Related Code Files

### Files to Create
| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Front/ProfileController.php` | Create | Profile management controller |
| `app/Services/ProfileService.php` | Create | Profile business logic |

## Implementation Steps

### Step 1: Create ProfileService

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Update basic profile info (name, location, province)
     */
    public function updateBasicInfo(User $user, array $data): bool
    {
        return $user->update([
            'name' => $data['name'],
            'location' => $data['location'] ?? null,
            'province_id' => $data['province_id'] ?? null,
        ]);
    }

    /**
     * Update user avatar using Laravel Storage
     *
     * @param User $user
     * @param UploadedFile|null $file New avatar file
     * @param bool $remove Whether to remove current avatar
     * @return bool
     */
    public function updateAvatar(User $user, ?UploadedFile $file, bool $remove = false): bool
    {
        // Remove current avatar if exists
        if ($remove || $file !== null) {
            $this->deleteCurrentAvatar($user);
        }

        // If only removing, update user and return
        if ($remove && $file === null) {
            return $user->update(['avatar' => null]);
        }

        // Upload new avatar
        if ($file !== null) {
            $path = $file->store('avatars', 'public');

            if ($path === false) {
                return false;
            }

            return $user->update(['avatar' => $path]);
        }

        return true;
    }

    /**
     * Delete current avatar file from storage
     */
    private function deleteCurrentAvatar(User $user): void
    {
        if (!empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
    }

    /**
     * Update user email (requires password verification)
     */
    public function updateEmail(User $user, string $newEmail, string $currentPassword): bool
    {
        if (!$this->verifyPassword($user, $currentPassword)) {
            return false;
        }

        return $user->update(['email' => $newEmail]);
    }

    /**
     * Update user password
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!$this->verifyPassword($user, $currentPassword)) {
            return false;
        }

        return $user->update(['password' => Hash::make($newPassword)]);
    }

    /**
     * Verify user's current password
     */
    public function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }
}
```

### Step 2: Create ProfileController

```php
<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Services\ProfileService;
use Illuminate\Http\Request;

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

        return view('user.profile.edit', compact('user', 'provinces'));
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
            'name.required' => 'Vui long nhap ten.',
            'name.min' => 'Ten phai co it nhat 3 ky tu.',
            'location.max' => 'Dia chi khong duoc vuot qua 255 ky tu.',
            'province_id.exists' => 'Tinh/thanh pho khong hop le.',
        ]);

        $user = auth()->user();
        $this->profileService->updateBasicInfo($user, $validated);

        return back()->with('success', 'Cap nhat thong tin thanh cong!');
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ], [
            'avatar.image' => 'File phai la hinh anh.',
            'avatar.mimes' => 'Chi chap nhan file JPG, PNG, WebP.',
            'avatar.max' => 'Kich thuoc file toi da la 2MB.',
        ]);

        $user = auth()->user();
        $remove = $request->boolean('remove_avatar');
        $file = $request->file('avatar');

        if ($remove) {
            $this->profileService->updateAvatar($user, null, true);
            return back()->with('success', 'Da xoa anh dai dien!');
        }

        if ($file) {
            if ($this->profileService->updateAvatar($user, $file)) {
                return back()->with('success', 'Cap nhat anh dai dien thanh cong!');
            }
            return back()->with('error', 'Khong the cap nhat anh dai dien. Vui long thu lai.');
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
            'email.required' => 'Vui long nhap email.',
            'email.email' => 'Email khong hop le.',
            'email.unique' => 'Email nay da duoc su dung.',
            'current_password_email.required' => 'Vui long nhap mat khau hien tai.',
        ]);

        if (!$this->profileService->updateEmail($user, $validated['email'], $validated['current_password_email'])) {
            return back()->withErrors(['current_password_email' => 'Mat khau hien tai khong dung.']);
        }

        return back()->with('success', 'Cap nhat email thanh cong!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Vui long nhap mat khau hien tai.',
            'password.required' => 'Vui long nhap mat khau moi.',
            'password.min' => 'Mat khau moi phai co it nhat 6 ky tu.',
            'password.confirmed' => 'Xac nhan mat khau khong khop.',
        ]);

        $user = auth()->user();

        if (!$this->profileService->updatePassword($user, $validated['current_password'], $validated['password'])) {
            return back()->withErrors(['current_password' => 'Mat khau hien tai khong dung.']);
        }

        return back()->with('success', 'Doi mat khau thanh cong!');
    }
}
```

## Todo List

- [ ] Create ProfileService.php
- [ ] Create ProfileController.php
- [ ] Implement updateBasicInfo method
- [ ] Implement updateAvatar method with Storage facade
- [ ] Implement updateEmail method
- [ ] Implement updatePassword method
- [ ] Implement verifyPassword helper

## Success Criteria

- [ ] ProfileService created with all methods
- [ ] ProfileController created with all endpoints
- [ ] Current password required for email change
- [ ] Current password required for password change
- [ ] Avatar upload works with Laravel Storage (public disk)
- [ ] Old avatar deleted when uploading new one
- [ ] Avatar removal deletes file and clears column
- [ ] All validation messages in Vietnamese

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|------------|
| Password verification fails for OAuth users | Medium | Medium | OAuth users get random password, need special handling |
| Storage disk not configured | Low | Medium | Use default public disk |
| File permission issues | Low | Medium | Ensure storage/app/public writable |

## Security Considerations

- Password verification before sensitive changes (email, password)
- Password never exposed in responses
- Avatar file validation (mime types, max size)
- CSRF protection via middleware
- Files stored in public disk with random names

## Next Steps

- Proceed to Phase 04: Profile Edit View
