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
}
