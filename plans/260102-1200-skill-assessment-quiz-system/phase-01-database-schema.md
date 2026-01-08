# Phase 1: Database Schema

**Date**: 2026-01-02
**Priority**: Critical
**Status**: Completed

## Context Links
- Spec: Quiz Specification v2.0 (user provided)
- Existing: `database/migrations/2025_12_29_000000_create_quizzes_table.php`
- Reference: `OprsService.php`, `EloService.php`

## Overview

Create database structure for 36-question skill assessment quiz with domain-based scoring.

## Requirements

### Functional
- 6 domains with configurable weights
- 36 questions (6 per domain) with anchor levels
- Quiz attempt tracking with timestamps
- Individual answer storage with time spent

### Non-functional
- Indexed for quick user lookups
- JSONB for flexible flag storage
- Support for re-quiz policy checks

## Database Schema

### Table 1: `skill_domains`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| key | varchar(50) | Unique key (rules, consistency, etc.) |
| name | varchar(100) | Display name |
| name_vi | varchar(100) | Vietnamese name |
| description | text | Domain description |
| weight | decimal(5,4) | Weight for scoring (0.10-0.20) |
| anchor_min | decimal(3,1) | Min anchor level (2.0) |
| anchor_max | decimal(3,1) | Max anchor level (6.0) |
| order | smallint | Display order |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes**: `key` (unique)

### Table 2: `skill_questions`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| domain_id | bigint FK | Reference to skill_domains |
| question_vi | text | Vietnamese question text |
| question_en | text | English question text (nullable) |
| anchor_level | decimal(3,1) | Anchor level (2.0-6.0) |
| order_in_domain | smallint | Order within domain (1-6) |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes**: `domain_id`, `(domain_id, order_in_domain)`

### Table 3: `skill_quiz_attempts`

| Column | Type | Description |
|--------|------|-------------|
| id | uuid | Primary key |
| user_id | bigint FK | Reference to users |
| started_at | timestamp | Quiz start time |
| completed_at | timestamp | Quiz completion time (nullable) |
| duration_seconds | int | Total duration |
| status | varchar(20) | in_progress, completed, abandoned |
| domain_scores | jsonb | {rules: 78.5, consistency: 65.0, ...} |
| quiz_percent | decimal(5,2) | Weighted total percentage |
| calculated_elo | int | Raw calculated ELO |
| final_elo | int | Final ELO after adjustments |
| flags | jsonb | [{type, message, adjustment}] |
| is_provisional | boolean | ELO needs match confirmation |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes**: `user_id`, `(user_id, created_at)`, `final_elo`, `status`

### Table 4: `skill_quiz_answers`

| Column | Type | Description |
|--------|------|-------------|
| id | uuid | Primary key |
| attempt_id | uuid FK | Reference to skill_quiz_attempts |
| question_id | bigint FK | Reference to skill_questions |
| answer_value | smallint | 0-3 score |
| answered_at | timestamp | When answered |
| time_spent_seconds | int | Seconds on this question |
| created_at | timestamp | |

**Indexes**: `attempt_id`, `(attempt_id, question_id)` unique

### User Table Additions

Add to `users` table:
| Column | Type | Description |
|--------|------|-------------|
| last_skill_quiz_at | timestamp | Last completed quiz |
| skill_quiz_count | int | Total completed quizzes |
| elo_is_provisional | boolean | ELO from quiz (not matches) |

## Related Code Files

### Create
- `database/migrations/2026_01_02_000001_create_skill_domains_table.php`
- `database/migrations/2026_01_02_000002_create_skill_questions_table.php`
- `database/migrations/2026_01_02_000003_create_skill_quiz_attempts_table.php`
- `database/migrations/2026_01_02_000004_create_skill_quiz_answers_table.php`
- `database/migrations/2026_01_02_000005_add_skill_quiz_fields_to_users_table.php`
- `database/seeders/SkillDomainSeeder.php`
- `database/seeders/SkillQuestionSeeder.php`

### Models to Create
- `app/Models/SkillDomain.php`
- `app/Models/SkillQuestion.php`
- `app/Models/SkillQuizAttempt.php`
- `app/Models/SkillQuizAnswer.php`

## Implementation Steps

### Step 1: Create Migrations

1. Create `skill_domains` migration:
```php
Schema::create('skill_domains', function (Blueprint $table) {
    $table->id();
    $table->string('key', 50)->unique();
    $table->string('name', 100);
    $table->string('name_vi', 100);
    $table->text('description')->nullable();
    $table->decimal('weight', 5, 4); // 0.1000 - 0.2000
    $table->decimal('anchor_min', 3, 1)->default(2.0);
    $table->decimal('anchor_max', 3, 1)->default(6.0);
    $table->smallInteger('order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

2. Create `skill_questions` migration:
```php
Schema::create('skill_questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('domain_id')->constrained('skill_domains')->cascadeOnDelete();
    $table->text('question_vi');
    $table->text('question_en')->nullable();
    $table->decimal('anchor_level', 3, 1);
    $table->smallInteger('order_in_domain');
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['domain_id', 'order_in_domain']);
});
```

3. Create `skill_quiz_attempts` migration:
```php
Schema::create('skill_quiz_attempts', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('started_at');
    $table->timestamp('completed_at')->nullable();
    $table->integer('duration_seconds')->nullable();
    $table->string('status', 20)->default('in_progress');
    $table->json('domain_scores')->nullable();
    $table->decimal('quiz_percent', 5, 2)->nullable();
    $table->integer('calculated_elo')->nullable();
    $table->integer('final_elo')->nullable();
    $table->json('flags')->default('[]');
    $table->boolean('is_provisional')->default(true);
    $table->timestamps();

    $table->index('user_id');
    $table->index(['user_id', 'created_at']);
    $table->index('final_elo');
    $table->index('status');
});
```

4. Create `skill_quiz_answers` migration:
```php
Schema::create('skill_quiz_answers', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('attempt_id');
    $table->foreignId('question_id')->constrained('skill_questions')->cascadeOnDelete();
    $table->smallInteger('answer_value'); // 0-3
    $table->timestamp('answered_at');
    $table->integer('time_spent_seconds')->default(0);
    $table->timestamp('created_at')->useCurrent();

    $table->foreign('attempt_id')
        ->references('id')
        ->on('skill_quiz_attempts')
        ->cascadeOnDelete();

    $table->index('attempt_id');
    $table->unique(['attempt_id', 'question_id']);
});
```

5. Add user fields migration:
```php
Schema::table('users', function (Blueprint $table) {
    $table->timestamp('last_skill_quiz_at')->nullable()->after('opr_level');
    $table->unsignedSmallInteger('skill_quiz_count')->default(0)->after('last_skill_quiz_at');
    $table->boolean('elo_is_provisional')->default(true)->after('skill_quiz_count');
});
```

### Step 2: Create Models

**SkillDomain Model**:
```php
class SkillDomain extends Model
{
    protected $fillable = [
        'key', 'name', 'name_vi', 'description',
        'weight', 'anchor_min', 'anchor_max', 'order', 'is_active'
    ];

    protected $casts = [
        'weight' => 'decimal:4',
        'anchor_min' => 'decimal:1',
        'anchor_max' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(SkillQuestion::class, 'domain_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
```

**SkillQuestion Model**:
```php
class SkillQuestion extends Model
{
    protected $fillable = [
        'domain_id', 'question_vi', 'question_en',
        'anchor_level', 'order_in_domain', 'is_active'
    ];

    protected $casts = [
        'anchor_level' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(SkillDomain::class, 'domain_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

**SkillQuizAttempt Model**:
```php
class SkillQuizAttempt extends Model
{
    use HasUuids;

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABANDONED = 'abandoned';

    protected $fillable = [
        'user_id', 'started_at', 'completed_at', 'duration_seconds',
        'status', 'domain_scores', 'quiz_percent', 'calculated_elo',
        'final_elo', 'flags', 'is_provisional'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'domain_scores' => 'array',
        'quiz_percent' => 'decimal:2',
        'flags' => 'array',
        'is_provisional' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SkillQuizAnswer::class, 'attempt_id');
    }
}
```

**SkillQuizAnswer Model**:
```php
class SkillQuizAnswer extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'attempt_id', 'question_id', 'answer_value',
        'answered_at', 'time_spent_seconds'
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(SkillQuizAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SkillQuestion::class);
    }
}
```

### Step 3: Create Seeders

**SkillDomainSeeder**:
```php
$domains = [
    [
        'key' => 'rules',
        'name' => 'Rules & Positioning',
        'name_vi' => 'Luật & Vị trí',
        'weight' => 0.10,
        'anchor_min' => 2.0,
        'anchor_max' => 4.0,
        'order' => 1,
    ],
    [
        'key' => 'consistency',
        'name' => 'Consistency',
        'name_vi' => 'Độ ổn định',
        'weight' => 0.20,
        'anchor_min' => 2.5,
        'anchor_max' => 4.5,
        'order' => 2,
    ],
    [
        'key' => 'serve_return',
        'name' => 'Serve & Return',
        'name_vi' => 'Giao bóng & Trả giao',
        'weight' => 0.15,
        'anchor_min' => 3.0,
        'anchor_max' => 5.0,
        'order' => 3,
    ],
    [
        'key' => 'dink_net',
        'name' => 'Dink & Net Play',
        'name_vi' => 'Dink & Chơi lưới',
        'weight' => 0.20,
        'anchor_min' => 3.5,
        'anchor_max' => 5.5,
        'order' => 4,
    ],
    [
        'key' => 'reset_defense',
        'name' => 'Reset & Defense',
        'name_vi' => 'Reset & Phòng thủ',
        'weight' => 0.20,
        'anchor_min' => 4.0,
        'anchor_max' => 5.5,
        'order' => 5,
    ],
    [
        'key' => 'tactics',
        'name' => 'Tactics & Partner Play',
        'name_vi' => 'Chiến thuật & Phối hợp',
        'weight' => 0.15,
        'anchor_min' => 4.0,
        'anchor_max' => 6.0,
        'order' => 6,
    ],
];
```

**SkillQuestionSeeder** (36 questions from spec):
```php
<?php

namespace Database\Seeders;

use App\Models\SkillDomain;
use App\Models\SkillQuestion;
use Illuminate\Database\Seeder;

class SkillQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // DOMAIN 1 – LUẬT & VỊ TRÍ (Rules & Positioning)
            // Anchor: 2.0 → 4.0
            'rules' => [
                ['question_vi' => 'Tôi hiểu và áp dụng đúng luật double bounce rule trong trận', 'anchor_level' => 2.0],
                ['question_vi' => 'Tôi hiếm khi đứng sai vị trí khi giao bóng / trả giao', 'anchor_level' => 2.5],
                ['question_vi' => 'Tôi biết khi nào được volley, khi nào không trong NVZ', 'anchor_level' => 3.0],
                ['question_vi' => 'Tôi gọi đúng line/out và hiểu luật replay, foot fault', 'anchor_level' => 3.0],
                ['question_vi' => 'Tôi xoay vị trí hợp lý khi đánh đôi (stacking cơ bản)', 'anchor_level' => 3.5],
                ['question_vi' => 'Tôi hiếm khi mất điểm vì lỗi luật', 'anchor_level' => 4.0],
            ],

            // DOMAIN 2 – CONSISTENCY (Độ ổn định)
            // Anchor: 2.5 → 4.5
            'consistency' => [
                ['question_vi' => 'Tôi có thể đánh rally 6-8 bóng không lỗi', 'anchor_level' => 2.5],
                ['question_vi' => 'Tôi giữ bóng trong sân khi đánh chéo cơ bản', 'anchor_level' => 3.0],
                ['question_vi' => 'Tôi giảm lỗi tự đánh hỏng khi bị ép nhịp', 'anchor_level' => 3.5],
                ['question_vi' => 'Tôi đánh ổn định cả forehand & backhand', 'anchor_level' => 3.5],
                ['question_vi' => 'Tôi giữ được độ ổn định xuyên suốt 1 game', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi hiếm khi đánh hỏng bóng dễ', 'anchor_level' => 4.5],
            ],

            // DOMAIN 3 – SERVE & RETURN
            // Anchor: 3.0 → 5.0
            'serve_return' => [
                ['question_vi' => 'Tôi giao bóng đúng luật và ổn định', 'anchor_level' => 3.0],
                ['question_vi' => 'Tôi điều hướng được vị trí giao bóng (wide / body)', 'anchor_level' => 3.5],
                ['question_vi' => 'Return của tôi sâu và gây áp lực', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi hạn chế lỗi return trước giao khó', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi tận dụng return để lên net', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi biến serve/return thành lợi thế chiến thuật', 'anchor_level' => 5.0],
            ],

            // DOMAIN 4 – DINK & NET PLAY
            // Anchor: 3.5 → 5.5
            'dink_net' => [
                ['question_vi' => 'Tôi duy trì được dink rally mà không nóng vội', 'anchor_level' => 3.5],
                ['question_vi' => 'Tôi điều hướng dink sang backhand đối thủ', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi nhận biết thời điểm speed-up hợp lý', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi volley ổn định tại NVZ line', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi tạo áp lực ở khu vực net', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi kiểm soát được nhịp độ ở kitchen', 'anchor_level' => 5.5],
            ],

            // DOMAIN 5 – RESET & DEFENSE
            // Anchor: 4.0 → 5.5
            'reset_defense' => [
                ['question_vi' => 'Tôi reset được bóng khi bị smash', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi block drive ổn định', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi giữ bóng thấp khi phòng thủ', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi chuyển từ defense sang neutral hiệu quả', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi đọc được hướng smash của đối thủ', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi giữ bình tĩnh khi bị ép nhịp', 'anchor_level' => 5.5],
            ],

            // DOMAIN 6 – TACTICS & PARTNER PLAY
            // Anchor: 4.0 → 6.0
            'tactics' => [
                ['question_vi' => 'Tôi chủ động phối hợp với partner', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi chọn mục tiêu tấn công hợp lý', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi nhận diện điểm yếu đối thủ trong trận', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi điều chỉnh chiến thuật giữa game', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi bọc lót và cover sân hiệu quả', 'anchor_level' => 5.5],
                ['question_vi' => 'Tôi giữ nhịp đội và tinh thần thi đấu', 'anchor_level' => 6.0],
            ],
        ];

        foreach ($questions as $domainKey => $domainQuestions) {
            $domain = SkillDomain::where('key', $domainKey)->first();

            if (!$domain) {
                continue;
            }

            foreach ($domainQuestions as $order => $questionData) {
                SkillQuestion::create([
                    'domain_id' => $domain->id,
                    'question_vi' => $questionData['question_vi'],
                    'question_en' => null,
                    'anchor_level' => $questionData['anchor_level'],
                    'order_in_domain' => $order + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
```

### Bảng tổng hợp 36 câu hỏi

| # | Domain | Câu hỏi | Anchor |
|---|--------|---------|--------|
| 1 | Rules | Tôi hiểu và áp dụng đúng luật double bounce rule trong trận | 2.0 |
| 2 | Rules | Tôi hiếm khi đứng sai vị trí khi giao bóng / trả giao | 2.5 |
| 3 | Rules | Tôi biết khi nào được volley, khi nào không trong NVZ | 3.0 |
| 4 | Rules | Tôi gọi đúng line/out và hiểu luật replay, foot fault | 3.0 |
| 5 | Rules | Tôi xoay vị trí hợp lý khi đánh đôi (stacking cơ bản) | 3.5 |
| 6 | Rules | Tôi hiếm khi mất điểm vì lỗi luật | 4.0 |
| 7 | Consistency | Tôi có thể đánh rally 6-8 bóng không lỗi | 2.5 |
| 8 | Consistency | Tôi giữ bóng trong sân khi đánh chéo cơ bản | 3.0 |
| 9 | Consistency | Tôi giảm lỗi tự đánh hỏng khi bị ép nhịp | 3.5 |
| 10 | Consistency | Tôi đánh ổn định cả forehand & backhand | 3.5 |
| 11 | Consistency | Tôi giữ được độ ổn định xuyên suốt 1 game | 4.0 |
| 12 | Consistency | Tôi hiếm khi đánh hỏng bóng dễ | 4.5 |
| 13 | Serve/Return | Tôi giao bóng đúng luật và ổn định | 3.0 |
| 14 | Serve/Return | Tôi điều hướng được vị trí giao bóng (wide / body) | 3.5 |
| 15 | Serve/Return | Return của tôi sâu và gây áp lực | 4.0 |
| 16 | Serve/Return | Tôi hạn chế lỗi return trước giao khó | 4.0 |
| 17 | Serve/Return | Tôi tận dụng return để lên net | 4.5 |
| 18 | Serve/Return | Tôi biến serve/return thành lợi thế chiến thuật | 5.0 |
| 19 | Dink/Net | Tôi duy trì được dink rally mà không nóng vội | 3.5 |
| 20 | Dink/Net | Tôi điều hướng dink sang backhand đối thủ | 4.0 |
| 21 | Dink/Net | Tôi nhận biết thời điểm speed-up hợp lý | 4.5 |
| 22 | Dink/Net | Tôi volley ổn định tại NVZ line | 4.5 |
| 23 | Dink/Net | Tôi tạo áp lực ở khu vực net | 5.0 |
| 24 | Dink/Net | Tôi kiểm soát được nhịp độ ở kitchen | 5.5 |
| 25 | Reset/Defense | Tôi reset được bóng khi bị smash | 4.0 |
| 26 | Reset/Defense | Tôi block drive ổn định | 4.0 |
| 27 | Reset/Defense | Tôi giữ bóng thấp khi phòng thủ | 4.5 |
| 28 | Reset/Defense | Tôi chuyển từ defense sang neutral hiệu quả | 5.0 |
| 29 | Reset/Defense | Tôi đọc được hướng smash của đối thủ | 5.0 |
| 30 | Reset/Defense | Tôi giữ bình tĩnh khi bị ép nhịp | 5.5 |
| 31 | Tactics | Tôi chủ động phối hợp với partner | 4.0 |
| 32 | Tactics | Tôi chọn mục tiêu tấn công hợp lý | 4.5 |
| 33 | Tactics | Tôi nhận diện điểm yếu đối thủ trong trận | 5.0 |
| 34 | Tactics | Tôi điều chỉnh chiến thuật giữa game | 5.0 |
| 35 | Tactics | Tôi bọc lót và cover sân hiệu quả | 5.5 |
| 36 | Tactics | Tôi giữ nhịp đội và tinh thần thi đấu | 6.0 |

## Todo List

- [x] Create skill_domains migration
- [x] Create skill_questions migration
- [x] Create skill_quiz_attempts migration
- [x] Create skill_quiz_answers migration
- [x] Create add_skill_quiz_fields_to_users migration
- [x] Create SkillDomain model
- [x] Create SkillQuestion model
- [x] Create SkillQuizAttempt model
- [x] Create SkillQuizAnswer model
- [x] Create SkillDomainSeeder with 6 domains
- [x] Create SkillQuestionSeeder with 36 questions
- [x] Update DatabaseSeeder to include new seeders
- [x] Run migrations and verify schema
- [x] Verify seeded data

## Success Criteria

- [x] All 4 tables created with correct schema
- [x] User table has new fields
- [x] 6 domains seeded with correct weights
- [x] 36 questions seeded (6 per domain)
- [x] Foreign keys and indexes working
- [x] Models can query related data

## Risk Assessment

| Risk | Mitigation |
|------|------------|
| UUID compatibility | Use Laravel's HasUuids trait |
| JSON column query | Index jsonb paths if needed |
| Large seeder | Split into smaller chunks |

## Next Steps

After Phase 1:
- Phase 2: Backend Services (scoring, validation)
