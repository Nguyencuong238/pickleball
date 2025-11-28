<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'user_id',
        'rating',
        'content',
        'tags',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'tags' => 'array',
        'is_approved' => 'boolean',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
