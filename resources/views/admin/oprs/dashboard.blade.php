@extends('admin.layouts.app')

@section('title', 'OPRS Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>OPRS Dashboard</h1>
        <form action="{{ route('admin.oprs.recalculate') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Recalculate OPRS for all users?')">
                [REFRESH] Recalculate All
            </button>
        </form>
    </div>

    {{-- Quick Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Players</h5>
                    <p class="display-4">{{ number_format($stats['total_users']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Pending Challenges</h5>
                    <p class="display-4">{{ $stats['pending_challenges'] }}</p>
                    @if($stats['pending_challenges'] > 0)
                    <a href="{{ route('admin.oprs.challenges.index', ['status' => 'pending']) }}" class="text-dark">View all</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Activities Today</h5>
                    <p class="display-4">{{ $stats['recent_activities'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Elite Players</h5>
                    <p class="display-4">{{ $stats['level_distribution']['5.0+'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Level Distribution --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Level Distribution</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Name</th>
                                <th>Players</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = array_sum($stats['level_distribution']); @endphp
                            @foreach(\App\Services\OprsService::OPR_LEVELS as $level => $info)
                            @php $count = $stats['level_distribution'][$level] ?? 0; @endphp
                            <tr>
                                <td><span class="badge bg-secondary">{{ $level }}</span></td>
                                <td>{{ $info['name'] }}</td>
                                <td>{{ $count }}</td>
                                <td>{{ $total > 0 ? round(($count/$total)*100, 1) : 0 }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <td colspan="2"><strong>Total</strong></td>
                                <td><strong>{{ $total }}</strong></td>
                                <td>100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Players --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Top 10 Players</h5>
                    <a href="{{ route('admin.oprs.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Player</th>
                                <th>OPRS</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['top_players'] as $index => $player)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.oprs.users.detail', $player) }}">
                                        {{ $player->name }}
                                    </a>
                                </td>
                                <td>{{ number_format($player->total_oprs, 0) }}</td>
                                <td><span class="badge bg-primary">{{ $player->opr_level }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.oprs.users.index') }}" class="btn btn-outline-primary me-2">
                        [USERS] Manage Users
                    </a>
                    <a href="{{ route('admin.oprs.challenges.index') }}" class="btn btn-outline-warning me-2">
                        [TARGET] Verify Challenges
                    </a>
                    <a href="{{ route('admin.oprs.activities.index') }}" class="btn btn-outline-info me-2">
                        [LIST] View Activities
                    </a>
                    <a href="{{ route('admin.oprs.reports.levels') }}" class="btn btn-outline-secondary">
                        [CHART] Level Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
