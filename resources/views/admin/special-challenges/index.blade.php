@extends('admin.layouts.app')

@section('title', 'Special Challenges')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Special Challenges</h1>
        <a href="{{ route('admin.special-challenges.create') }}" class="btn btn-primary">+ Create Challenge</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover">
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
                                    <span class="badge bg-warning text-dark">Inactive</span>
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
