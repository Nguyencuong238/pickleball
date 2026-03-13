@extends('home-yard.tournaments.dashboard')

@section('css')
@parent
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-matches.css') }}">
@endsection

@section('tournament-content')
    @include('home-yard.tournaments.partials._matches', [
        'tournament' => $tournament,
        'categories' => $categories,
    ])
@endsection

@section('js')
@parent
<script src="{{ asset('assets/js/tournament-matches-api.js') }}"></script>
<script src="{{ asset('assets/js/tournament-matches-schedule-mixin.js') }}"></script>
<script src="{{ asset('assets/js/tournament-matches.js') }}"></script>
@endsection
