<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\SyncMediaCollection;

class Tournament extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SyncMediaCollection;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'datetime',
        'price' => 'float',
        'prizes' => 'float',
        'max_participants' => 'integer',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function athletes()
    {
        return $this->hasMany(TournamentAthlete::class);
    }

    public function categories()
    {
        return $this->hasMany(TournamentCategory::class);
    }

    public function rounds()
    {
        return $this->hasMany(Round::class);
    }

    public function groups()
     {
         return $this->hasMany(Group::class);
     }

     public function matches()
     {
         return $this->hasMany(MatchModel::class);
     }

     public function athleteCount()
    {
        return $this->athletes()->count();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')
            ->singleFile();
    }

    // ==================== Referee Relationships ====================

    /**
     * Referee assignments for this tournament
     */
    public function tournamentReferees()
    {
        return $this->hasMany(TournamentReferee::class);
    }

    /**
     * Active referees assigned to this tournament
     */
    public function referees()
    {
        return $this->belongsToMany(User::class, 'tournament_referees')
            ->withPivot(['assigned_at', 'assigned_by', 'status'])
            ->withTimestamps()
            ->wherePivot('status', 'active');
    }

    /**
     * All referees (including inactive)
     */
    public function allReferees()
    {
        return $this->belongsToMany(User::class, 'tournament_referees')
            ->withPivot(['assigned_at', 'assigned_by', 'status'])
            ->withTimestamps();
    }

    /**
     * Check if user is assigned as referee
     */
    public function hasReferee(User $user): bool
    {
        return $this->referees()->where('users.id', $user->id)->exists();
    }

    /**
     * Assign referee to tournament
     */
    public function assignReferee(User $referee, User $assignedBy): TournamentReferee
    {
        return $this->tournamentReferees()->create([
            'user_id' => $referee->id,
            'assigned_at' => now(),
            'assigned_by' => $assignedBy->id,
            'status' => 'active',
        ]);
    }

    /**
     * Remove referee from tournament
     */
    public function removeReferee(User $referee): bool
    {
        return (bool) $this->tournamentReferees()
            ->where('user_id', $referee->id)
            ->delete();
    }
}
