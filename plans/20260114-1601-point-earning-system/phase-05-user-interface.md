# Phase 5: User Interface

**Parent**: [plan.md](./plan.md)
**Date**: 2026-01-14 | **Priority**: Medium | **Status**: Completed

## Context

- Depends on: [Phase 2: Services Core](./phase-02-services-core.md)
- Blocks: None
- Related: [Front Views](../resources/views/front/)

## Overview

Create user-facing pages for earning points: task list with eligibility status, proof submission forms, submission history, and point transaction history. Display special challenges prominently **on homepage (banner/modal)** per spec requirement.

## Key Insights

1. Follow existing front layout patterns
2. Image upload via Spatie Media Library (existing pattern)
3. Show ongoing special challenges as banner/card
4. Separate view for each proof type (image, link)

---

## Requirements

### UserPointController

| Route | Method | Action |
|-------|--------|--------|
| GET /user/points | index | Earn points page with task list |
| POST /user/points/submit | submit | Submit proof for approval |
| GET /user/points/history | history | Point transaction history |
| GET /user/points/submissions | submissions | User's submission history |

### Views

| View | Description |
|------|-------------|
| index | Main earn points page with available tasks |
| submit | Submission form (dynamic by proof type) |
| history | Point transaction log |
| submissions | User's proof submissions with status |

### Homepage Banner (NEW - per spec requirement)

Display ongoing special challenges on homepage as banner/modal.

| Component | Description |
|-----------|-------------|
| `_special_challenge_banner.blade.php` | Partial for homepage banner |
| Modify `HomeController@index` | Pass ongoing challenges to homepage |

---

## Architecture

```
app/Http/Controllers/Front/
├── UserPointController.php
└── HomeController.php (modify - add challenges to homepage)

resources/views/front/points/
├── index.blade.php         # Earn points page
├── submit.blade.php        # Submission form
├── history.blade.php       # Transaction history
└── submissions.blade.php   # User submissions

resources/views/front/partials/
└── _special_challenge_banner.blade.php  # Homepage banner (NEW)

resources/views/front/
└── index.blade.php (modify - include banner)
```

---

## Related Code Files

**Reference Controllers**:
- `app/Http/Controllers/Front/OcrController.php` - Front controller pattern
- `app/Http/Controllers/Front/ProfileController.php` - User-specific views

**Reference Views**:
- `resources/views/front/ocr/` - Page layout patterns
- `resources/views/front/user/` - User dashboard patterns

---

## Implementation Steps

### Step 1: Create UserPointController

**File**: `app/Http/Controllers/Front/UserPointController.php`

```php
<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\SpecialChallenge;
use App\Services\PointEarningService;
use App\Services\PointSubmissionService;
use App\Services\SocialVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserPointController extends Controller
{
    public function __construct(
        private PointEarningService $pointEarningService,
        private PointSubmissionService $submissionService,
        private SocialVerificationService $socialVerificationService
    ) {}

    /**
     * Earn points page - show available tasks
     */
    public function index(): View
    {
        $user = auth()->user();

        // Get available tasks with eligibility
        $tasks = $this->pointEarningService->getAvailableTasks($user);

        // Get ongoing special challenges
        $challenges = SpecialChallenge::ongoing()->get();

        // Get user's wallet balance
        $wallet = $user->wallet;
        $balance = $wallet ? $wallet->points : 0;

        // Get user's social verification status
        $socialStatus = $this->socialVerificationService->getVerificationStatus($user);

        // Get pending submissions count
        $pendingCount = PointSubmission::where('user_id', $user->id)
            ->where('status', PointSubmission::STATUS_PENDING)
            ->count();

        return view('front.points.index', compact(
            'tasks', 'challenges', 'balance', 'socialStatus', 'pendingCount'
        ));
    }

    /**
     * Show submission form for a task
     */
    public function showSubmitForm(PointTask $task): View
    {
        $user = auth()->user();

        // Check if task requires approval
        if (!$task->requires_approval) {
            abort(404, 'This task does not require submission');
        }

        // Check if already has pending submission
        $pendingSubmission = PointSubmission::where('user_id', $user->id)
            ->where('point_task_id', $task->id)
            ->where('status', PointSubmission::STATUS_PENDING)
            ->first();

        // For special_challenge, get active challenges
        $challenges = [];
        if ($task->code === PointTask::CODE_SPECIAL_CHALLENGE) {
            $challenges = SpecialChallenge::ongoing()->get();
        }

        return view('front.points.submit', compact('task', 'pendingSubmission', 'challenges'));
    }

    /**
     * Submit proof for approval
     */
    public function submit(Request $request, PointTask $task): RedirectResponse
    {
        $user = auth()->user();

        // Validate based on proof type
        $rules = $this->getValidationRules($task);
        $request->validate($rules);

        try {
            // Build proof data
            $proofData = $this->buildProofData($request, $task);

            // Create submission
            $this->submissionService->submit($user, $task->code, $proofData);

            return redirect()
                ->route('user.points.submissions')
                ->with('success', 'Submission sent for review. You will receive points once approved.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Point transaction history
     */
    public function history(): View
    {
        $user = auth()->user();

        $transactions = $user->pointTransactions()
            ->orderByDesc('created_at')
            ->paginate(20);

        $wallet = $user->wallet;
        $balance = $wallet ? $wallet->points : 0;

        return view('front.points.history', compact('transactions', 'balance'));
    }

    /**
     * User's submission history
     */
    public function submissions(): View
    {
        $user = auth()->user();

        $submissions = $this->submissionService->getUserSubmissions($user);

        return view('front.points.submissions', compact('submissions'));
    }

    /**
     * Get validation rules based on proof type
     */
    private function getValidationRules(PointTask $task): array
    {
        return match ($task->proof_type) {
            'image' => [
                'images' => 'required|array|min:1|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            ],
            'link' => [
                'url' => 'required|url|max:500',
            ],
            'qr_code' => [
                'qr_data' => 'required|string|max:255',
            ],
            default => [],
        };
    }

    /**
     * Build proof data from request
     */
    private function buildProofData(Request $request, PointTask $task): array
    {
        $proofData = ['type' => $task->proof_type];

        switch ($task->proof_type) {
            case 'image':
                $paths = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('point-submissions/' . auth()->id(), 'public');
                    $paths[] = $path;
                }
                $proofData['paths'] = $paths;
                break;

            case 'link':
                $proofData['url'] = $request->input('url');
                break;

            case 'qr_code':
                $proofData['qr_data'] = $request->input('qr_data');
                break;
        }

        // Special challenge ID
        if ($task->code === PointTask::CODE_SPECIAL_CHALLENGE && $request->filled('challenge_id')) {
            $proofData['challenge_id'] = $request->input('challenge_id');
        }

        return $proofData;
    }
}
```

### Step 2: Add Routes

**File**: `routes/web.php` (add to authenticated user group)

```php
// Point Earning System - User
Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    Route::get('points', [UserPointController::class, 'index'])->name('points.index');
    Route::get('points/task/{task}', [UserPointController::class, 'showSubmitForm'])->name('points.submit-form');
    Route::post('points/task/{task}', [UserPointController::class, 'submit'])->name('points.submit');
    Route::get('points/history', [UserPointController::class, 'history'])->name('points.history');
    Route::get('points/submissions', [UserPointController::class, 'submissions'])->name('points.submissions');
});
```

### Step 3: Create Views

**File**: `resources/views/front/points/index.blade.php`

```blade
@extends('layouts.front')

@section('title', 'Earn Points')

@section('content')
<div class="container py-4">
    <!-- Header with Balance -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Earn OnePickleball Points</h1>
        <div class="text-end">
            <span class="badge bg-primary fs-5">[COIN] {{ number_format($balance) }} Points</span>
            @if($pendingCount > 0)
                <br><small class="text-muted">{{ $pendingCount }} pending submission(s)</small>
            @endif
        </div>
    </div>

    <!-- Quick Links -->
    <div class="mb-4">
        <a href="{{ route('user.points.history') }}" class="btn btn-outline-primary btn-sm">View History</a>
        <a href="{{ route('user.points.submissions') }}" class="btn btn-outline-secondary btn-sm">My Submissions</a>
    </div>

    <!-- Special Challenges Banner -->
    @if($challenges->count() > 0)
        <div class="card bg-warning text-dark mb-4">
            <div class="card-body">
                <h5 class="card-title">[FIRE] Special Challenges</h5>
                @foreach($challenges as $challenge)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $challenge->title }}</strong>
                            <br><small>{{ $challenge->description }}</small>
                            <br><small class="text-muted">Ends: {{ $challenge->end_date->format('M d, Y') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-danger">+{{ $challenge->points }} points</span>
                            @if(!$challenge->hasReachedLimit())
                                <br><a href="{{ route('user.points.submit-form', PointTask::findByCode('special_challenge')) }}" class="btn btn-sm btn-dark mt-1">Participate</a>
                            @else
                                <br><span class="badge bg-secondary">Full</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Task Categories -->
    @php
        $tasksByCategory = $tasks->groupBy(fn($t) => $t['task']->category);
        $categoryLabels = [
            'daily' => ['label' => 'Daily Tasks', 'icon' => '[CALENDAR]'],
            'social' => ['label' => 'Social Tasks', 'icon' => '[SHARE]'],
            'event' => ['label' => 'Events', 'icon' => '[TICKET]'],
            'tournament' => ['label' => 'Tournament & Community', 'icon' => '[TROPHY]'],
        ];
    @endphp

    @foreach($categoryLabels as $category => $info)
        @if($tasksByCategory->has($category))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ $info['icon'] }} {{ $info['label'] }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($tasksByCategory[$category] as $item)
                            @php $task = $item['task']; @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 {{ $item['can_earn'] ? '' : 'bg-light' }}">
                                    <div class="card-body d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title">{{ $task->name }}</h6>
                                            <p class="card-text small text-muted">{{ $task->description }}</p>
                                            <span class="badge bg-secondary">{{ ucfirst($task->frequency) }}</span>
                                            @if($task->requires_approval)
                                                <span class="badge bg-info">Needs Approval</span>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success fs-6">+{{ $task->points }}</span>
                                            <br>
                                            @if($item['can_earn'])
                                                @if($task->requires_approval)
                                                    <a href="{{ route('user.points.submit-form', $task) }}" class="btn btn-sm btn-primary mt-2">Submit</a>
                                                @else
                                                    <span class="badge bg-primary mt-2">Auto</span>
                                                @endif
                                            @else
                                                <small class="text-danger">{{ $item['reason'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <!-- Social Verification Status -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">[LINK] Social Profile Status</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    @if($socialStatus['facebook'])
                        <span class="badge bg-success">[CHECK] Facebook Verified</span>
                    @else
                        <span class="badge bg-secondary">Facebook Not Verified</span>
                    @endif
                </div>
                <div class="col-md-4 text-center">
                    @if($socialStatus['youtube'])
                        <span class="badge bg-success">[CHECK] YouTube Verified</span>
                    @else
                        <span class="badge bg-secondary">YouTube Not Verified</span>
                    @endif
                </div>
                <div class="col-md-4 text-center">
                    @if($socialStatus['tiktok'])
                        <span class="badge bg-success">[CHECK] TikTok Verified</span>
                    @else
                        <span class="badge bg-secondary">TikTok Not Verified</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

**File**: `resources/views/front/points/submit.blade.php`

```blade
@extends('layouts.front')

@section('title', 'Submit - ' . $task->name)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.points.index') }}">Earn Points</a></li>
            <li class="breadcrumb-item active">{{ $task->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $task->name }}</h5>
                </div>
                <div class="card-body">
                    @if($pendingSubmission)
                        <div class="alert alert-warning">
                            You have a pending submission for this task submitted on {{ $pendingSubmission->created_at->format('M d, Y H:i') }}.
                            Please wait for admin review.
                        </div>
                    @else
                        <p class="text-muted">{{ $task->description }}</p>
                        <p><strong>Points:</strong> <span class="badge bg-success">+{{ $task->points }}</span></p>

                        <hr>

                        <form action="{{ route('user.points.submit', $task) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @if($task->proof_type === 'image')
                                <div class="mb-3">
                                    <label class="form-label">Upload Proof Images (max 5) *</label>
                                    <input type="file" name="images[]" class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" multiple accept="image/*" required>
                                    <small class="form-text text-muted">Accepted: JPG, PNG, GIF. Max 5MB each.</small>
                                    @error('images')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif

                            @if($task->proof_type === 'link')
                                <div class="mb-3">
                                    <label class="form-label">Profile URL *</label>
                                    <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}" placeholder="https://facebook.com/yourprofile" required>
                                    <small class="form-text text-muted">Enter your full profile URL</small>
                                    @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif

                            @if($task->proof_type === 'qr_code')
                                <div class="mb-3">
                                    <label class="form-label">QR Code Data *</label>
                                    <input type="text" name="qr_data" class="form-control @error('qr_data') is-invalid @enderror" value="{{ old('qr_data') }}" placeholder="Scanned QR code value" required>
                                    @error('qr_data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif

                            @if($task->code === 'special_challenge' && $challenges->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label">Select Challenge *</label>
                                    <select name="challenge_id" class="form-select @error('challenge_id') is-invalid @enderror" required>
                                        <option value="">-- Select Challenge --</option>
                                        @foreach($challenges as $challenge)
                                            <option value="{{ $challenge->id }}" {{ old('challenge_id') == $challenge->id ? 'selected' : '' }}>
                                                {{ $challenge->title }} (+{{ $challenge->points }} points)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('challenge_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary">Submit for Review</button>
                            <a href="{{ route('user.points.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Instructions</div>
                <div class="card-body">
                    @switch($task->code)
                        @case('check_in_stadium')
                            <ol>
                                <li>Go to a pickleball stadium</li>
                                <li>Take a photo at the location</li>
                                <li>Upload the photo here</li>
                                <li>Wait for admin approval</li>
                            </ol>
                            @break

                        @case('join_fb_group')
                        @case('follow_fb_page')
                            <ol>
                                <li>Go to OnePickleball Facebook</li>
                                <li>Join the group / Follow the page</li>
                                <li>Copy your Facebook profile URL</li>
                                <li>Paste it here and submit</li>
                            </ol>
                            @break

                        @case('subscribe_youtube')
                            <ol>
                                <li>Go to OnePickleball YouTube channel</li>
                                <li>Subscribe to the channel</li>
                                <li>Copy your YouTube profile URL</li>
                                <li>Paste it here and submit</li>
                            </ol>
                            @break

                        @case('follow_tiktok')
                            <ol>
                                <li>Go to OnePickleball TikTok</li>
                                <li>Follow the account</li>
                                <li>Copy your TikTok profile URL</li>
                                <li>Paste it here and submit</li>
                            </ol>
                            @break

                        @case('special_challenge')
                            <ol>
                                <li>Select the challenge you completed</li>
                                <li>Upload proof photo(s)</li>
                                <li>Wait for admin verification</li>
                            </ol>
                            @break

                        @default
                            <p>Follow the task requirements and submit your proof.</p>
                    @endswitch
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

**File**: `resources/views/front/points/history.blade.php`

```blade
@extends('layouts.front')

@section('title', 'Point History')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Point History</h1>
        <span class="badge bg-primary fs-5">[COIN] {{ number_format($balance) }} Points</span>
    </div>

    <div class="mb-3">
        <a href="{{ route('user.points.index') }}" class="btn btn-outline-primary btn-sm">[ARROW_LEFT] Back to Earn Points</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th class="text-end">Points</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                {{ $tx->description }}
                                @if(isset($tx->metadata['task_code']))
                                    <br><small class="text-muted">Task: {{ $tx->metadata['task_code'] }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $tx->isPositive() ? 'bg-success' : 'bg-danger' }}">
                                    {{ $tx->getTypeLabel() }}
                                </span>
                            </td>
                            <td class="text-end {{ $tx->isPositive() ? 'text-success' : 'text-danger' }}">
                                {{ $tx->getFormattedPoints() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No transactions yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
```

**File**: `resources/views/front/points/submissions.blade.php`

```blade
@extends('layouts.front')

@section('title', 'My Submissions')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">My Submissions</h1>

    <div class="mb-3">
        <a href="{{ route('user.points.index') }}" class="btn btn-outline-primary btn-sm">[ARROW_LEFT] Back to Earn Points</a>
    </div>

    <div class="card">
        <div class="card-body">
            @forelse($submissions as $submission)
                <div class="card mb-3 {{ $submission->isPending() ? 'border-warning' : ($submission->isApproved() ? 'border-success' : 'border-danger') }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title">{{ $submission->pointTask->name }}</h6>
                                <p class="card-text small text-muted">
                                    Submitted: {{ $submission->created_at->format('Y-m-d H:i') }}
                                </p>

                                @if($submission->proof_data)
                                    <div class="mt-2">
                                        @if(isset($submission->proof_data['paths']))
                                            <small class="text-muted">{{ count($submission->proof_data['paths']) }} image(s) attached</small>
                                        @elseif(isset($submission->proof_data['url']))
                                            <small class="text-muted">URL: {{ Str::limit($submission->proof_data['url'], 50) }}</small>
                                        @endif
                                    </div>
                                @endif

                                @if($submission->admin_notes)
                                    <div class="mt-2">
                                        <small><strong>Admin Notes:</strong> {{ $submission->admin_notes }}</small>
                                    </div>
                                @endif
                            </div>
                            <div class="text-end">
                                @if($submission->isPending())
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($submission->isApproved())
                                    <span class="badge bg-success">Approved</span>
                                    <br><small class="text-success">+{{ $submission->points_awarded }} points</small>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif

                                @if($submission->reviewed_at)
                                    <br><small class="text-muted">{{ $submission->reviewed_at->format('Y-m-d H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">No submissions yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
```

### Step 4: Homepage Special Challenge Banner (NEW - per spec)

**File**: `resources/views/front/partials/_special_challenge_banner.blade.php`

```blade
@if(isset($specialChallenges) && $specialChallenges->count() > 0)
<div class="special-challenge-banner mb-4">
    <div class="card bg-gradient-warning border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center">
                    <span class="badge bg-danger me-2 pulse-animation">[FIRE] HOT</span>
                    <div>
                        <h6 class="mb-0 text-dark fw-bold">
                            {{ $specialChallenges->first()->title }}
                        </h6>
                        <small class="text-muted">
                            +{{ $specialChallenges->first()->points }} points |
                            Ends {{ $specialChallenges->first()->end_date->format('M d') }}
                        </small>
                    </div>
                </div>
                <a href="{{ route('user.points.index') }}" class="btn btn-dark btn-sm">
                    Join Challenge [ARROW_RIGHT]
                </a>
            </div>
            @if($specialChallenges->count() > 1)
                <div class="mt-2">
                    <small class="text-muted">
                        +{{ $specialChallenges->count() - 1 }} more challenge(s) available
                    </small>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}
.pulse-animation {
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
@endif
```

**Modify `HomeController@index`** to pass challenges:

```php
// In HomeController.php - index() method
public function index()
{
    // ... existing code ...

    // Add special challenges for homepage banner
    $specialChallenges = \App\Models\SpecialChallenge::ongoing()->get();

    return view('front.index', compact(
        // ... existing variables ...
        'specialChallenges'
    ));
}
```

**Include in homepage view** (`resources/views/front/index.blade.php`):

```blade
{{-- Add near top of content section, after hero/header --}}
@include('front.partials._special_challenge_banner')
```

---

## Todo

- [x] Create `UserPointController`
- [x] Add user routes to `web.php`
- [x] Create `front/points/index.blade.php`
- [x] Create `front/points/submit.blade.php`
- [x] Create `front/points/history.blade.php`
- [x] Create `front/points/submissions.blade.php`
- [x] Create `front/partials/_special_challenge_banner.blade.php` (NEW)
- [x] Modify `HomeController@index` to pass challenges (NEW)
- [x] Include banner in homepage view (NEW)
- [x] Add navigation link to user menu
- [x] PHP syntax verified
- [x] Routes registered successfully
- [x] View templates compiled successfully

---

## Success Criteria

1. User can view all available tasks
2. Eligibility status displayed correctly
3. Image upload works for check-in tasks
4. Link submission works for social tasks
5. Special challenges displayed prominently
6. Transaction history paginated
7. Submission status visible
8. **Homepage banner shows ongoing challenges** (per spec requirement)

---

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Large file uploads | Medium | Low | Max 5MB limit enforced |
| Slow page load | Low | Medium | Paginate submissions |

---

## Security Considerations

1. Validate file types server-side
2. Store uploads in `point-submissions/{user_id}/` path
3. Validate URL format for social links
4. CSRF on all forms
5. Auth middleware on all routes

---

## Next Steps

After completion, proceed to [Phase 6: API Integration](./phase-06-api-integration.md)
