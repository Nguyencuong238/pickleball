@extends('admin.layouts.app')

@section('title', 'OPS User Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>OPS User Management</h1>
        <a href="{{ route('admin.oprs.dashboard') }}" class="btn btn-outline-secondary">
            [ARROW_LEFT] Back to Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name or email...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">OPR Level</label>
                    <select name="level" class="form-select">
                        <option value="">All Levels</option>
                        @foreach($levels as $level => $info)
                        <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                            {{ $level }} - {{ $info['name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.oprs.users.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>OPS</th>
                        <th>Level</th>
                        <th>Elo</th>
                        <th>Challenge</th>
                        <th>Community</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            <br><small class="text-muted">{{ $user->email }}</small>
                        </td>
                        <td><strong>{{ number_format($user->total_oprs, 0) }}</strong></td>
                        <td><span class="badge bg-primary">{{ $user->opr_level }}</span></td>
                        <td>{{ $user->elo_rating }}</td>
                        <td>{{ number_format($user->challenge_score, 0) }}</td>
                        <td>{{ number_format($user->community_score, 0) }}</td>
                        <td>
                            <a href="{{ route('admin.oprs.users.detail', $user) }}" class="btn btn-sm btn-outline-primary">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
