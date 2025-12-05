@extends('admin.layouts.app')

@section('title', 'OPRS Detail - ' . $user->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $user->name }} - OPRS Detail</h1>
        <a href="{{ route('admin.oprs.users.index') }}" class="btn btn-outline-secondary">
            [ARROW_LEFT] Back to Users
        </a>
    </div>

    <div class="row">
        {{-- User Info & OPRS Breakdown --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Info</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Registered:</strong> {{ $user->created_at->format('Y-m-d') }}</p>
                    <hr>
                    <h4 class="text-center mb-3">
                        <span class="badge bg-primary fs-3">{{ $user->opr_level }}</span>
                    </h4>
                    <h2 class="text-center text-primary">{{ number_format($user->total_oprs, 0) }}</h2>
                    <p class="text-center text-muted">Total OPRS</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">OPRS Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Elo (70%)</span>
                            <strong>{{ number_format($breakdown['elo']['weighted'], 2) }}</strong>
                        </div>
                        <small class="text-muted">Raw: {{ $breakdown['elo']['raw'] }}</small>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Challenge (20%)</span>
                            <strong>{{ number_format($breakdown['challenge']['weighted'], 2) }}</strong>
                        </div>
                        <small class="text-muted">Raw: {{ $breakdown['challenge']['raw'] }}</small>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Community (10%)</span>
                            <strong>{{ number_format($breakdown['community']['weighted'], 2) }}</strong>
                        </div>
                        <small class="text-muted">Raw: {{ $breakdown['community']['raw'] }}</small>
                    </div>
                </div>
            </div>

            {{-- Manual Adjustment --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Manual Adjustment</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.oprs.users.adjust', $user) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Component</label>
                            <select name="component" class="form-select" required>
                                <option value="challenge">Challenge Score</option>
                                <option value="community">Community Score</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (+/-)</label>
                            <input type="number" name="amount" class="form-control" required min="-1000" max="1000" placeholder="e.g., 10 or -5">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <input type="text" name="reason" class="form-control" required placeholder="Reason for adjustment">
                        </div>
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Apply this adjustment?')">
                            Apply Adjustment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- History & Activities --}}
        <div class="col-md-8">
            {{-- OPRS History --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">OPRS History (Recent 20)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Elo</th>
                                <th>Challenge</th>
                                <th>Community</th>
                                <th>Total</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $h)
                            <tr>
                                <td>{{ $h->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ number_format($h->elo_score, 0) }}</td>
                                <td>{{ number_format($h->challenge_score, 0) }}</td>
                                <td>{{ number_format($h->community_score, 0) }}</td>
                                <td><strong>{{ number_format($h->total_oprs, 0) }}</strong></td>
                                <td><small>{{ $h->change_reason }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">No history</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Challenges --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Challenges</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Score</th>
                                <th>Passed</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($challenges as $c)
                            <tr>
                                <td>{{ $c->created_at->format('Y-m-d') }}</td>
                                <td>{{ $c->challenge_type }}</td>
                                <td>{{ $c->score }}</td>
                                <td>
                                    @if($c->passed)
                                    <span class="badge bg-success">Yes</span>
                                    @else
                                    <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $c->points_earned }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">No challenges</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Activities --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Points</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $a)
                            <tr>
                                <td>{{ $a->created_at->format('Y-m-d') }}</td>
                                <td>{{ $a->activity_type }}</td>
                                <td>{{ $a->points_earned }}</td>
                                <td><small>{{ json_encode($a->metadata) }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">No activities</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
