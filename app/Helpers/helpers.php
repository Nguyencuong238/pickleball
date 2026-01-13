<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('storage_url')) {
    /**
     * Get the URL for a file stored in the default filesystem disk.
     * Works with both local storage and cloud storage (S3, Digital Ocean Spaces, etc.)
     *
     * @param string|null $path
     * @return string|null
     */
    function storage_url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk(config('filesystems.default'))->url($path);
    }
}
