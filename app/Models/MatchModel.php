<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchModel extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'category_id',
        'round_id',
        'court_id',
        'group_id',
        'match_number',
        'bracket_position',
        'athlete1_id',
        'athlete1_name',
        'athlete1_score',
        'athlete2_id',
        'athlete2_name',
        'athlete2_score',
        'winner_id',
        'referee_id',
        'referee_name',
        'match_date',
        'match_time',
        'actual_start_time',
        'actual_end_time',
        'status',
        'best_of',
        'set_scores',  // JSON array: [{"set": 1, "athlete1": 11, "athlete2": 7}, ...]
        'final_score',
        'notes',
        'next_match_id',
        'winner_advances_to',
        // Match control columns
        'match_state',
        'current_game',
        'games_won_athlete1',
        'games_won_athlete2',
        'game_scores',
        'serving_team',
        'server_number',
        'timer_seconds',
    ];

    protected $casts = [
        'match_date' => 'date',
        'match_time' => 'string',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'set_scores' => 'array',
        'athlete1_score' => 'integer',
        'athlete2_score' => 'integer',
        'best_of' => 'integer',
        'bracket_position' => 'integer',
        // Match control casts
        'match_state' => 'array',
        'current_game' => 'integer',
        'games_won_athlete1' => 'integer',
        'games_won_athlete2' => 'integer',
        'game_scores' => 'array',
        'server_number' => 'integer',
        'timer_seconds' => 'integer',
    ];

    /**
     * Get the tournament that owns this match.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the category for this match.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TournamentCategory::class, 'category_id');
    }

    /**
     * Get the round for this match.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Get the court for this match.
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get the group for this match.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get athlete 1.
     */
    public function athlete1(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class, 'athlete1_id');
    }

    /**
     * Get athlete 2.
     */
    public function athlete2(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class, 'athlete2_id');
    }

    /**
     * Get the winner.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class, 'winner_id');
    }

    /**
     * Get the next match in the bracket.
     */
    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(MatchModel::class, 'next_match_id');
    }

    /**
     * Check if match is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if match is live.
     */
    public function isLive(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if match is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Get the loser of the match.
     */
    public function getLoserIdAttribute(): ?int
    {
        if (!$this->winner_id) {
            return null;
        }
        return $this->winner_id === $this->athlete1_id ? $this->athlete2_id : $this->athlete1_id;
    }

    /**
     * Start the match.
     */
    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'actual_start_time' => now(),
        ]);
    }

    /**
     * End the match.
     */
    public function end(int $winnerId): void
    {
        $this->update([
            'status' => 'completed',
            'actual_end_time' => now(),
            'winner_id' => $winnerId,
        ]);
    }

    // ==================== Referee Relationships ====================

    /**
     * Referee assigned to this match
     */
    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    /**
     * Check if match has referee assigned
     */
    public function hasReferee(): bool
    {
        return !is_null($this->referee_id);
    }

    /**
     * Check if user is assigned referee
     */
    public function isAssignedToReferee(User $user): bool
    {
        return $this->referee_id === $user->id;
    }

    /**
     * Assign referee to match
     */
    public function assignReferee(User $referee): void
    {
        $this->update([
            'referee_id' => $referee->id,
            'referee_name' => $referee->name,
        ]);
    }

    /**
     * Remove referee from match
     */
    public function removeReferee(): void
    {
        $this->update([
            'referee_id' => null,
            'referee_name' => null,
        ]);
    }

    /**
     * Check if referee can edit this match scores
     */
    public function canEditScores(User $user): bool
    {
        return $this->isAssignedToReferee($user) && !$this->isCompleted();
    }

    /**
     * Scope: matches for a specific referee
     */
    public function scopeForReferee($query, int $refereeId)
    {
        return $query->where('referee_id', $refereeId);
    }

    /**
     * Scope: unassigned matches (no referee)
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('referee_id');
    }

    // ==================== Match Control Methods ====================

    /**
     * Get all events for this match
     */
    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id');
    }

    /**
     * Check if this is a doubles match
     */
    public function isDoubles(): bool
    {
        return $this->category?->isDoubles() ?? false;
    }

    /**
     * Get game mode attribute (singles or doubles)
     */
    public function getGameModeAttribute(): string
    {
        return $this->isDoubles() ? 'doubles' : 'singles';
    }

    /**
     * Record a match event
     */
    public function recordEvent(string $type, ?string $team = null, array $data = [], int $timerSeconds = 0): MatchEvent
    {
        return MatchEvent::record($this->id, $type, $team, $data, $timerSeconds);
    }

    /**
     * Serialize match state for Vue initialization
     */
    public function toVueState(): array
    {
        $isDoubles = $this->isDoubles();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'isCompleted' => $this->isCompleted(),
            'bestOf' => $this->best_of,
            'gameMode' => $isDoubles ? 'doubles' : 'singles',

            'tournament' => [
                'name' => $this->tournament?->name ?? 'N/A',
            ],
            'category' => [
                'name' => $this->category?->category_name ?? 'N/A',
            ],
            'round' => [
                'name' => $this->round?->round_name ?? 'N/A',
            ],
            'court' => [
                'name' => $this->court?->name ?? 'TBA',
                'number' => $this->court?->number ?? '--',
            ],

            'athlete1' => [
                'id' => $this->athlete1_id,
                'name' => $this->athlete1->athlete_name ?? 'TBD',
                'partnerName' => $isDoubles ? ($this->athlete1?->partner?->athlete_name ?? null) : null,
                'pairName' => $isDoubles ? ($this->athlete1?->pair_name ?? $this->athlete1_name) : $this->athlete1_name,
            ],
            'athlete2' => [
                'id' => $this->athlete2_id,
                'name' => $this->athlete2->athlete_name ?? 'TBD',
                'partnerName' => $isDoubles ? ($this->athlete2?->partner?->athlete_name ?? null) : null,
                'pairName' => $isDoubles ? ($this->athlete2?->pair_name ?? $this->athlete2_name) : $this->athlete2_name,
            ],

            'referee' => [
                'id' => $this->referee_id,
                'name' => $this->referee_name ?? 'N/A',
            ],

            // Existing state for recovery
            'existingState' => $this->match_state,
            'gameScores' => $this->game_scores ?? [],
            'setScores' => $this->set_scores ?? [],
            'currentGame' => $this->current_game ?? 1,
            'gamesWonAthlete1' => $this->games_won_athlete1 ?? 0,
            'gamesWonAthlete2' => $this->games_won_athlete2 ?? 0,
            'timerSeconds' => $this->timer_seconds ?? 0,
            'servingTeam' => $this->serving_team,
            'serverNumber' => $this->server_number,
        ];
    }

    /**
     * Update match state from Vue app
     */
    public function syncState(array $state): void
    {
        $this->update([
            'match_state' => $state,
            'current_game' => $state['currentGame'] ?? $this->current_game,
            'games_won_athlete1' => $state['gamesWonAthlete1'] ?? $this->games_won_athlete1,
            'games_won_athlete2' => $state['gamesWonAthlete2'] ?? $this->games_won_athlete2,
            'game_scores' => $state['gameScores'] ?? $this->game_scores,
            'serving_team' => $state['servingTeam'] ?? $this->serving_team,
            'server_number' => $state['serverNumber'] ?? $this->server_number,
            'timer_seconds' => $state['timerSeconds'] ?? $this->timer_seconds,
        ]);
    }
}
