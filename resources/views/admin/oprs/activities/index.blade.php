@extends('admin.layouts.app')

@section('title', 'OPS Activity Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Activity Management</h1>
        <a href="{{ route('admin.oprs.dashboard') }}" class="btn btn-outline-secondary">
            [ARROW_LEFT] Back to Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Activity Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.oprs.activities.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Activities Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Points</th>
                        <th>Details</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->id }}</td>
                        <td>
                            <a href="{{ route('admin.oprs.users.detail', $activity->user) }}">
                                {{ $activity->user->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $activity->activity_type }}</span>
                        </td>
                        <td>+{{ number_format($activity->points_earned, 0) }}</td>
                        <td>
                            @if($activity->metadata)
                            <small class="text-muted">
                                {{ $activity->metadata['stadium_name'] ?? $activity->metadata['event_name'] ?? $activity->metadata['referred_user_name'] ?? '-' }}
                            </small>
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $activity->id }}">
                                Remove
                            </button>

                            {{-- Delete Modal --}}
                            <div class="modal fade" id="deleteModal{{ $activity->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.oprs.activities.destroy', $activity) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Remove Activity</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>This will remove <strong>{{ $activity->points_earned }}</strong> points from {{ $activity->user->name }}'s community score.</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Reason</label>
                                                    <input type="text" name="reason" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Remove & Deduct Points</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No activities found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $activities->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
