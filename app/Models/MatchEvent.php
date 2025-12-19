<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    /**
     * Disable default timestamps since we only use created_at
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'match_id',
        'event_type',
        'team',
        'data',
        'timer_seconds',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'data' => 'array',
        'timer_seconds' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Event type constants
     */
    public const TYPE_SCORE = 'score';
    public const TYPE_SIDE_OUT = 'side_out';
    public const TYPE_TIMEOUT = 'timeout';
    public const TYPE_FAULT = 'fault';
    public const TYPE_GAME_END = 'game_end';
    public const TYPE_MATCH_START = 'match_start';
    public const TYPE_MATCH_END = 'match_end';
    public const TYPE_UNDO = 'undo';
    public const TYPE_SERVER_CHANGE = 'server_change';
    public const TYPE_RALLY_WON = 'rally_won';
    public const TYPE_RALLY_LOST = 'rally_lost';

    /**
     * Team constants
     */
    public const TEAM_LEFT = 'left';
    public const TEAM_RIGHT = 'right';

    /**
     * Get the match that owns this event.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }

    /**
     * Create a new event for a match
     */
    public static function record(
        int $matchId,
        string $eventType,
        ?string $team = null,
        array $data = [],
        int $timerSeconds = 0
    ): self {
        return self::create([
            'match_id' => $matchId,
            'event_type' => $eventType,
            'team' => $team,
            'data' => $data,
            'timer_seconds' => $timerSeconds,
            'created_at' => now(),
        ]);
    }

    /**
     * Bulk insert events
     */
    public static function recordBatch(int $matchId, array $events): int
    {
        $records = array_map(function ($event) use ($matchId) {
            // Parse created_at from ISO 8601 format or use now()
            $createdAt = now();
            if (!empty($event['created_at'])) {
                try {
                    $createdAt = Carbon::parse($event['created_at']);
                } catch (\Exception $e) {
                    $createdAt = now();
                }
            }

            return [
                'match_id' => $matchId,
                'event_type' => $event['type'] ?? $event['event_type'],
                'team' => $event['team'] ?? null,
                'data' => json_encode($event['data'] ?? []),
                'timer_seconds' => $event['timer_seconds'] ?? 0,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
            ];
        }, $events);

        self::insert($records);

        return count($records);
    }

    /**
     * Get formatted event message for display
     */
    public function getFormattedMessageAttribute(): string
    {
        $teamName = $this->team === self::TEAM_LEFT ? 'Doi Trai' : 'Doi Phai';

        return match ($this->event_type) {
            self::TYPE_SCORE => "{$teamName} ghi diem",
            self::TYPE_SIDE_OUT => 'Side-out! Doi quyen giao',
            self::TYPE_TIMEOUT => "{$teamName} goi timeout",
            self::TYPE_FAULT => 'Loi giao bong',
            self::TYPE_GAME_END => "Ket thuc Game " . $this->data['game_number'] ?? '',
            self::TYPE_MATCH_START => 'Tran dau bat dau',
            self::TYPE_MATCH_END => 'Tran dau ket thuc',
            self::TYPE_UNDO => 'Hoan tac thao tac',
            self::TYPE_SERVER_CHANGE => 'Doi server',
            self::TYPE_RALLY_WON => "{$teamName} thang rally",
            self::TYPE_RALLY_LOST => "{$teamName} thua rally",
            default => $this->event_type,
        };
    }

    /**
     * Get formatted timer display
     */
    public function getTimerDisplayAttribute(): string
    {
        $minutes = floor($this->timer_seconds / 60);
        $seconds = $this->timer_seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
