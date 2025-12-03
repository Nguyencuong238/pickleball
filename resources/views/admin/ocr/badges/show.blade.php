@extends('layouts.app')

@section('title', $info['name'] . ' Badge - Users')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <div class="badge-icon me-3 p-3 rounded-circle bg-warning text-white">
                        {{ $info['icon'] }}
                    </div>
                    <div>
                        <h2 class="mb-0">{{ $info['name'] }}</h2>
                        <p class="text-muted mb-0">{{ $info['description'] }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.ocr.badges.index') }}" class="btn btn-outline-secondary">
                    Back to Badges
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Users with this badge ({{ $users->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($users->isEmpty())
                        <p class="text-muted">No users have earned this badge yet.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Elo Rating</th>
                                    <th>Earned At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $user->elo_rating }}</span>
                                            <small class="text-muted">({{ $user->elo_rank }})</small>
                                        </td>
                                        <td>
                                            @php
                                                $badge = $user->badges->first();
                                            @endphp
                                            {{ $badge ? $badge->earned_at->format('Y-m-d H:i') : '-' }}
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.ocr.badges.revoke') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <input type="hidden" name="badge_type" value="{{ $badgeType }}">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Revoke badge from {{ $user->name }}?')">
                                                    Revoke
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
