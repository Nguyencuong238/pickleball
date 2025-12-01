<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'description', 'video_link', 'category_id', 'instructor_id', 'duration', 'level', 'views_count', 'rating', 'rating_count', 'chapters'];

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
}
