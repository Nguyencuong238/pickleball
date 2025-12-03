@extends('layouts.app')

@section('title', 'OCR Matches')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>OCR Matches</h2>
                <div class="btn-group">
                    <a href="{{ route('admin.ocr.disputes.index') }}" class="btn btn-outline-danger">
                        View Disputes
                    </a>
                    <a href="{{ route('admin.ocr.badges.index') }}" class="btn btn-outline-warning">
                        Manage Badges
                    </a>
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">Filter by Status:</label>
                        </div>
                        <div class="col-auto">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    @if($matches->isEmpty())
                        <p class="text-muted text-center py-4">No matches found.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Challenger</th>
                                    <th>Opponent</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Elo Change</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($matches as $match)
                                    <tr>
                                        <td>#{{ $match->id }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($match->match_type) }}</span></td>
                                        <td>{{ $match->challenger->name ?? 'Unknown' }}</td>
                                        <td>{{ $match->opponent->name ?? 'Unknown' }}</td>
                                        <td>
                                            @if($match->challenger_score || $match->opponent_score)
                                                {{ $match->challenger_score }} - {{ $match->opponent_score }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'secondary',
                                                    'accepted' => 'primary',
                                                    'in_progress' => 'info',
                                                    'result_submitted' => 'warning',
                                                    'confirmed' => 'success',
                                                    'disputed' => 'danger',
                                                    'cancelled' => 'dark',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$match->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($match->elo_change)
                                                <span class="text-{{ $match->winner_team === 'challenger' ? 'success' : 'danger' }}">
                                                    {{ $match->winner_team === 'challenger' ? '+' : '-' }}{{ $match->elo_change }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $match->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{ $matches->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
