@extends('admin.layouts.app')

@section('title', 'OPS Level Distribution Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Level Distribution Report</h1>
        <a href="{{ route('admin.oprs.dashboard') }}" class="btn btn-outline-secondary">
            [ARROW_LEFT] Back to Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Level Distribution</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Name</th>
                                <th>OPS Range</th>
                                <th>Players</th>
                                <th>Percentage</th>
                                <th>Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $level => $info)
                            <tr>
                                <td><span class="badge bg-primary fs-6">{{ $level }}</span></td>
                                <td>{{ $info['name'] }}</td>
                                <td>{{ $info['min'] }} - {{ $info['max'] ?? 'Max' }}</td>
                                <td><strong>{{ $info['count'] }}</strong></td>
                                <td>{{ $info['percent'] }}%</td>
                                <td style="width: 30%">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $info['percent'] }}%">
                                            {{ $info['percent'] }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <td colspan="3"><strong>Total</strong></td>
                                <td><strong>{{ $total }}</strong></td>
                                <td>100%</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h3 class="text-center text-primary">{{ $total }}</h3>
                        <p class="text-center text-muted">Total Players with OPS</p>
                    </div>

                    <hr>

                    <h6 class="mb-3">Quick Stats</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Beginner (1.0-2.0):</strong>
                            {{ ($data['1.0']['count'] ?? 0) + ($data['2.0']['count'] ?? 0) }} players
                        </li>
                        <li class="mb-2">
                            <strong>Intermediate (3.0-3.5):</strong>
                            {{ ($data['3.0']['count'] ?? 0) + ($data['3.5']['count'] ?? 0) }} players
                        </li>
                        <li class="mb-2">
                            <strong>Advanced (4.0+):</strong>
                            {{ ($data['4.0']['count'] ?? 0) + ($data['4.5']['count'] ?? 0) + ($data['5.0+']['count'] ?? 0) }} players
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
