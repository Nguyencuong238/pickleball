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
        if (empty($user->password)) {
            return false;
        }
        return Hash::check($password, $user->password);
    }

    /**
     * Check if user has password set (for OAuth users)
     */
    public function hasPassword(User $user): bool
    {
        return !empty($user->password);
    }
}
