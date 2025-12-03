@extends('layouts.app')

@section('title', 'OCR Disputed Matches')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">OCR Disputed Matches</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    @if($disputes->isEmpty())
                        <p class="text-muted text-center py-4">No disputed matches at this time.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Challenger</th>
                                    <th>Opponent</th>
                                    <th>Score</th>
                                    <th>Dispute Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disputes as $match)
                                    <tr>
                                        <td>#{{ $match->id }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($match->match_type) }}</span></td>
                                        <td>
                                            {{ $match->challenger->name ?? 'Unknown' }}
                                            @if($match->challengerPartner)
                                                <br><small class="text-muted">+ {{ $match->challengerPartner->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $match->opponent->name ?? 'Unknown' }}
                                            @if($match->opponentPartner)
                                                <br><small class="text-muted">+ {{ $match->opponentPartner->name }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $match->challenger_score }} - {{ $match->opponent_score }}</td>
                                        <td>
                                            <small>{{ Str::limit($match->disputed_reason, 50) }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ocr.disputes.show', $match) }}" class="btn btn-sm btn-primary">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{ $disputes->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
