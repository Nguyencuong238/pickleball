# Phase 1: Database & Models

**Parent**: [plan.md](./plan.md)
**Date**: 2026-01-14 | **Priority**: Critical | **Status**: COMPLETED

## Context

- Depends on: None (foundation phase)
- Blocks: All subsequent phases
- Related: [Wallet Research](./research/researcher-01-wallet-events.md)

## Overview

Create 5 new database tables and corresponding Eloquent models for point task definitions, user submissions, social profile verifications, special challenges, and **events/workshops** (separate from Social). Seed with 16 initial tasks.

## Key Insights

1. `UserWallet.addPoints()` handles audit trail automatically - no need to duplicate
2. Follow existing model patterns (fillable, casts, relationships)
3. Use UUID for point_submissions to prevent enumeration attacks
4. Unique constraints prevent duplicate social profiles

---

## Requirements

### Table: `point_tasks`

Task definitions with configurable points and frequency limits.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| code | varchar(50) | Unique, e.g., 'referral', 'check_in_stadium' |
| name | varchar(100) | Display name |
| description | text | Nullable |
| points | int | Points awarded |
| role | varchar(50) | 'user', 'home_yard', 'referee', 'expert_host' |
| category | enum | 'daily', 'social', 'event', 'tournament' |
| frequency | enum | 'unlimited', 'daily', 'weekly', 'monthly', 'once' |
| requires_approval | boolean | Default false |
| proof_type | enum | 'none', 'image', 'link', 'qr_code' |
| is_active | boolean | Default true |
| timestamps | - | created_at, updated_at |

### Table: `point_submissions`

User proof submissions for admin approval.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| uuid | char(36) | Unique, for public URLs |
| user_id | bigint | FK users |
| point_task_id | bigint | FK point_tasks |
| status | enum | 'pending', 'approved', 'rejected' |
| proof_data | json | {type: 'image', paths: [...]} or {type: 'link', url: '...'} |
| admin_id | bigint | FK users, nullable |
| admin_notes | text | Nullable |
| reviewed_at | timestamp | Nullable |
| points_awarded | int | Default 0 |
| timestamps | - | created_at, updated_at |

**Indexes**: (user_id, point_task_id), (status), (created_at)

### Table: `social_profile_verifications`

Verified social profiles to prevent duplicates.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| user_id | bigint | FK users |
| platform | enum | 'facebook', 'youtube', 'tiktok' |
| profile_url | varchar(500) | Full profile URL |
| verified_at | timestamp | When admin approved |
| verified_by | bigint | FK users (admin) |
| timestamps | - | created_at, updated_at |

**Constraints**: UNIQUE(user_id, platform), UNIQUE(profile_url)

### Table: `special_challenges`

Admin-created time-limited challenges.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| title | varchar(200) | Challenge title |
| description | text | Nullable |
| points | int | Default 15 |
| start_date | date | Challenge start |
| end_date | date | Challenge end |
| is_active | boolean | Default true |
| max_participants | int | Nullable (unlimited if null) |
| timestamps | - | created_at, updated_at |

### Table: `events` (NEW - separate from Social)

Events/workshops with QR check-in for point earning.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| uuid | char(36) | Unique, for QR codes |
| title | varchar(200) | Event title |
| description | text | Nullable |
| location | varchar(500) | Event location |
| stadium_id | bigint | FK stadiums, nullable |
| start_datetime | datetime | Event start |
| end_datetime | datetime | Event end |
| points | int | Default 5 (from join_event task) |
| max_attendees | int | Nullable (unlimited if null) |
| is_active | boolean | Default true |
| qr_code_data | varchar(100) | Unique QR identifier |
| created_by | bigint | FK users (admin/organizer) |
| timestamps | - | created_at, updated_at |

**Indexes**: (stadium_id), (start_datetime), (qr_code_data UNIQUE)

### Table: `event_checkins`

Track user check-ins at events.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| event_id | bigint | FK events |
| user_id | bigint | FK users |
| checked_in_at | timestamp | When user checked in |
| check_in_method | enum | 'qr_code', 'manual' |
| timestamps | - | created_at, updated_at |

**Constraints**: UNIQUE(event_id, user_id) - prevent double check-in

---

## Architecture

```
app/Models/
├── PointTask.php
├── PointSubmission.php
├── SocialProfileVerification.php
├── SpecialChallenge.php
├── Event.php                    # NEW - separate from Social
└── EventCheckin.php             # NEW - track check-ins

database/migrations/
├── xxxx_create_point_tasks_table.php
├── xxxx_create_point_submissions_table.php
├── xxxx_create_social_profile_verifications_table.php
├── xxxx_create_special_challenges_table.php
├── xxxx_create_events_table.php           # NEW
└── xxxx_create_event_checkins_table.php   # NEW

database/seeders/
└── PointTaskSeeder.php
```

---

## Related Code Files

**Reference Models**:
- `app/Models/UserWallet.php` - Existing wallet with addPoints()
- `app/Models/UserPointTransaction.php` - Transaction audit
- `app/Models/CommunityActivity.php` - Pattern for constants/enums

---

## Implementation Steps

### Step 1: Create Migrations

**File**: `database/migrations/2026_01_14_160100_create_point_tasks_table.php`

```php
Schema::create('point_tasks', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique();
    $table->string('name', 100);
    $table->text('description')->nullable();
    $table->integer('points');
    $table->string('role', 50);
    $table->enum('category', ['daily', 'social', 'event', 'tournament']);
    $table->enum('frequency', ['unlimited', 'daily', 'weekly', 'monthly', 'once']);
    $table->boolean('requires_approval')->default(false);
    $table->enum('proof_type', ['none', 'image', 'link', 'qr_code'])->default('none');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**File**: `database/migrations/2026_01_14_160200_create_point_submissions_table.php`

```php
Schema::create('point_submissions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('point_task_id')->constrained()->onDelete('cascade');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->json('proof_data')->nullable();
    $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
    $table->text('admin_notes')->nullable();
    $table->timestamp('reviewed_at')->nullable();
    $table->integer('points_awarded')->default(0);
    $table->timestamps();

    $table->index(['user_id', 'point_task_id']);
    $table->index('status');
    $table->index('created_at');
});
```

**File**: `database/migrations/2026_01_14_160300_create_social_profile_verifications_table.php`

```php
Schema::create('social_profile_verifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('platform', ['facebook', 'youtube', 'tiktok']);
    $table->string('profile_url', 500);
    $table->timestamp('verified_at');
    $table->foreignId('verified_by')->constrained('users')->onDelete('cascade');
    $table->timestamps();

    $table->unique(['user_id', 'platform']);
    $table->unique('profile_url');
});
```

**File**: `database/migrations/2026_01_14_160400_create_special_challenges_table.php`

```php
Schema::create('special_challenges', function (Blueprint $table) {
    $table->id();
    $table->string('title', 200);
    $table->text('description')->nullable();
    $table->integer('points')->default(15);
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_active')->default(true);
    $table->integer('max_participants')->nullable();
    $table->timestamps();
});
```

**File**: `database/migrations/2026_01_14_160500_create_events_table.php`

```php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('title', 200);
    $table->text('description')->nullable();
    $table->string('location', 500)->nullable();
    $table->foreignId('stadium_id')->nullable()->constrained()->onDelete('set null');
    $table->datetime('start_datetime');
    $table->datetime('end_datetime');
    $table->integer('points')->default(5);
    $table->integer('max_attendees')->nullable();
    $table->boolean('is_active')->default(true);
    $table->string('qr_code_data', 100)->unique();
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    $table->timestamps();

    $table->index('stadium_id');
    $table->index('start_datetime');
});
```

**File**: `database/migrations/2026_01_14_160600_create_event_checkins_table.php`

```php
Schema::create('event_checkins', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamp('checked_in_at');
    $table->enum('check_in_method', ['qr_code', 'manual'])->default('qr_code');
    $table->timestamps();

    $table->unique(['event_id', 'user_id']);
    $table->index('user_id');
});
```

### Step 2: Create Models

**File**: `app/Models/PointTask.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointTask extends Model
{
    // Constants for task codes
    public const CODE_REFERRAL = 'referral';
    public const CODE_CHECK_IN_STADIUM = 'check_in_stadium';
    public const CODE_WEEKLY_5_MATCHES = 'weekly_5_matches';
    public const CODE_JOIN_EVENT = 'join_event';
    public const CODE_SPECIAL_CHALLENGE = 'special_challenge';
    public const CODE_JOIN_FB_GROUP = 'join_fb_group';
    public const CODE_FOLLOW_FB_PAGE = 'follow_fb_page';
    public const CODE_SUBSCRIBE_YOUTUBE = 'subscribe_youtube';
    public const CODE_FOLLOW_TIKTOK = 'follow_tiktok';
    public const CODE_JOIN_CLUB = 'join_club';
    public const CODE_CREATE_OCR_MATCH = 'create_ocr_match';
    public const CODE_UPDATE_STADIUM_INFO = 'update_stadium_info';
    public const CODE_CREATE_SOCIAL_SCHEDULE = 'create_social_schedule';
    public const CODE_CREATE_TOURNAMENT = 'create_tournament';
    public const CODE_REFEREE_SCORE_MATCH = 'referee_score_match';
    public const CODE_EXPERT_VERIFY_ELO = 'expert_verify_elo';

    // Role constants
    public const ROLE_USER = 'user';
    public const ROLE_HOME_YARD = 'home_yard';
    public const ROLE_REFEREE = 'referee';
    public const ROLE_EXPERT_HOST = 'expert_host';

    // Category constants
    public const CATEGORY_DAILY = 'daily';
    public const CATEGORY_SOCIAL = 'social';
    public const CATEGORY_EVENT = 'event';
    public const CATEGORY_TOURNAMENT = 'tournament';

    // Frequency constants
    public const FREQ_UNLIMITED = 'unlimited';
    public const FREQ_DAILY = 'daily';
    public const FREQ_WEEKLY = 'weekly';
    public const FREQ_MONTHLY = 'monthly';
    public const FREQ_ONCE = 'once';

    protected $fillable = [
        'code', 'name', 'description', 'points', 'role',
        'category', 'frequency', 'requires_approval', 'proof_type', 'is_active',
    ];

    protected $casts = [
        'points' => 'integer',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(PointSubmission::class);
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    public static function getActiveByRole(string $role): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('role', $role)->where('is_active', true)->get();
    }
}
```

**File**: `app/Models/PointSubmission.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PointSubmission extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'uuid', 'user_id', 'point_task_id', 'status',
        'proof_data', 'admin_id', 'admin_notes', 'reviewed_at', 'points_awarded',
    ];

    protected $casts = [
        'proof_data' => 'array',
        'reviewed_at' => 'datetime',
        'points_awarded' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pointTask(): BelongsTo
    {
        return $this->belongsTo(PointTask::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
```

**File**: `app/Models/SocialProfileVerification.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialProfileVerification extends Model
{
    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_YOUTUBE = 'youtube';
    public const PLATFORM_TIKTOK = 'tiktok';

    protected $fillable = [
        'user_id', 'platform', 'profile_url', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function isProfileUrlTaken(string $url, ?int $excludeUserId = null): bool
    {
        $query = static::where('profile_url', $url);
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }
        return $query->exists();
    }

    public static function hasVerifiedPlatform(int $userId, string $platform): bool
    {
        return static::where('user_id', $userId)->where('platform', $platform)->exists();
    }
}
```

**File**: `app/Models/SpecialChallenge.php`

```php
<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SpecialChallenge extends Model
{
    protected $fillable = [
        'title', 'description', 'points', 'start_date', 'end_date',
        'is_active', 'max_participants',
    ];

    protected $casts = [
        'points' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'max_participants' => 'integer',
    ];

    public function isOngoing(): bool
    {
        $today = Carbon::today();
        return $this->is_active
            && $this->start_date <= $today
            && $this->end_date >= $today;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOngoing($query)
    {
        $today = Carbon::today();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    public function getParticipantCount(): int
    {
        return PointSubmission::where('proof_data->challenge_id', $this->id)
            ->where('status', PointSubmission::STATUS_APPROVED)
            ->count();
    }

    public function hasReachedLimit(): bool
    {
        if ($this->max_participants === null) {
            return false;
        }
        return $this->getParticipantCount() >= $this->max_participants;
    }
}
```

**File**: `app/Models/Event.php` (NEW - separate from Social)

```php
<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    public const CHECK_IN_QR_CODE = 'qr_code';
    public const CHECK_IN_MANUAL = 'manual';

    protected $fillable = [
        'uuid', 'title', 'description', 'location', 'stadium_id',
        'start_datetime', 'end_datetime', 'points', 'max_attendees',
        'is_active', 'qr_code_data', 'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'points' => 'integer',
        'max_attendees' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
            if (empty($model->qr_code_data)) {
                $model->qr_code_data = 'EVT-' . strtoupper(Str::random(8));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function stadium(): BelongsTo
    {
        return $this->belongsTo(Stadium::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(EventCheckin::class);
    }

    public function isOngoing(): bool
    {
        $now = Carbon::now();
        return $this->is_active
            && $this->start_datetime <= $now
            && $this->end_datetime >= $now;
    }

    public function isUpcoming(): bool
    {
        return $this->is_active && $this->start_datetime > Carbon::now();
    }

    public function hasEnded(): bool
    {
        return $this->end_datetime < Carbon::now();
    }

    public function getAttendeeCount(): int
    {
        return $this->checkins()->count();
    }

    public function hasReachedLimit(): bool
    {
        if ($this->max_attendees === null) {
            return false;
        }
        return $this->getAttendeeCount() >= $this->max_attendees;
    }

    public function hasUserCheckedIn(int $userId): bool
    {
        return $this->checkins()->where('user_id', $userId)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOngoing($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('is_active', true)
            ->where('start_datetime', '>', Carbon::now());
    }

    public static function findByQrCode(string $qrCode): ?self
    {
        return static::where('qr_code_data', $qrCode)->first();
    }
}
```

**File**: `app/Models/EventCheckin.php`

```php
<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCheckin extends Model
{
    protected $fillable = [
        'event_id', 'user_id', 'checked_in_at', 'check_in_method',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function checkIn(Event $event, User $user, string $method = 'qr_code'): self
    {
        return static::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'checked_in_at' => Carbon::now(),
            'check_in_method' => $method,
        ]);
    }
}
```

### Step 3: Create Seeder

**File**: `database/seeders/PointTaskSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\PointTask;
use Illuminate\Database\Seeder;

class PointTaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            // User tasks (11)
            ['code' => 'referral', 'name' => 'Gioi thieu ban be', 'points' => 10, 'role' => 'user', 'category' => 'daily', 'frequency' => 'unlimited', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Nhan diem khi ban gioi thieu hoan thanh Skill Quiz'],
            ['code' => 'check_in_stadium', 'name' => 'Check-in san tap', 'points' => 1, 'role' => 'user', 'category' => 'daily', 'frequency' => 'daily', 'requires_approval' => true, 'proof_type' => 'image', 'description' => 'Chup anh check-in tai san tap'],
            ['code' => 'weekly_5_matches', 'name' => 'Thu thach 5 tran/tuan', 'points' => 5, 'role' => 'user', 'category' => 'daily', 'frequency' => 'weekly', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Hoan thanh 5 tran OCR trong tuan'],
            ['code' => 'join_event', 'name' => 'Tham gia su kien', 'points' => 5, 'role' => 'user', 'category' => 'event', 'frequency' => 'unlimited', 'requires_approval' => false, 'proof_type' => 'qr_code', 'description' => 'Check-in su kien bang QR code'],
            ['code' => 'special_challenge', 'name' => 'Thu thach dac biet', 'points' => 15, 'role' => 'user', 'category' => 'event', 'frequency' => 'unlimited', 'requires_approval' => true, 'proof_type' => 'image', 'description' => 'Hoan thanh thu thach dac biet tu Admin'],
            ['code' => 'join_fb_group', 'name' => 'Tham gia Group Facebook', 'points' => 1, 'role' => 'user', 'category' => 'social', 'frequency' => 'once', 'requires_approval' => true, 'proof_type' => 'link', 'description' => 'Tham gia Group Facebook OnePickleball'],
            ['code' => 'follow_fb_page', 'name' => 'Follow Fanpage Facebook', 'points' => 1, 'role' => 'user', 'category' => 'social', 'frequency' => 'once', 'requires_approval' => true, 'proof_type' => 'link', 'description' => 'Follow Fanpage Facebook OnePickleball'],
            ['code' => 'subscribe_youtube', 'name' => 'Dang ky kenh Youtube', 'points' => 1, 'role' => 'user', 'category' => 'social', 'frequency' => 'once', 'requires_approval' => true, 'proof_type' => 'link', 'description' => 'Dang ky kenh Youtube OnePickleball'],
            ['code' => 'follow_tiktok', 'name' => 'Follow TikTok', 'points' => 1, 'role' => 'user', 'category' => 'social', 'frequency' => 'once', 'requires_approval' => true, 'proof_type' => 'link', 'description' => 'Follow kenh TikTok OnePickleball'],
            ['code' => 'join_club', 'name' => 'Tham gia CLB/Nhom', 'points' => 5, 'role' => 'user', 'category' => 'tournament', 'frequency' => 'once', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Tham gia mot CLB tren he thong'],
            ['code' => 'create_ocr_match', 'name' => 'Tao tran OCR', 'points' => 2, 'role' => 'user', 'category' => 'tournament', 'frequency' => 'unlimited', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Tao va hoan thanh tran dau OCR'],

            // Home Yard tasks (3) - once per stadium
            ['code' => 'update_stadium_info', 'name' => 'Cap nhat thong tin cum san', 'points' => 10, 'role' => 'home_yard', 'category' => 'tournament', 'frequency' => 'once', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Cap nhat day du thong tin cum san'],
            ['code' => 'create_social_schedule', 'name' => 'Tao lich dau Social', 'points' => 5, 'role' => 'home_yard', 'category' => 'tournament', 'frequency' => 'once', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Tao lich dau Social cho san'],
            ['code' => 'create_tournament', 'name' => 'Tao giai dau', 'points' => 20, 'role' => 'home_yard', 'category' => 'tournament', 'frequency' => 'once', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Tao giai dau moi cho san'],

            // Referee task (1)
            ['code' => 'referee_score_match', 'name' => 'Cham diem tran dau', 'points' => 10, 'role' => 'referee', 'category' => 'tournament', 'frequency' => 'unlimited', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Cham diem cho tran dau giai dau'],

            // Expert Host task (1)
            ['code' => 'expert_verify_elo', 'name' => 'Cham trinh VDV', 'points' => 15, 'role' => 'expert_host', 'category' => 'tournament', 'frequency' => 'unlimited', 'requires_approval' => false, 'proof_type' => 'none', 'description' => 'Xac nhan trinh do ELO cho VDV'],
        ];

        foreach ($tasks as $task) {
            PointTask::updateOrCreate(['code' => $task['code']], $task);
        }
    }
}
```

### Step 4: Run Migrations

```bash
php artisan migrate
php artisan db:seed --class=PointTaskSeeder
```

---

## Todo

- [x] Create migration `create_point_tasks_table`
- [x] Create migration `create_point_submissions_table`
- [x] Create migration `create_social_profile_verifications_table`
- [x] Create migration `create_special_challenges_table`
- [x] Create migration `create_events_table` (NEW)
- [x] Create migration `create_event_checkins_table` (NEW)
- [x] Create model `PointTask` with constants
- [x] Create model `PointSubmission` with UUID boot
- [x] Create model `SocialProfileVerification`
- [x] Create model `SpecialChallenge`
- [x] Create model `Event` with QR code (NEW)
- [x] Create model `EventCheckin` (NEW)
- [x] Create `PointTaskSeeder` with 16 tasks
- [x] Run migrations and seeder
- [x] Verify tables created correctly

## Completion Notes

**Completed**: 2026-01-14

**Files Created**:
- `database/migrations/2026_01_14_160100_create_point_tasks_table.php`
- `database/migrations/2026_01_14_160200_create_point_submissions_table.php`
- `database/migrations/2026_01_14_160300_create_social_profile_verifications_table.php`
- `database/migrations/2026_01_14_160400_create_special_challenges_table.php`
- `database/migrations/2026_01_14_160500_create_events_table.php`
- `database/migrations/2026_01_14_160600_create_event_checkins_table.php`
- `app/Models/PointTask.php`
- `app/Models/PointSubmission.php`
- `app/Models/SocialProfileVerification.php`
- `app/Models/SpecialChallenge.php`
- `app/Models/Event.php`
- `app/Models/EventCheckin.php`
- `database/seeders/PointTaskSeeder.php`

**Verification**:
- All 6 tables created successfully
- 16 point tasks seeded (11 user, 3 home_yard, 1 referee, 1 expert_host)
- All models have proper relationships and methods

---

## Success Criteria

1. All 6 tables created with correct schema (incl. events, event_checkins)
2. All 6 models have proper relationships and methods
3. 16 tasks seeded correctly
4. UUID generation works for submissions and events
5. Unique constraints enforced
6. Event QR code generation works
7. Event check-in prevents duplicates (unique constraint)

---

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Migration conflicts | Low | Medium | Use unique timestamps |
| Seeder duplicate errors | Low | Low | Use updateOrCreate |

---

## Security Considerations

1. UUID on point_submissions prevents enumeration attacks
2. Foreign key constraints maintain referential integrity
3. Cascade delete removes orphaned submissions

---

## Next Steps

After completion, proceed to [Phase 2: Services Core](./phase-02-services-core.md)
