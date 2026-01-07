# Phase 2: Models & Relationships

**Date**: 2025-12-09
**Status**: Completed
**Completion Date**: 2025-12-09
**Priority**: High
**Parent Plan**: [plan.md](./plan.md)
**Depends On**: [Phase 1 - Database Schema](./phase-01-database-schema.md)

---

## Context

Build Eloquent models and relationships for referee system. TournamentReferee pivot model tracks assignments with audit metadata. Update existing User, Tournament, MatchModel with referee relationships following TournamentAthlete pattern.

---

## Overview

1. Create TournamentReferee pivot model with relationships to User, Tournament
2. Add referees() relationship to Tournament model
3. Add tournamentReferees() relationship to User model
4. Add referee() relationship to MatchModel
5. Add helper methods for validation and queries

---

## Key Insights from Research

**Pivot Model Best Practices**:
- Use dedicated model (not inline pivot) when adding metadata fields
- withPivot(['assigned_at', 'assigned_by']) exposes audit fields
- Pivot model can have own relationships (assignedByUser)

**From TournamentAthlete Pattern**:
- Pivot model has fillable array for mass assignment
- Relationships use explicit FK names for clarity
- Helper methods encapsulate business logic

**From MatchModel Pattern**:
- Cache referee_name to avoid joins on leaderboards
- Use nullable referee_id for unassigned matches
- Add helper methods: isAssignedToReferee(), canEditScores()

---

## Requirements

### Functional

1. Tournament can have multiple referees (hasManyThrough pivot)
2. User can referee multiple tournaments (hasManyThrough pivot)
3. Match belongs to one referee (belongsTo)
4. Pivot model tracks who assigned referee and when
5. Helper methods validate referee assignments

### Non-Functional

1. Eager loading avoids N+1 queries
2. Relationships use explicit FK names for clarity
3. Type hints on all methods
4. Pivot model uses HasFactory for testing

---

## Related Files

### Models to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Models/TournamentReferee.php` | CREATE | Pivot model with audit fields |

### Models to Modify

| File | Action | Description |
|------|--------|-------------|
| `app/Models/Tournament.php` | MODIFY | Add referees() relationship |
| `app/Models/User.php` | MODIFY | Add tournamentReferees() and isReferee() |
| `app/Models/MatchModel.php` | MODIFY | Add referee() relationship and helpers |

---

## Implementation Steps

### Step 1: Create TournamentReferee Pivot Model

**File**: `app/Models/TournamentReferee.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentReferee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tournament_id',
        'user_id',
        'assigned_at',
        'assigned_by',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Tournament this assignment belongs to
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Referee user
     */
    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User who assigned this referee
     */
    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Check if referee is active for this tournament
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Activate referee assignment
     */
    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Deactivate referee assignment
     */
    public function deactivate(): void
    {
        $this->update(['status' => 'inactive']);
    }
}
```

### Step 2: Update Tournament Model

**File**: `app/Models/Tournament.php`

Add to existing Tournament model:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Referee assignments for this tournament
 */
public function tournamentReferees(): HasMany
{
    return $this->hasMany(TournamentReferee::class);
}

/**
 * Active referees assigned to this tournament
 */
public function referees(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'tournament_referees')
        ->withPivot(['assigned_at', 'assigned_by', 'status'])
        ->withTimestamps()
        ->wherePivot('status', 'active')
        ->using(TournamentReferee::class);
}

/**
 * All referees (including inactive)
 */
public function allReferees(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'tournament_referees')
        ->withPivot(['assigned_at', 'assigned_by', 'status'])
        ->withTimestamps()
        ->using(TournamentReferee::class);
}

/**
 * Check if user is assigned as referee
 */
public function hasReferee(User $user): bool
{
    return $this->referees()->where('user_id', $user->id)->exists();
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
    return $this->tournamentReferees()
        ->where('user_id', $referee->id)
        ->delete();
}
```

### Step 3: Update User Model

**File**: `app/Models/User.php`

Add to existing User model:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Tournament referee assignments
 */
public function tournamentReferees(): HasMany
{
    return $this->hasMany(TournamentReferee::class, 'user_id');
}

/**
 * Tournaments this user referees
 */
public function refereeTournaments(): BelongsToMany
{
    return $this->belongsToMany(Tournament::class, 'tournament_referees')
        ->withPivot(['assigned_at', 'assigned_by', 'status'])
        ->withTimestamps()
        ->wherePivot('status', 'active')
        ->using(TournamentReferee::class);
}

/**
 * Matches this user is assigned to referee
 */
public function refereeMatches(): HasMany
{
    return $this->hasMany(MatchModel::class, 'referee_id');
}

/**
 * Check if user has referee role
 */
public function isReferee(): bool
{
    return $this->hasRole('referee');
}

/**
 * Check if user can referee this tournament
 */
public function canReferee(Tournament $tournament): bool
{
    return $this->isReferee() && $tournament->hasReferee($this);
}
```

### Step 4: Update MatchModel

**File**: `app/Models/MatchModel.php`

Add to existing MatchModel:

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * Check if referee can edit this match
 */
public function canEditScores(User $user): bool
{
    return $this->isAssignedToReferee($user) && !$this->isCompleted();
}
```

---

## Todo List

- [ ] Create TournamentReferee model with relationships
- [ ] Update Tournament model with referees() relationships
- [ ] Update User model with tournamentReferees() relationships
- [ ] Update MatchModel with referee() relationship
- [ ] Add helper methods to all models
- [ ] Test relationships in tinker
- [ ] Verify eager loading works: Tournament::with('referees')
- [ ] Test pivot metadata access: $tournament->referees()->first()->pivot->assigned_at
- [ ] Create factory for TournamentReferee (testing)

---

## Success Criteria

- Can query tournament referees: `$tournament->referees`
- Can query user tournaments: `$user->refereeTournaments`
- Can query match referee: `$match->referee`
- Pivot metadata accessible: `$tournament->referees()->first()->pivot->assigned_by`
- Helper methods work: `$tournament->hasReferee($user)`
- No N+1 queries with eager loading

---

## Risk Assessment

**Risk**: Circular relationship complexity (User->Tournament->Match->User)
**Mitigation**: Clear naming (referees vs tournamentReferees) and explicit FK names

**Risk**: Pivot model not recognized by Eloquent
**Mitigation**: Use ->using(TournamentReferee::class) in relationships

**Risk**: Cache drift (referee_name out of sync)
**Mitigation**: Update referee_name whenever referee_id changes (observer pattern in phase 5)

---

## Security Considerations

- Relationships use constrained FKs (database enforces integrity)
- Helper methods check authorization before mutations
- No mass assignment vulnerabilities (fillable defined)
- Type hints prevent unexpected data types

---

## Next Steps

After models complete:
1. Phase 3: Build RefereeController using relationships
2. Test data: Create TournamentRefereeFactory
3. Verify eager loading prevents N+1: `$tournament->load('referees.refereeTournaments')`
