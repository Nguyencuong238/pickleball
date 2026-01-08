<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubPostReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_post_id',
        'user_id',
        'type',
    ];

    public const TYPE_LIKE = 'like';
    public const TYPE_LOVE = 'love';
    public const TYPE_FIRE = 'fire';

    public const TYPES = [
        self::TYPE_LIKE,
        self::TYPE_LOVE,
        self::TYPE_FIRE,
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(ClubPost::class, 'club_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
