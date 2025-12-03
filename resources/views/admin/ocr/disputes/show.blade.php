@extends('layouts.app')

@section('title', 'Review Dispute #' . $match->id)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Review Dispute #{{ $match->id }}</h2>
                <a href="{{ route('admin.ocr.disputes.index') }}" class="btn btn-outline-secondary">
                    Back to Disputes
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    {{-- Match Details --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Match Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Type:</th>
                                    <td><span class="badge bg-info">{{ ucfirst($match->match_type) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Scheduled:</th>
                                    <td>{{ $match->scheduled_date?->format('Y-m-d') }} {{ $match->scheduled_time }}</td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>{{ $match->location ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Submitted Score:</th>
                                    <td class="fs-4">{{ $match->challenger_score }} - {{ $match->opponent_score }}</td>
                                </tr>
                                <tr>
                                    <th>Winner:</th>
                                    <td>{{ ucfirst($match->winner_team) }} Team</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Dispute Reason --}}
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Dispute Reason</h5>
                        </div>
                        <div class="card-body">
                            <p>{{ $match->disputed_reason }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    {{-- Teams --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Participants</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <h6>Challenger Team</h6>
                                    <div class="mb-2">
                                        <strong>{{ $match->challenger->name ?? 'Unknown' }}</strong>
                                        <br><small class="text-muted">Elo: {{ $match->challenger->elo_rating ?? '-' }}</small>
                                    </div>
                                    @if($match->challengerPartner)
                                        <div>
                                            {{ $match->challengerPartner->name }}
                                            <br><small class="text-muted">Elo: {{ $match->challengerPartner->elo_rating }}</small>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-6">
                                    <h6>Opponent Team</h6>
                                    <div class="mb-2">
                                        <strong>{{ $match->opponent->name ?? 'Unknown' }}</strong>
                                        <br><small class="text-muted">Elo: {{ $match->opponent->elo_rating ?? '-' }}</small>
                                    </div>
                                    @if($match->opponentPartner)
                                        <div>
                                            {{ $match->opponentPartner->name }}
                                            <br><small class="text-muted">Elo: {{ $match->opponentPartner->elo_rating }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Evidence --}}
                    @if($match->media->isNotEmpty())
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Evidence ({{ $match->media->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($match->media as $media)
                                        <div class="col-4">
                                            @if(Str::startsWith($media->mime_type, 'image'))
                                                <a href="{{ $media->getUrl() }}" target="_blank">
                                                    <img src="{{ $media->getUrl() }}" class="img-fluid rounded" alt="Evidence">
                                                </a>
                                            @else
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-outline-secondary w-100">
                                                    [VIDEO] View
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Resolution Actions --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Resolution Options</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Confirm Original Result --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <h6>Confirm Original Result</h6>
                                    <p class="small text-muted">Accept the submitted score and update Elo ratings</p>
                                    <form action="{{ route('admin.ocr.disputes.confirm', $match) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Confirm the original result?')">
                                            Confirm Result
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Override Result --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <h6>Override Result</h6>
                                    <p class="small text-muted">Set a different score and update Elo</p>
                                    <form action="{{ route('admin.ocr.disputes.override', $match) }}" method="POST">
                                        @csrf
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <input type="number" name="challenger_score" class="form-control" placeholder="Challenger" min="0" max="99" required>
                                            </div>
                                            <div class="col-6">
                                                <input type="number" name="opponent_score" class="form-control" placeholder="Opponent" min="0" max="99" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-warning" onclick="return confirm('Override with new score?')">
                                            Override
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Cancel Match --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-danger">
                                <div class="card-body text-center">
                                    <h6>Cancel Match</h6>
                                    <p class="small text-muted">Void the match entirely, no Elo changes</p>
                                    <form action="{{ route('admin.ocr.disputes.cancel', $match) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this match?')">
                                            Cancel Match
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
