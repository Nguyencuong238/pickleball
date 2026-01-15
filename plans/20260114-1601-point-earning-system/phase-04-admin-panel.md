# Phase 4: Admin Panel

**Parent**: [plan.md](./plan.md)
**Date**: 2026-01-14 | **Priority**: High | **Status**: COMPLETED ✅

## Context

- Depends on: [Phase 2: Services Core](./phase-02-services-core.md)
- Blocks: None
- Related: [Admin Views](../resources/views/admin/)

## Overview

Create admin panel for managing point submissions (approval queue), point tasks (configuration), and special challenges (CRUD). Follow existing admin panel patterns.

## Key Insights

1. Follow OprsController pattern for admin controllers
2. Use existing admin layout (`admin.dashboard` reference)
3. All admins can approve (no specific role check beyond admin)
4. Bulk actions useful for high-volume approval

---

## Requirements

### PointSubmissionController

| Route | Method | Action |
|-------|--------|--------|
| GET /admin/point-submissions | index | List pending submissions |
| GET /admin/point-submissions/{uuid} | show | View submission detail |
| POST /admin/point-submissions/{uuid}/approve | approve | Approve submission |
| POST /admin/point-submissions/{uuid}/reject | reject | Reject submission |
| POST /admin/point-submissions/bulk-approve | bulkApprove | Bulk approve selected |

### PointTaskController

| Route | Method | Action |
|-------|--------|--------|
| GET /admin/point-tasks | index | List all tasks |
| PUT /admin/point-tasks/{id} | update | Update points/active status |

### SpecialChallengeController

| Route | Method | Action |
|-------|--------|--------|
| GET /admin/special-challenges | index | List challenges |
| GET /admin/special-challenges/create | create | Create form |
| POST /admin/special-challenges | store | Store new challenge |
| GET /admin/special-challenges/{id}/edit | edit | Edit form |
| PUT /admin/special-challenges/{id} | update | Update challenge |
| DELETE /admin/special-challenges/{id} | destroy | Delete challenge |

---

## Architecture

```
app/Http/Controllers/Admin/
├── PointSubmissionController.php
├── PointTaskController.php
└── SpecialChallengeController.php

resources/views/admin/
├── point-submissions/
│   ├── index.blade.php
│   └── show.blade.php
├── point-tasks/
│   └── index.blade.php
└── special-challenges/
    ├── index.blade.php
    ├── create.blade.php
    └── edit.blade.php
```

---

## Related Code Files

**Reference Controllers**:
- `app/Http/Controllers/Admin/OprsController.php` - Pattern for admin controllers
- `app/Http/Controllers/Admin/OprsChallengeController.php` - CRUD pattern

**Reference Views**:
- `resources/views/admin/oprs/challenges/index.blade.php`
- `resources/views/admin/oprs/users/index.blade.php`

---

## Implementation Steps

### Step 1: Create PointSubmissionController

**File**: `app/Http/Controllers/Admin/PointSubmissionController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Services\PointSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointSubmissionController extends Controller
{
    public function __construct(
        private PointSubmissionService $submissionService
    ) {}

    /**
     * List pending submissions
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['task_code', 'status', 'user_id']);
        $submissions = $this->submissionService->getSubmissions($filters, 20);
        $stats = $this->submissionService->getStats();
        $tasks = PointTask::where('requires_approval', true)->get();

        return view('admin.point-submissions.index', compact('submissions', 'stats', 'tasks'));
    }

    /**
     * View submission detail
     */
    public function show(PointSubmission $submission): View
    {
        $submission->load(['user', 'pointTask', 'admin']);

        return view('admin.point-submissions.show', compact('submission'));
    }

    /**
     * Approve submission
     */
    public function approve(Request $request, PointSubmission $submission): RedirectResponse
    {
        try {
            $this->submissionService->approve(
                $submission,
                auth()->user(),
                $request->input('notes')
            );

            return redirect()
                ->route('admin.point-submissions.index')
                ->with('success', 'Submission approved. ' . $submission->pointTask->points . ' points awarded.');
        } catch (\Exception $e) {
            return back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject submission
     */
    public function reject(Request $request, PointSubmission $submission): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->submissionService->reject(
                $submission,
                auth()->user(),
                $request->input('reason')
            );

            return redirect()
                ->route('admin.point-submissions.index')
                ->with('success', 'Submission rejected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve submissions
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'submission_ids' => 'required|array',
            'submission_ids.*' => 'exists:point_submissions,id',
        ]);

        $count = 0;
        $failed = 0;

        foreach ($request->input('submission_ids') as $id) {
            $submission = PointSubmission::find($id);
            if ($submission && $submission->isPending()) {
                try {
                    $this->submissionService->approve($submission, auth()->user());
                    $count++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }
        }

        $message = "Approved {$count} submissions.";
        if ($failed > 0) {
            $message .= " {$failed} failed.";
        }

        return back()->with('success', $message);
    }
}
```

### Step 2: Create PointTaskController

**File**: `app/Http/Controllers/Admin/PointTaskController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointTaskController extends Controller
{
    /**
     * List all point tasks
     */
    public function index(Request $request): View
    {
        $query = PointTask::query()->orderBy('role')->orderBy('category');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $tasks = $query->get();

        // Group by role for display
        $tasksByRole = $tasks->groupBy('role');

        return view('admin.point-tasks.index', compact('tasks', 'tasksByRole'));
    }

    /**
     * Update task configuration
     */
    public function update(Request $request, PointTask $pointTask): RedirectResponse
    {
        $request->validate([
            'points' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $pointTask->update([
            'points' => $request->input('points'),
            'is_active' => $request->boolean('is_active'),
            'description' => $request->input('description'),
        ]);

        return back()->with('success', "Task '{$pointTask->name}' updated.");
    }
}
```

### Step 3: Create SpecialChallengeController

**File**: `app/Http/Controllers/Admin/SpecialChallengeController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialChallengeController extends Controller
{
    /**
     * List challenges
     */
    public function index(): View
    {
        $challenges = SpecialChallenge::orderByDesc('created_at')->paginate(20);

        return view('admin.special-challenges.index', compact('challenges'));
    }

    /**
     * Create form
     */
    public function create(): View
    {
        return view('admin.special-challenges.create');
    }

    /**
     * Store new challenge
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'points' => 'required|integer|min:1|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        SpecialChallenge::create($validated);

        return redirect()
            ->route('admin.special-challenges.index')
            ->with('success', 'Challenge created successfully.');
    }

    /**
     * Edit form
     */
    public function edit(SpecialChallenge $specialChallenge): View
    {
        return view('admin.special-challenges.edit', compact('specialChallenge'));
    }

    /**
     * Update challenge
     */
    public function update(Request $request, SpecialChallenge $specialChallenge): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'points' => 'required|integer|min:1|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $specialChallenge->update($validated);

        return redirect()
            ->route('admin.special-challenges.index')
            ->with('success', 'Challenge updated successfully.');
    }

    /**
     * Delete challenge
     */
    public function destroy(SpecialChallenge $specialChallenge): RedirectResponse
    {
        $specialChallenge->delete();

        return redirect()
            ->route('admin.special-challenges.index')
            ->with('success', 'Challenge deleted.');
    }
}
```

### Step 4: Add Routes

**File**: `routes/web.php` (add to admin group)

```php
// Point Earning System - Admin
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    // Point Submissions
    Route::get('point-submissions', [PointSubmissionController::class, 'index'])
        ->name('point-submissions.index');
    Route::get('point-submissions/{submission}', [PointSubmissionController::class, 'show'])
        ->name('point-submissions.show');
    Route::post('point-submissions/{submission}/approve', [PointSubmissionController::class, 'approve'])
        ->name('point-submissions.approve');
    Route::post('point-submissions/{submission}/reject', [PointSubmissionController::class, 'reject'])
        ->name('point-submissions.reject');
    Route::post('point-submissions/bulk-approve', [PointSubmissionController::class, 'bulkApprove'])
        ->name('point-submissions.bulk-approve');

    // Point Tasks
    Route::get('point-tasks', [PointTaskController::class, 'index'])
        ->name('point-tasks.index');
    Route::put('point-tasks/{pointTask}', [PointTaskController::class, 'update'])
        ->name('point-tasks.update');

    // Special Challenges
    Route::resource('special-challenges', SpecialChallengeController::class);
});
```

### Step 5: Create Views

**File**: `resources/views/admin/point-submissions/index.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Point Submissions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Point Submissions</h1>
        <div class="badge-group">
            <span class="badge bg-warning">Pending: {{ $stats['pending'] }}</span>
            <span class="badge bg-success">Approved Today: {{ $stats['approved_today'] }}</span>
            <span class="badge bg-danger">Rejected Today: {{ $stats['rejected_today'] }}</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="task_code" class="form-select">
                        <option value="">All Tasks</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->code }}" {{ request('task_code') == $task->code ? 'selected' : '' }}>
                                {{ $task->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.point-submissions.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="card">
        <div class="card-body">
            <form id="bulk-form" action="{{ route('admin.point-submissions.bulk-approve') }}" method="POST">
                @csrf
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>User</th>
                            <th>Task</th>
                            <th>Points</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $submission)
                            <tr>
                                <td>
                                    @if($submission->isPending())
                                        <input type="checkbox" name="submission_ids[]" value="{{ $submission->id }}">
                                    @endif
                                </td>
                                <td>{{ $submission->user->name }}</td>
                                <td>{{ $submission->pointTask->name }}</td>
                                <td>{{ $submission->pointTask->points }}</td>
                                <td>{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($submission->isPending())
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($submission->isApproved())
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.point-submissions.show', $submission) }}" class="btn btn-sm btn-info">View</a>
                                    @if($submission->isPending())
                                        <button type="button" class="btn btn-sm btn-success" onclick="quickApprove('{{ $submission->uuid }}')">Approve</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No submissions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success" id="bulk-approve-btn" disabled>
                        Bulk Approve Selected
                    </button>
                    {{ $submissions->links() }}
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('input[name="submission_ids[]"]').forEach(cb => {
        cb.checked = this.checked;
    });
    updateBulkButton();
});

document.querySelectorAll('input[name="submission_ids[]"]').forEach(cb => {
    cb.addEventListener('change', updateBulkButton);
});

function updateBulkButton() {
    const checked = document.querySelectorAll('input[name="submission_ids[]"]:checked').length;
    document.getElementById('bulk-approve-btn').disabled = checked === 0;
}

function quickApprove(uuid) {
    if (confirm('Approve this submission?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/point-submissions/${uuid}/approve`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
```

**File**: `resources/views/admin/point-submissions/show.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Submission Detail')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Submission #{{ $submission->uuid }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">User</dt>
                        <dd class="col-sm-9">{{ $submission->user->name }} ({{ $submission->user->email }})</dd>

                        <dt class="col-sm-3">Task</dt>
                        <dd class="col-sm-9">{{ $submission->pointTask->name }}</dd>

                        <dt class="col-sm-3">Points</dt>
                        <dd class="col-sm-9">{{ $submission->pointTask->points }}</dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            @if($submission->isPending())
                                <span class="badge bg-warning">Pending</span>
                            @elseif($submission->isApproved())
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Submitted At</dt>
                        <dd class="col-sm-9">{{ $submission->created_at->format('Y-m-d H:i:s') }}</dd>

                        @if($submission->admin)
                            <dt class="col-sm-3">Reviewed By</dt>
                            <dd class="col-sm-9">{{ $submission->admin->name }}</dd>

                            <dt class="col-sm-3">Reviewed At</dt>
                            <dd class="col-sm-9">{{ $submission->reviewed_at->format('Y-m-d H:i:s') }}</dd>

                            @if($submission->admin_notes)
                                <dt class="col-sm-3">Notes</dt>
                                <dd class="col-sm-9">{{ $submission->admin_notes }}</dd>
                            @endif
                        @endif
                    </dl>

                    <hr>

                    <h6>Proof Data</h6>
                    @if($submission->proof_data)
                        @if(isset($submission->proof_data['paths']))
                            <div class="row">
                                @foreach($submission->proof_data['paths'] as $path)
                                    <div class="col-md-4 mb-2">
                                        <img src="{{ Storage::url($path) }}" class="img-fluid img-thumbnail" alt="Proof">
                                    </div>
                                @endforeach
                            </div>
                        @elseif(isset($submission->proof_data['url']))
                            <p><a href="{{ $submission->proof_data['url'] }}" target="_blank">{{ $submission->proof_data['url'] }}</a></p>
                        @else
                            <pre>{{ json_encode($submission->proof_data, JSON_PRETTY_PRINT) }}</pre>
                        @endif
                    @else
                        <p class="text-muted">No proof data</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($submission->isPending())
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">Approve</div>
                    <div class="card-body">
                        <form action="{{ route('admin.point-submissions.approve', $submission) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Approve (+{{ $submission->pointTask->points }} points)</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-danger text-white">Reject</div>
                    <div class="card-body">
                        <form action="{{ route('admin.point-submissions.reject', $submission) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Reason (required)</label>
                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Reject</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('admin.point-submissions.index') }}" class="btn btn-secondary mt-3">[ARROW_LEFT] Back to List</a>
</div>
@endsection
```

**File**: `resources/views/admin/point-tasks/index.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Point Tasks')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Point Tasks Configuration</h1>

    @foreach($tasksByRole as $role => $tasks)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ ucfirst($role) }} Tasks</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Points</th>
                            <th>Frequency</th>
                            <th>Approval</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td><code>{{ $task->code }}</code></td>
                                <td>{{ $task->name }}</td>
                                <td>
                                    <form action="{{ route('admin.point-tasks.update', $task) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="points" value="{{ $task->points }}" class="form-control form-control-sm d-inline" style="width: 80px;">
                                        <input type="hidden" name="is_active" value="{{ $task->is_active ? '1' : '0' }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                    </form>
                                </td>
                                <td>{{ $task->frequency }}</td>
                                <td>{{ $task->requires_approval ? 'Yes' : 'No' }}</td>
                                <td>
                                    <form action="{{ route('admin.point-tasks.update', $task) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="points" value="{{ $task->points }}">
                                        <input type="hidden" name="is_active" value="{{ $task->is_active ? '0' : '1' }}">
                                        <button type="submit" class="btn btn-sm {{ $task->is_active ? 'btn-success' : 'btn-secondary' }}">
                                            {{ $task->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td>{{ $task->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
```

**File**: `resources/views/admin/special-challenges/index.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Special Challenges')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Special Challenges</h1>
        <a href="{{ route('admin.special-challenges.create') }}" class="btn btn-primary">+ Create Challenge</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Points</th>
                        <th>Period</th>
                        <th>Participants</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($challenges as $challenge)
                        <tr>
                            <td>{{ $challenge->title }}</td>
                            <td>{{ $challenge->points }}</td>
                            <td>{{ $challenge->start_date->format('Y-m-d') }} - {{ $challenge->end_date->format('Y-m-d') }}</td>
                            <td>
                                {{ $challenge->getParticipantCount() }}
                                @if($challenge->max_participants)
                                    / {{ $challenge->max_participants }}
                                @endif
                            </td>
                            <td>
                                @if($challenge->isOngoing())
                                    <span class="badge bg-success">Ongoing</span>
                                @elseif($challenge->end_date < now())
                                    <span class="badge bg-secondary">Ended</span>
                                @elseif(!$challenge->is_active)
                                    <span class="badge bg-warning">Inactive</span>
                                @else
                                    <span class="badge bg-info">Upcoming</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.special-challenges.edit', $challenge) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('admin.special-challenges.destroy', $challenge) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this challenge?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No challenges found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $challenges->links() }}
        </div>
    </div>
</div>
@endsection
```

**File**: `resources/views/admin/special-challenges/create.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Create Special Challenge')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Create Special Challenge</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.special-challenges.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Points *</label>
                        <input type="number" name="points" class="form-control @error('points') is-invalid @enderror" value="{{ old('points', 15) }}" min="1" required>
                        @error('points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Max Participants (leave empty for unlimited)</label>
                        <input type="number" name="max_participants" class="form-control @error('max_participants') is-invalid @enderror" value="{{ old('max_participants') }}" min="1">
                        @error('max_participants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Challenge</button>
                    <a href="{{ route('admin.special-challenges.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

**File**: `resources/views/admin/special-challenges/edit.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Edit Special Challenge')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Edit Special Challenge</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.special-challenges.update', $specialChallenge) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $specialChallenge->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $specialChallenge->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Points *</label>
                        <input type="number" name="points" class="form-control @error('points') is-invalid @enderror" value="{{ old('points', $specialChallenge->points) }}" min="1" required>
                        @error('points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $specialChallenge->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $specialChallenge->end_date->format('Y-m-d')) }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Max Participants</label>
                        <input type="number" name="max_participants" class="form-control @error('max_participants') is-invalid @enderror" value="{{ old('max_participants', $specialChallenge->max_participants) }}" min="1">
                        @error('max_participants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $specialChallenge->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Participants:</strong> {{ $specialChallenge->getParticipantCount() }}
                    @if($specialChallenge->max_participants)
                        / {{ $specialChallenge->max_participants }}
                    @endif
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Challenge</button>
                    <a href="{{ route('admin.special-challenges.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

## Implementation Summary

**Date Completed**: 2026-01-14

### Controllers Created (3 files)
- `app/Http/Controllers/Admin/PointSubmissionController.php` - list, show, approve, reject, bulk approve
- `app/Http/Controllers/Admin/PointTaskController.php` - list, update points/active status
- `app/Http/Controllers/Admin/SpecialChallengeController.php` - full CRUD operations

### Routes Added
- GET `/admin/point-submissions` - list pending submissions
- GET `/admin/point-submissions/{submission}` - view submission detail
- POST `/admin/point-submissions/{submission}/approve` - approve submission
- POST `/admin/point-submissions/{submission}/reject` - reject submission
- POST `/admin/point-submissions/bulk-approve` - bulk approve selected
- GET `/admin/point-tasks` - list all tasks with filtering
- PUT `/admin/point-tasks/{pointTask}` - update points and active status
- Resource routes for special-challenges (index, create, store, edit, update, destroy)

### Views Created (6 files)
- `resources/views/admin/point-submissions/index.blade.php` - pending queue with filters, bulk actions, stats badges
- `resources/views/admin/point-submissions/show.blade.php` - detail view with proof display, approve/reject forms
- `resources/views/admin/point-tasks/index.blade.php` - grouped by role with inline edit capability
- `resources/views/admin/special-challenges/index.blade.php` - list with participants count, status badges
- `resources/views/admin/special-challenges/create.blade.php` - form for creating challenge
- `resources/views/admin/special-challenges/edit.blade.php` - form for editing challenge with participant info

### Navigation
- Added Point Earning section to admin sidebar with links:
  - Submissions link with pending count badge
  - Point Tasks link
  - Special Challenges link

### Validation & Testing
- All routes verified working
- All views compiled successfully
- Sidebar navigation added
- Bulk approve functionality with checkbox select-all
- Image/proof display on submission detail
- Role-based filtering on point tasks

---

## Todo

- [x] Create `PointSubmissionController`
- [x] Create `PointTaskController`
- [x] Create `SpecialChallengeController`
- [x] Add admin routes to `web.php`
- [x] Create `point-submissions/index.blade.php`
- [x] Create `point-submissions/show.blade.php`
- [x] Create `point-tasks/index.blade.php`
- [x] Create `special-challenges/index.blade.php`
- [x] Create `special-challenges/create.blade.php`
- [x] Create `special-challenges/edit.blade.php`
- [x] Add navigation links to admin sidebar
- [x] Test approval/rejection flow
- [x] Test bulk approve
- [x] Test task configuration updates

---

## Success Criteria

1. Admin can view pending submissions queue ✅
2. Admin can approve/reject with notes ✅
3. Bulk approve works correctly ✅
4. Task points configurable ✅
5. Task enable/disable works ✅
6. Special challenges CRUD functional ✅
7. Participant count displayed ✅

---

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Image storage issues | Low | Medium | Validate paths exist |
| Bulk approve timeout | Low | Medium | Queue large batches |

---

## Security Considerations

1. Admin role middleware on all routes
2. CSRF protection on all forms
3. Validate submission ownership before display
4. Escape user-generated content in views

---

## Next Steps

After completion, proceed to [Phase 5: User Interface](./phase-05-user-interface.md)
