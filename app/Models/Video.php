<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'image', 'description', 'video_link', 'category_id', 'instructor_id', 'duration', 'level', 'views_count', 'rating', 'rating_count', 'chapters'];

    protected $casts = [
        'chapters' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function comments()
    {
        return $this->hasMany(VideoComment::class)->whereNull('parent_id')->with('user', 'replies');
    }

    public function allComments()
    {
        return $this->hasMany(VideoComment::class);
    }

    public function likes()
    {
        return $this->hasMany(VideoLike::class);
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'video_likes', 'video_id', 'user_id')
            ->withTimestamps();
    }

    public function isLikedBy(User|null $user): bool
    {
        if (!$user) return false;
        return $this->likedByUsers()->where('user_id', $user->id)->exists();
    }

    /**
     * Extract YouTube video ID from various URL formats
     * Supports: youtube.com/watch?v=, youtu.be/, youtube.com/shorts/, youtube.com/embed/
     */
    public function getYoutubeId(): ?string
    {
        if (!$this->video_link) return null;

        $url = $this->video_link;

        // Match youtu.be/ID
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        // Match youtube.com/watch?v=ID
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        // Match youtube.com/shorts/ID
        if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        // Match youtube.com/embed/ID
        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get embeddable YouTube URL
     */
    public function getEmbedUrl(): ?string
    {
        $videoId = $this->getYoutubeId();
        if (!$videoId) return null;

        return "https://www.youtube.com/embed/{$videoId}";
    }
}
