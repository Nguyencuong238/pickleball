<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClubPostMedia extends Model
{
    use HasFactory;

    protected $table = 'club_post_media';

    protected $fillable = [
        'club_post_id',
        'type',
        'path',
        'disk',
        'youtube_url',
        'size',
        'order',
    ];

    protected $casts = [
        'size' => 'integer',
        'order' => 'integer',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(ClubPost::class, 'club_post_id');
    }

    // Accessors
    public function getUrlAttribute(): ?string
    {
        if ($this->type === 'youtube') {
            return $this->youtube_url;
        }

        if ($this->path) {
            return Storage::disk($this->disk)->url($this->path);
        }

        return null;
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->type !== 'youtube') return null;

        $url = $this->youtube_url;

        // Handle various YouTube URL formats
        if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }
        if (preg_match('/youtu\.be\/([^?]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }
        if (preg_match('/youtube\.com\/embed\/([^?]+)/', $url, $matches)) {
            return $url; // Already embed URL
        }

        return null;
    }

    // Methods
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isYoutube(): bool
    {
        return $this->type === 'youtube';
    }
}
