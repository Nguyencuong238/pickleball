@extends('admin.layouts.app')

@section('title', 'OPS Challenge Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Challenge Management</h1>
        <a href="{{ route('admin.oprs.dashboard') }}" class="btn btn-outline-secondary">
            [ARROW_LEFT] Back to Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>Passed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Challenge Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.oprs.challenges.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Challenges Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Score</th>
                        <th>Passed</th>
                        <th>Points</th>
                        <th>Verified</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($challenges as $challenge)
                    <tr>
                        <td>{{ $challenge->id }}</td>
                        <td>
                            <a href="{{ route('admin.oprs.users.detail', $challenge->user) }}">
                                {{ $challenge->user->name }}
                            </a>
                        </td>
                        <td>{{ $challenge->challenge_type }}</td>
                        <td>{{ $challenge->score }}</td>
                        <td>
                            @if($challenge->passed)
                            <span class="badge bg-success">Yes</span>
                            @else
                            <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>{{ number_format($challenge->points_earned, 0) }}</td>
                        <td>
                            @if($challenge->verified_at)
                            <span class="badge bg-success">Verified</span>
                            @else
                            <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                        <td>{{ $challenge->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            @if(!$challenge->verified_at)
                            <form action="{{ route('admin.oprs.challenges.verify', $challenge) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Verify</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $challenge->id }}">
                                Reject
                            </button>

                            {{-- Reject Modal --}}
                            <div class="modal fade" id="rejectModal{{ $challenge->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.oprs.challenges.reject', $challenge) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Challenge</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Reason</label>
                                                    <input type="text" name="reason" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject & Remove Points</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No challenges found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $challenges->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
