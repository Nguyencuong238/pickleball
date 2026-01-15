<?php

namespace App\Services;

use App\Models\SocialProfileVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SocialVerificationService
{
    /**
     * Allowed platforms for verification
     */
    public const PLATFORMS = ['facebook', 'youtube', 'tiktok'];

    /**
     * Create or update social profile verification
     *
     * @throws InvalidArgumentException
     */
    public function verifyProfile(User $user, string $platform, string $url, User $admin): SocialProfileVerification
    {
        // Validate platform
        if (!in_array($platform, self::PLATFORMS)) {
            throw new InvalidArgumentException("Invalid platform: {$platform}");
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL format');
        }

        // Check if URL is already taken by another user
        if (SocialProfileVerification::isProfileUrlTaken($url, $user->id)) {
            throw new InvalidArgumentException('This profile URL is already verified by another user');
        }

        // Check if user already has this platform verified
        $existing = SocialProfileVerification::where('user_id', $user->id)
            ->where('platform', $platform)
            ->first();

        if ($existing) {
            // Update existing verification
            $existing->update([
                'profile_url' => $url,
                'verified_at' => Carbon::now(),
                'verified_by' => $admin->id,
            ]);
            return $existing;
        }

        // Create new verification
        return SocialProfileVerification::create([
            'user_id' => $user->id,
            'platform' => $platform,
            'profile_url' => $url,
            'verified_at' => Carbon::now(),
            'verified_by' => $admin->id,
        ]);
    }

    /**
     * Check if user has verified a platform
     */
    public function isProfileVerified(User $user, string $platform): bool
    {
        return SocialProfileVerification::hasVerifiedPlatform($user->id, $platform);
    }

    /**
     * Check if URL is available (not taken)
     */
    public function isUrlAvailable(string $url, ?int $excludeUserId = null): bool
    {
        return !SocialProfileVerification::isProfileUrlTaken($url, $excludeUserId);
    }

    /**
     * Get user's verified social profiles
     */
    public function getUserVerifications(User $user): Collection
    {
        return SocialProfileVerification::where('user_id', $user->id)->get();
    }

    /**
     * Get verification by platform for user
     */
    public function getVerification(User $user, string $platform): ?SocialProfileVerification
    {
        return SocialProfileVerification::where('user_id', $user->id)
            ->where('platform', $platform)
            ->first();
    }

    /**
     * Get all verified platforms for user
     *
     * @return array<string, bool>
     */
    public function getVerificationStatus(User $user): array
    {
        $verifications = $this->getUserVerifications($user)->keyBy('platform');

        return [
            'facebook' => $verifications->has('facebook'),
            'youtube' => $verifications->has('youtube'),
            'tiktok' => $verifications->has('tiktok'),
        ];
    }

    /**
     * Remove verification (admin action)
     */
    public function removeVerification(User $user, string $platform): bool
    {
        return SocialProfileVerification::where('user_id', $user->id)
            ->where('platform', $platform)
            ->delete() > 0;
    }

    /**
     * Get platform label for display
     */
    public function getPlatformLabel(string $platform): string
    {
        return match ($platform) {
            SocialProfileVerification::PLATFORM_FACEBOOK => 'Facebook',
            SocialProfileVerification::PLATFORM_YOUTUBE => 'YouTube',
            SocialProfileVerification::PLATFORM_TIKTOK => 'TikTok',
            default => ucfirst($platform),
        };
    }
}
