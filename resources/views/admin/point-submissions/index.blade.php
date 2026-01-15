@extends('admin.layouts.app')

@section('title', 'Point Submissions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Point Submissions</h1>
        <div class="badge-group">
            <span class="badge bg-warning text-dark">Pending: {{ $stats['pending'] ?? 0 }}</span>
            <span class="badge bg-success">Approved Today: {{ $stats['approved_today'] ?? 0 }}</span>
            <span class="badge bg-danger">Rejected Today: {{ $stats['rejected_today'] ?? 0 }}</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Task Type</label>
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
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.point-submissions.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Submissions Table --}}
    <div class="card">
        <div class="card-body">
            <form id="bulk-form" action="{{ route('admin.point-submissions.bulk-approve') }}" method="POST">
                @csrf
                <table class="table table-striped table-hover">
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
                                <td>{{ $submission->user->name ?? 'N/A' }}</td>
                                <td>{{ $submission->pointTask->name ?? 'N/A' }}</td>
                                <td>{{ $submission->pointTask->points ?? 0 }}</td>
                                <td>{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($submission->isPending())
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($submission->isApproved())
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.point-submissions.show', $submission) }}" class="btn btn-sm btn-info">View</a>
                                    @if($submission->isPending())
                                        <form action="{{ route('admin.point-submissions.approve', $submission) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this submission?')">Approve</button>
                                        </form>
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

                <div class="d-flex justify-content-between align-items-center">
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
</script>
@endpush
@endsection
