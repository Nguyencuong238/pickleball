<?php

namespace App\Services;

use App\Models\ClubPost;
use App\Models\ClubPostMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClubPostMediaService
{
    private string $disk;

    public function __construct()
    {
        $this->disk = config('club_posts.disk', 'public');
    }

    /**
     * Handle media upload for a post
     */
    public function handleUpload(ClubPost $post, array $files, string $type): void
    {
        $order = 0;
        foreach ($files as $file) {
            $path = $this->storeFile($file, $type);

            ClubPostMedia::create([
                'club_post_id' => $post->id,
                'type' => $type,
                'path' => $path,
                'disk' => $this->disk,
                'size' => $file->getSize(),
                'order' => $order++,
            ]);
        }
    }

    /**
     * Handle YouTube URL
     */
    public function handleYoutube(ClubPost $post, string $url): void
    {
        ClubPostMedia::create([
            'club_post_id' => $post->id,
            'type' => 'youtube',
            'youtube_url' => $url,
            'disk' => $this->disk,
            'order' => 0,
        ]);
    }

    /**
     * Store uploaded file
     */
    private function storeFile(UploadedFile $file, string $type): string
    {
        $folder = $type === 'image' ? 'club-posts/images' : 'club-posts/videos';
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $filename, $this->disk);
    }

    /**
     * Delete all media for a post
     */
    public function deletePostMedia(ClubPost $post): void
    {
        foreach ($post->media as $media) {
            $this->deleteMedia($media);
        }
    }

    /**
     * Delete single media item
     */
    public function deleteMedia(ClubPostMedia $media): void
    {
        if ($media->path && Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }
        $media->delete();
    }

    /**
     * Update post media (delete old, add new)
     */
    public function updatePostMedia(ClubPost $post, ?array $files, ?string $type, ?string $youtubeUrl, array $keepMediaIds = []): void
    {
        // Delete media not in keepMediaIds
        foreach ($post->media as $media) {
            if (!in_array($media->id, $keepMediaIds)) {
                $this->deleteMedia($media);
            }
        }

        // Add new media
        if ($files && $type && $type !== 'youtube') {
            $this->handleUpload($post, $files, $type);
        } elseif ($youtubeUrl) {
            $this->handleYoutube($post, $youtubeUrl);
        }
    }
}
