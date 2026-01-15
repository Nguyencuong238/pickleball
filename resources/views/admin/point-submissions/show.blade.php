@extends('admin.layouts.app')

@section('title', 'Submission Detail')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Submission #{{ Str::limit($submission->uuid, 8) }}</h1>
        <a href="{{ route('admin.point-submissions.index') }}" class="btn btn-outline-secondary">
            &larr; Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Submission Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">User</dt>
                        <dd class="col-sm-9">{{ $submission->user->name ?? 'N/A' }} ({{ $submission->user->email ?? '' }})</dd>

                        <dt class="col-sm-3">Task</dt>
                        <dd class="col-sm-9">{{ $submission->pointTask->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Points</dt>
                        <dd class="col-sm-9">{{ $submission->pointTask->points ?? 0 }}</dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            @if($submission->isPending())
                                <span class="badge bg-warning text-dark">Pending</span>
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
                            <dd class="col-sm-9">{{ $submission->reviewed_at?->format('Y-m-d H:i:s') }}</dd>

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
                                        <img src="{{ Storage::url($path) }}" class="img-fluid img-thumbnail" alt="Proof" referrerpolicy="no-referrer" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                        @elseif(isset($submission->proof_data['url']))
                            <p><a href="{{ $submission->proof_data['url'] }}" target="_blank">{{ $submission->proof_data['url'] }}</a></p>
                        @else
                            <pre class="bg-light p-3 rounded">@json($submission->proof_data, JSON_PRETTY_PRINT)</pre>
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
                    <div class="card-header bg-success text-white">Approve Submission</div>
                    <div class="card-body">
                        <form action="{{ route('admin.point-submissions.approve', $submission) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                Approve (+{{ $submission->pointTask->points ?? 0 }} points)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-danger text-white">Reject Submission</div>
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
</div>
@endsection
