@extends('admin.layouts.app')

@section('title', 'Point Tasks')

@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4">Point Tasks Configuration</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @foreach($tasksByRole as $role => $roleTasks)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ ucfirst($role) }} Tasks</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
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
                            @foreach($roleTasks as $task)
                                <tr>
                                    <td><code>{{ $task->code }}</code></td>
                                    <td>{{ $task->name }}</td>
                                    <td>
                                        <form action="{{ route('admin.point-tasks.update', $task) }}" method="POST" class="d-inline-flex align-items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="number" name="points" value="{{ $task->points }}" class="form-control form-control-sm" style="width: 80px;" min="1" max="1000">
                                            <input type="hidden" name="is_active" value="{{ $task->is_active ? '1' : '0' }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $task->frequency }}</span>
                                    </td>
                                    <td>
                                        @if($task->requires_approval)
                                            <span class="badge bg-info">Yes</span>
                                        @else
                                            <span class="badge bg-light text-dark">No</span>
                                        @endif
                                    </td>
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
                                    <td class="text-muted small">{{ $task->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
