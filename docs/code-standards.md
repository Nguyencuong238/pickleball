# Code Standards & Conventions

**Last Updated**: 2026-04-08
**Project**: Pickleball Platform
**Framework**: Laravel 10.10+

## Overview

This document defines coding standards, conventions, and best practices for the Pickleball Platform Laravel project.

## Laravel Conventions

### Directory Structure

Follow Laravel's standard structure:

```
app/
├── Console/Commands/      # Artisan commands (25 commands)
├── Contracts/             # Interfaces (e.g., Payable)
├── Exceptions/            # Exception handlers
├── Http/
│   ├── Controllers/       # Route controllers (117 total)
│   │   ├── Admin/        # Admin panel (24 controllers)
│   │   ├── Api/          # API endpoints (32 controllers)
│   │   └── Front/        # Public frontend (33 + 11 Tournament + 5 Traits)
│   └── Middleware/        # HTTP middleware (10: 8 standard + Authenticate + VerifySepayWebhook)
├── Models/                # Eloquent models (86 models)
├── Policies/              # Authorization policies (8)
├── Services/              # Business logic (43 services)
└── Providers/             # Service providers
```

### Controller Organization

**Naming**: `{Resource}Controller.php` in PascalCase

**Location by Role**:
- `Admin/` - Admin panel controllers
- `Api/` - API endpoints
- `Front/` - Public frontend

**Example**:
```php
// app/Http/Controllers/Admin/TournamentController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TournamentController extends Controller
{
    public function index() { }
    public function create() { }
    public function store(Request $request) { }
    public function show(Tournament $tournament) { }
    public function edit(Tournament $tournament) { }
    public function update(Request $request, Tournament $tournament) { }
    public function destroy(Tournament $tournament) { }
}
```

### Model Conventions

**Naming**: Singular PascalCase (`Tournament`, `User`, `Court`)

**Structure**:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Stadium extends Model implements HasMedia
{
    use InteractsWithMedia;

    // 1. Properties
    protected $fillable = ['name', 'address', 'description'];
    protected $casts = ['is_featured' => 'boolean'];

    // 2. Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courts()
    {
        return $this->hasMany(Court::class);
    }

    // 3. Accessors & Mutators
    public function getFullAddressAttribute()
    {
        return $this->address . ', ' . $this->city;
    }

    // 4. Query Scopes
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // 5. Custom Methods
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
```

### Migration Conventions

**Naming**: Recent migrations use `YYYY_MM_DD_action_table_name.php` (without timestamp suffix)

**Actions**:
- `create_*_table` - New table
- `add_*_to_*_table` - Add columns
- `drop_*_from_*_table` - Remove columns
- `modify_*_in_*_table` - Change columns

**Example**: `2026_02_07_add_booking_code_to_bookings_table.php`

```php
public function up(): void
{
    Schema::create('tournaments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->text('description')->nullable();
        $table->date('start_date');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
```

## PHP Conventions

### Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| Classes | PascalCase | `TournamentController` |
| Methods | camelCase | `getAvailableSlots()` |
| Variables | camelCase | `$tournamentId` |
| Constants | UPPER_SNAKE | `MAX_ATHLETES` |
| Database columns | snake_case | `created_at` |
| Routes | kebab-case | `tournaments-detail` |

### Type Declarations

Always use type hints for parameters and return types:

```php
public function calculatePrice(Court $court, string $date, int $duration): float
{
    // Implementation
}

public function getBookings(): Collection
{
    return $this->bookings()->get();
}
```

### Avoid `any` Type

Never use `mixed` or untyped parameters when specific types are known:

```php
// Bad
public function process($data) { }

// Good
public function process(array $data): void { }
public function process(Request $request): JsonResponse { }
```

### Error Handling

Use try-catch blocks for external operations:

```php
public function store(Request $request)
{
    try {
        DB::beginTransaction();

        $tournament = Tournament::create($validated);
        $tournament->addMediaFromRequest('image')->toMediaCollection('images');

        DB::commit();

        return redirect()->route('tournaments.index')
            ->with('success', 'Tournament created successfully');
    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Tournament creation failed', ['error' => $e->getMessage()]);

        return back()->with('error', 'Failed to create tournament');
    }
}
```

## Blade Conventions

### File Naming

- Use kebab-case: `tournament-detail.blade.php`
- Partials prefix with underscore: `_sidebar.blade.php`
- Components use folders: `components/button.blade.php`

### Template Structure

```blade
@extends('layouts.frontend')

@section('title', $tournament->name)

@section('content')
<div class="container">
    {{-- Main content --}}
</div>
@endsection

@push('scripts')
<script>
    // Page-specific scripts
</script>
@endpush
```

### Components

```blade
{{-- resources/views/components/button.blade.php --}}
@props(['type' => 'button', 'variant' => 'primary'])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "btn btn-{$variant}"]) }}
>
    {{ $slot }}
</button>
```

### Avoid Inline PHP

```blade
{{-- Bad --}}
<?php $total = $items->sum('price'); ?>

{{-- Good - pass from controller --}}
{{ $total }}
```

## Route Conventions

### Naming

Use descriptive, RESTful route names:

```php
// Resource routes
Route::resource('tournaments', TournamentController::class);
// Creates: tournaments.index, tournaments.create, tournaments.store, etc.

// Custom routes
Route::get('tournaments/{tournament}/rankings', [TournamentController::class, 'rankings'])
    ->name('tournaments.rankings');
```

### Grouping

Group routes by prefix and middleware:

```php
// Home Yard routes
Route::middleware(['auth', 'role:home_yard'])
    ->prefix('homeyard')
    ->name('homeyard.')
    ->group(function () {
        Route::resource('stadiums', HomeYardStadiumController::class);
        Route::resource('tournaments', HomeYardTournamentController::class);
    });

// Admin routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
    });
```

## Database Conventions

### Table Naming

- Plural snake_case: `tournaments`, `tournament_athletes`
- Pivot tables: alphabetical order `category_tournament`

### Column Naming

| Type | Convention | Example |
|------|-----------|---------|
| Primary key | `id` | `id` |
| Foreign key | `{table}_id` | `user_id` |
| Boolean | `is_*` or `has_*` | `is_active`, `has_payment` |
| Dates | `*_at` or `*_date` | `created_at`, `start_date` |
| JSON | descriptive | `settings`, `metadata` |

### Indexes

Add indexes for:
- Foreign keys (automatic in Laravel)
- Columns used in WHERE clauses
- Columns used in ORDER BY

```php
$table->index('start_date');
$table->index(['user_id', 'status']);
```

## Security Standards

### Input Validation

Always validate input:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'start_date' => 'required|date|after:today',
        'max_athletes' => 'required|integer|min:1|max:500',
    ]);

    Tournament::create($validated);
}
```

### Authorization

Use policies for authorization:

```php
// TournamentPolicy.php
public function update(User $user, Tournament $tournament): bool
{
    return $user->id === $tournament->user_id;
}

// Controller
public function update(Request $request, Tournament $tournament)
{
    $this->authorize('update', $tournament);
    // ...
}
```

### Mass Assignment

Always define `$fillable` or `$guarded`:

```php
// Preferred: explicit fillable
protected $fillable = ['name', 'email', 'status'];

// Alternative: guarded (be careful)
protected $guarded = ['id', 'created_at', 'updated_at'];
```

### SQL Injection Prevention

Use Eloquent or query builder:

```php
// Good - parameterized query
User::where('email', $email)->first();

// Good - query builder
DB::table('users')->where('email', '=', $email)->first();

// Bad - raw SQL with user input
DB::select("SELECT * FROM users WHERE email = '$email'");
```

## Code Organization

### Database Transaction & Lock Pattern

For sequence generation (e.g., booking codes), use DB transactions with pessimistic locking:

```php
return DB::transaction(function () use ($courtId, $date) {
    $last = self::where('court_id', $courtId)
        ->where('booking_date', $date)
        ->lockForUpdate()
        ->latest('id')
        ->first();

    $seq = ($last ? (int)substr($last->booking_code, -3) : 0) + 1;
    return 'BK' . str_pad($courtId, 3, '0', STR_PAD_LEFT)
         . date('ymd', strtotime($date))
         . str_pad($seq, 3, '0', STR_PAD_LEFT);
});
```

Key: `lockForUpdate()` prevents race conditions; `DB::transaction()` ensures atomicity. Fallback: `$booking->formatted_booking_code ?: 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT)`

### Soft Delete Pattern

Use `SoftDeletes` trait for logical deletion with recovery. Auto-excludes deleted records; use `withTrashed()` to include, `onlyTrashed()` to filter only deleted.

### Service Classes

For complex business logic, create service classes. Services encapsulate business rules and coordinate model interactions.

#### Basic Service Example

```php
// app/Services/BookingService.php
class BookingService
{
    public function calculatePrice(Court $court, string $date, int $duration): float
    {
        $pricing = $court->pricings()
            ->where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->first();

        return $pricing->price_per_hour * $duration;
    }

    public function checkAvailability(Court $court, string $date, string $time): bool
    {
        return !Booking::where('court_id', $court->id)
            ->where('date', $date)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->exists();
    }
}
```

#### Service with Dependencies (OPRS Pattern)

Use constructor injection for service dependencies:

```php
// app/Services/OprsService.php
class OprsService
{
    // Define constants for configuration
    public const WEIGHT_ELO = 0.7;
    public const WEIGHT_CHALLENGE = 0.2;
    public const WEIGHT_COMMUNITY = 0.1;

    /**
     * Calculate OPRS from components
     */
    public function calculateOprs(User $user): float
    {
        $oprs = (self::WEIGHT_ELO * $user->elo_rating)
              + (self::WEIGHT_CHALLENGE * $user->challenge_score)
              + (self::WEIGHT_COMMUNITY * $user->community_score);

        return round($oprs, 2);
    }

    /**
     * Update user OPRS and record history
     */
    public function updateUserOprs(User $user, string $reason, ?array $metadata = null): void
    {
        DB::transaction(function () use ($user, $reason, $metadata) {
            $newOprs = $this->calculateOprs($user);
            $newLevel = $this->calculateOprLevel($newOprs);

            OprsHistory::create([
                'user_id' => $user->id,
                'total_oprs' => $newOprs,
                'opr_level' => $newLevel,
                'change_reason' => $reason,
                'metadata' => $metadata,
            ]);

            $user->update([
                'total_oprs' => $newOprs,
                'opr_level' => $newLevel,
            ]);
        });
    }
}
```

#### Service with Dependency Injection

```php
// app/Services/ChallengeService.php
class ChallengeService
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    public function submitChallenge(User $user, string $type, int $score): ChallengeResult
    {
        return DB::transaction(function () use ($user, $type, $score) {
            $challenge = ChallengeResult::create([
                'user_id' => $user->id,
                'challenge_type' => $type,
                'score' => $score,
            ]);

            if ($challenge->passed) {
                $user->increment('challenge_score', $challenge->points_earned);
                $this->oprsService->recalculateAfterChallenge($user, $challenge->id);
            }

            return $challenge;
        });
    }
}
```

#### Service Organization Guidelines

1. **Single Responsibility**: Each service handles one domain area
2. **Constructor Injection**: Inject dependencies via constructor
3. **Type Hints**: Always use strict typing
4. **Transactions**: Wrap multi-model operations in DB transactions
5. **Return Types**: Be explicit about return types
6. **Documentation**: Document complex business logic with PHPDoc

#### Skill Quiz Service Pattern

Key constants and methods in `app/Services/SkillQuizService.php`:
- `MIN_COMPLETION_TIME=180`, `MAX_COMPLETION_TIME=1200`, `ELO_CAP_NEW_PLAYER=1100`, `ELO_CAP_EXPERIENCED=1200`
- `ELO_THRESHOLDS_MALE` / `ELO_THRESHOLDS_FEMALE` arrays (female gets +0.5 level at same ELO)
- `SKILL_LEVEL_NAMES`: 8 levels (2.0–5.5+) with `en`/`vi` keys
- `calculateElo(int $totalScore, int $maxScore): int` — base 800 + percentage*6, capped 800–1400
- `eloToSkillLevel(int $elo, ?string $gender = 'male'): string` — gender-aware threshold lookup
- `getSkillLevelName(string $level, string $locale = 'vi'): string`
- `crossValidate(array $answers): array` — returns `['is_consistent' => bool, 'inconsistency_count' => int]`
- `canRetakeQuiz(User $user): bool` — checks `can_retake_quiz_at` cooldown

### Form Requests

For complex validation, use Form Requests:

```php
// app/Http/Requests/StoreTournamentRequest.php
class StoreTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('home_yard');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'max_athletes' => 'required|integer|min:1',
        ];
    }
}
```

## Testing Standards

### Test Organization

```
tests/
├── Feature/           # Integration tests
│   ├── Auth/
│   ├── Tournament/
│   └── Booking/
└── Unit/              # Unit tests
    ├── Models/
    └── Services/
```

### Test Naming

```php
/** @test */
public function user_can_create_tournament()
{
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->post('/tournaments', [
        'name' => 'Test Tournament',
    ]);

    // Assert
    $response->assertRedirect('/tournaments');
    $this->assertDatabaseHas('tournaments', ['name' => 'Test Tournament']);
}
```

## Git Conventions

### Commit Messages

Use conventional commits:

```
feat(tournament): add athlete export functionality
fix(booking): resolve availability calculation bug
docs(readme): update installation instructions
refactor(auth): simplify OAuth callback handling
```

### Branch Naming

```
feature/tournament-rankings
fix/booking-price-calculation
hotfix/auth-redirect-loop
```

## File Size Guidelines

- Controllers: < 500 lines
- Models: < 300 lines
- Views: < 200 lines
- Services: < 300 lines

If exceeding: extract to services, split controllers, or use Blade components.

## Race-Condition Safe Service Pattern

For atomic sequences (e.g., team generation), use `lockForUpdate()` within `DB::transaction()`. See `LeagueAutoTeamService` and `BookingService` for examples.

## Documentation Standards

Document public methods with PHPDoc (`@param`, `@return`). Use inline comments sparingly for complex logic only.

## Alpine.js Mixin Patterns (Tournament Rewrite - Mar 2026)

Use mixin composition with `Object.assign()` for modular Alpine components:

```javascript
// Base component + mixins
const component = Object.assign({}, baseComponent, groupSetupMixin, manualDrawMixin);

// Mixin file: tournament-draw-group-setup-mixin.js
const groupSetupMixin = {
    groups: [],
    setupGroups() { /* implementation */ },
};
```

**Guidelines:** One mixin per file. Name: `{feature}-mixin.js`. Compose in main file.

## Club Activity Service Pattern (2026-03 Update)

Club activities use multi-service architecture:

```php
// ClubActivityService - RSVP/participant management
public function confirmParticipant(User $user, ClubActivity $activity): ClubActivityParticipant

// ClubActivityMatchService - Match generation (3 algorithms)
public function generateRoundRobinMatches(ClubActivity $activity): Collection

// WaitlistAutoPromotionService - Automatic promotion
public function promoteFromWaitlist(ClubActivity $activity): Collection

// ClubScoreService - Score confirmation workflow
public function confirmScore(ClubActivityMatch $match, User $confirmedBy): bool

// ClubMemberStatsService - Stats calculation
public function calculatePlayerStats(ClubActivity $activity, User $player): array
```

Score confirmation states: pending_confirmation → confirmed/rejected → admin_confirmed

Match format support: Best of 1/3/5 with configurable points_per_set (default: 21)

## Gems Wallet Service Pattern (Apr 2026)

Gems wallet services: GemWalletService (balance, top-up, transfer, refund, releaseReceipt), GemCashbackService (Points conversion), SepayService (VietQR), GemPaymentProcessor (orchestrator).

Key: VerifySepayWebhook middleware validates IP; transaction status pending → completed; webhook metadata for exchange rate; cashback auto-triggers after payment.

### Payable Contract Pattern (v1.14.0)

Any model can plug into the Gems payment system by implementing `App\Contracts\Payable` via the `IsPayable` trait (~10 LOC). Currently: `Booking`, `ClubActivity`. Future: `League`, `Tournament`.

```php
// app/Contracts/Payable.php — 6 required methods
interface Payable {
    public function getPayableId(): int;
    public function getPayableType(): string;
    public function getGemAmount(): int;
    public function getPayerId(): int;
    public function getPayeeId(): int;
    public function isRefundable(): bool;
}

// Usage via GemPaymentProcessor
GemPaymentProcessor::pay($payable);      // debit payer, credit payee locked_balance
GemPaymentProcessor::refundFor($payable); // clawback within window, hard-block after
```

### gems:release-locked Command

Scheduled every 5 minutes. Scans `gem_transactions` where `type=receipt AND released_at IS NULL AND available_at <= NOW()`. Decrements `locked_balance`, sets `released_at`. Idempotent and race-safe with concurrent refunds. Single-server safe with `withoutOverlapping()`; use `onOneServer()` + database cache driver for multi-server.

### Club Activity Gems Payment Pattern
ClubActivityService integrates gems deduction for activity fees:
- `chargeGems(activity, user)` → GemWalletService.deduct() atomically reduces balance → GemTransaction created
- `refundGems(participant)` → GemWalletService.refund() restores gems if transaction.status='completed'
- `promoteFromWaitlist(activity)` → Loop through waitlist, skip users with insufficient gems, auto-cancel them
- All operations wrapped in DB::transaction() with lockForUpdate() for race condition safety
- Example: RSVP confirmed + activity has fee → chargeGems() throws RuntimeException on insufficient balance → controller catches and returns error flag

## Related Documentation

- [Project Overview PDR](./project-overview-pdr.md) | [Codebase Summary](./codebase-summary.md) | [System Architecture](./system-architecture.md)
