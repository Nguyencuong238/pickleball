# Phase 5: Admin Panel

**Date**: 2026-01-02
**Priority**: Medium
**Status**: COMPLETED
**Completed**: 2026-01-03
**Depends on**: Phase 1, Phase 2, Phase 3

## Context Links
- Reference: `app/Http/Controllers/Admin/OprsController.php`
- Reference: `resources/views/admin/oprs/`

## Overview

Create admin interface for managing skill quiz attempts, reviewing flagged submissions, and manual ELO adjustments.

## Requirements

### Features
1. List all quiz attempts with filters
2. View attempt details with answers
3. Review flagged attempts
4. Manual ELO adjustment
5. Quiz statistics dashboard

## Related Code Files

### Create
- `app/Http/Controllers/Admin/SkillQuizController.php`
- `resources/views/admin/skill-quiz/index.blade.php`
- `resources/views/admin/skill-quiz/show.blade.php`
- `resources/views/admin/skill-quiz/dashboard.blade.php`

### Modify
- `routes/web.php` (add admin routes)
- `resources/views/admin/layouts/sidebar.blade.php` (add menu)

## Implementation Steps

### Step 1: Create Admin Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkillQuizAttempt;
use App\Models\User;
use App\Services\SkillQuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillQuizController extends Controller
{
    public function __construct(
        private SkillQuizService $quizService
    ) {}

    /**
     * Dashboard with statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_attempts' => SkillQuizAttempt::count(),
            'completed' => SkillQuizAttempt::where('status', 'completed')->count(),
            'in_progress' => SkillQuizAttempt::where('status', 'in_progress')->count(),
            'abandoned' => SkillQuizAttempt::where('status', 'abandoned')->count(),
            'flagged' => SkillQuizAttempt::whereJsonLength('flags', '>', 0)->count(),
        ];

        // ELO distribution
        $eloDistribution = SkillQuizAttempt::where('status', 'completed')
            ->selectRaw('
                CASE
                    WHEN final_elo < 900 THEN "< 900"
                    WHEN final_elo < 1000 THEN "900-999"
                    WHEN final_elo < 1100 THEN "1000-1099"
                    WHEN final_elo < 1200 THEN "1100-1199"
                    WHEN final_elo < 1300 THEN "1200-1299"
                    ELSE "1300+"
                END as elo_range,
                COUNT(*) as count
            ')
            ->groupBy('elo_range')
            ->pluck('count', 'elo_range');

        // Recent attempts
        $recentAttempts = SkillQuizAttempt::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Flagged attempts needing review
        $flaggedAttempts = SkillQuizAttempt::with('user')
            ->whereJsonLength('flags', '>', 0)
            ->where('status', 'completed')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.skill-quiz.dashboard', compact(
            'stats', 'eloDistribution', 'recentAttempts', 'flaggedAttempts'
        ));
    }

    /**
     * List all attempts
     */
    public function index(Request $request)
    {
        $query = SkillQuizAttempt::with('user');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('flagged') && $request->flagged === '1') {
            $query->whereJsonLength('flags', '>', 0);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $attempts = $query->latest()->paginate(20);

        return view('admin.skill-quiz.index', compact('attempts'));
    }

    /**
     * Show attempt details
     */
    public function show(SkillQuizAttempt $attempt)
    {
        $attempt->load(['user', 'answers.question.domain']);

        // Group answers by domain
        $answersByDomain = $attempt->answers
            ->groupBy(fn($a) => $a->question->domain->key);

        return view('admin.skill-quiz.show', compact('attempt', 'answersByDomain'));
    }

    /**
     * Adjust user ELO
     */
    public function adjustElo(Request $request, SkillQuizAttempt $attempt)
    {
        $request->validate([
            'new_elo' => 'required|integer|min:100|max:2000',
            'reason' => 'required|string|max:255',
        ]);

        $user = $attempt->user;
        $oldElo = $user->elo_rating;
        $newElo = $request->new_elo;

        DB::transaction(function () use ($user, $attempt, $newElo, $oldElo, $request) {
            // Update user ELO
            $user->update(['elo_rating' => $newElo]);
            $user->updateEloRank();

            // Update attempt record
            $attempt->update([
                'final_elo' => $newElo,
                'flags' => array_merge($attempt->flags ?? [], [[
                    'type' => 'ADMIN_ADJUSTMENT',
                    'message' => $request->reason,
                    'adjustment' => $newElo - $oldElo,
                    'admin_id' => auth()->id(),
                    'adjusted_at' => now()->toIso8601String(),
                ]]),
            ]);

            // Recalculate OPRS
            app(\App\Services\OprsService::class)->recalculateAfterMatch($user);
        });

        return redirect()->route('admin.skill-quiz.show', $attempt)
            ->with('success', "ELO da cap nhat: {$oldElo} -> {$newElo}");
    }

    /**
     * Mark flags as reviewed
     */
    public function markReviewed(SkillQuizAttempt $attempt)
    {
        $flags = $attempt->flags ?? [];

        foreach ($flags as &$flag) {
            $flag['reviewed'] = true;
            $flag['reviewed_at'] = now()->toIso8601String();
            $flag['reviewed_by'] = auth()->id();
        }

        $attempt->update(['flags' => $flags]);

        return redirect()->route('admin.skill-quiz.show', $attempt)
            ->with('success', 'Da danh dau da xem xet');
    }
}
```

### Step 2: Add Admin Routes

```php
// Admin Skill Quiz Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('skill-quiz')->name('skill-quiz.')->group(function () {
        Route::get('/', [Admin\SkillQuizController::class, 'dashboard'])->name('dashboard');
        Route::get('/attempts', [Admin\SkillQuizController::class, 'index'])->name('index');
        Route::get('/attempts/{attempt}', [Admin\SkillQuizController::class, 'show'])->name('show');
        Route::post('/attempts/{attempt}/adjust-elo', [Admin\SkillQuizController::class, 'adjustElo'])->name('adjust-elo');
        Route::post('/attempts/{attempt}/mark-reviewed', [Admin\SkillQuizController::class, 'markReviewed'])->name('mark-reviewed');
    });
});
```

### Step 3: Create Dashboard View

```blade
{{-- resources/views/admin/skill-quiz/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Skill Quiz Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Skill Quiz Management</h1>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3>{{ $stats['total_attempts'] }}</h3>
                    <small class="text-muted">Tong so</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3>{{ $stats['completed'] }}</h3>
                    <small>Hoan thanh</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-warning">
                <div class="card-body">
                    <h3>{{ $stats['in_progress'] }}</h3>
                    <small>Dang lam</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-secondary text-white">
                <div class="card-body">
                    <h3>{{ $stats['abandoned'] }}</h3>
                    <small>Bo do</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-danger text-white">
                <div class="card-body">
                    <h3>{{ $stats['flagged'] }}</h3>
                    <small>Co canh bao</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ELO Distribution --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Phan bo ELO</div>
                <div class="card-body">
                    @foreach($eloDistribution as $range => $count)
                        <div class="mb-2">
                            <span>{{ $range }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ ($count / max(1, $stats['completed'])) * 100 }}%">
                                    {{ $count }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Flagged Attempts --}}
        <div class="col-md-6 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    Can xem xet ({{ count($flaggedAttempts) }})
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>ELO</th>
                                <th>Flags</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($flaggedAttempts as $attempt)
                                <tr>
                                    <td>{{ $attempt->user->name }}</td>
                                    <td>{{ $attempt->final_elo }}</td>
                                    <td>{{ count($attempt->flags) }}</td>
                                    <td>
                                        <a href="{{ route('admin.skill-quiz.show', $attempt) }}" class="btn btn-sm btn-outline-primary">
                                            Xem
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Attempts --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Quiz gan day</span>
            <a href="{{ route('admin.skill-quiz.index') }}" class="btn btn-sm btn-primary">Xem tat ca</a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Trang thai</th>
                        <th>ELO</th>
                        <th>Thoi gian</th>
                        <th>Ngay</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAttempts as $attempt)
                        <tr>
                            <td>{{ $attempt->user->name }}</td>
                            <td>
                                <span class="badge bg-{{ $attempt->status === 'completed' ? 'success' : ($attempt->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                    {{ $attempt->status }}
                                </span>
                            </td>
                            <td>{{ $attempt->final_elo ?? '-' }}</td>
                            <td>{{ $attempt->duration_seconds ? gmdate('i:s', $attempt->duration_seconds) : '-' }}</td>
                            <td>{{ $attempt->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.skill-quiz.show', $attempt) }}" class="btn btn-sm btn-outline-primary">
                                    Chi tiet
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

### Step 4: Create Index View

```blade
{{-- resources/views/admin/skill-quiz/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Skill Quiz Attempts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h1>Danh sach Skill Quiz</h1>
        <a href="{{ route('admin.skill-quiz.dashboard') }}" class="btn btn-secondary">
            Quay lai Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Trang thai</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoan thanh</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Dang lam</option>
                        <option value="abandoned" {{ request('status') === 'abandoned' ? 'selected' : '' }}>Bo do</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="flagged" class="form-select">
                        <option value="">Canh bao</option>
                        <option value="1" {{ request('flagged') === '1' ? 'selected' : '' }}>Co canh bao</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Tim user..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary">Loc</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Trang thai</th>
                        <th>ELO</th>
                        <th>Diem %</th>
                        <th>Thoi gian</th>
                        <th>Flags</th>
                        <th>Ngay</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attempts as $attempt)
                        <tr>
                            <td>
                                <a href="{{ route('admin.oprs.users.detail', $attempt->user) }}">
                                    {{ $attempt->user->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-{{ $attempt->status === 'completed' ? 'success' : ($attempt->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                    {{ $attempt->status }}
                                </span>
                            </td>
                            <td>{{ $attempt->final_elo ?? '-' }}</td>
                            <td>{{ $attempt->quiz_percent ? number_format($attempt->quiz_percent, 1) . '%' : '-' }}</td>
                            <td>{{ $attempt->duration_seconds ? gmdate('i:s', $attempt->duration_seconds) : '-' }}</td>
                            <td>
                                @if(count($attempt->flags ?? []) > 0)
                                    <span class="badge bg-danger">{{ count($attempt->flags) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $attempt->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.skill-quiz.show', $attempt) }}" class="btn btn-sm btn-outline-primary">
                                    Chi tiet
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Khong co du lieu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $attempts->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
```

### Step 5: Create Show View

```blade
{{-- resources/views/admin/skill-quiz/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Chi tiet Skill Quiz')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h1>Chi tiet Quiz: {{ $attempt->user->name }}</h1>
        <a href="{{ route('admin.skill-quiz.index') }}" class="btn btn-secondary">Quay lai</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        {{-- Summary --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Thong tin chung</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>User:</td>
                            <td><strong>{{ $attempt->user->name }}</strong></td>
                        </tr>
                        <tr>
                            <td>Trang thai:</td>
                            <td>
                                <span class="badge bg-{{ $attempt->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ $attempt->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Bat dau:</td>
                            <td>{{ $attempt->started_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td>Ket thuc:</td>
                            <td>{{ $attempt->completed_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Thoi gian:</td>
                            <td>{{ $attempt->duration_seconds ? gmdate('i:s', $attempt->duration_seconds) : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Diem %:</td>
                            <td><strong>{{ number_format($attempt->quiz_percent ?? 0, 1) }}%</strong></td>
                        </tr>
                        <tr>
                            <td>ELO tinh:</td>
                            <td>{{ $attempt->calculated_elo ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>ELO cuoi:</td>
                            <td><strong class="fs-4">{{ $attempt->final_elo ?? '-' }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Flags --}}
            @if(count($attempt->flags ?? []) > 0)
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white">Canh bao</div>
                    <div class="card-body">
                        @foreach($attempt->flags as $flag)
                            <div class="alert alert-{{ $flag['type'] === 'ADMIN_ADJUSTMENT' ? 'info' : 'warning' }} mb-2">
                                <strong>{{ $flag['type'] }}</strong>
                                <p class="mb-0">{{ $flag['message'] }}</p>
                                <small>Dieu chinh: {{ $flag['adjustment'] > 0 ? '+' : '' }}{{ $flag['adjustment'] }} ELO</small>
                            </div>
                        @endforeach

                        <form action="{{ route('admin.skill-quiz.mark-reviewed', $attempt) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                Danh dau da xem xet
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- ELO Adjustment --}}
            <div class="card">
                <div class="card-header">Dieu chinh ELO</div>
                <div class="card-body">
                    <form action="{{ route('admin.skill-quiz.adjust-elo', $attempt) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">ELO moi</label>
                            <input type="number" name="new_elo" class="form-control" value="{{ $attempt->final_elo }}" min="100" max="2000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ly do</label>
                            <input type="text" name="reason" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Cap nhat ELO</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Domain Scores & Answers --}}
        <div class="col-md-8">
            {{-- Domain Scores --}}
            <div class="card mb-4">
                <div class="card-header">Diem theo linh vuc</div>
                <div class="card-body">
                    @foreach($attempt->domain_scores ?? [] as $domain => $score)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ ucfirst(str_replace('_', ' ', $domain)) }}</span>
                                <strong>{{ number_format($score, 1) }}%</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-{{ $score >= 70 ? 'success' : ($score >= 50 ? 'info' : 'warning') }}"
                                     style="width: {{ $score }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Answers by Domain --}}
            @foreach($answersByDomain as $domainKey => $answers)
                <div class="card mb-3">
                    <div class="card-header">
                        {{ $answers->first()->question->domain->name_vi }}
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cau hoi</th>
                                    <th>Tra loi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($answers as $answer)
                                    <tr>
                                        <td>{{ $answer->question->order_in_domain }}</td>
                                        <td>{{ Str::limit($answer->question->question_vi, 60) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $answer->answer_value >= 2 ? 'success' : ($answer->answer_value == 1 ? 'warning' : 'danger') }}">
                                                {{ $answer->answer_value }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
```

## Todo List

- [x] Create Admin\SkillQuizController
- [x] Add admin routes for skill-quiz
- [x] Create dashboard view
- [x] Create index view with filters
- [x] Create show view with details
- [x] Implement ELO adjustment
- [x] Implement mark as reviewed
- [x] Add sidebar menu item
- [x] Test all admin functions

## Success Criteria

- [x] Dashboard shows statistics
- [x] List displays with filters
- [x] Detail view shows all answers
- [x] ELO adjustment works
- [x] Flags can be marked reviewed

## Risk Assessment

| Risk | Mitigation |
|------|------------|
| Unauthorized access | Role middleware |
| Invalid ELO value | Validation rules |

## Next Steps

After Phase 5:
- Phase 6: Testing & Validation
