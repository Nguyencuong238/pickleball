@extends('home-yard.tournaments.dashboard')

@section('css')
@parent
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-rankings.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-rankings-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-rankings-row-states.css') }}">
@endsection

@section('tournament-content')
    @include('home-yard.tournaments.partials._rankings', [
        'tournament'  => $tournament,
        'categories'  => $categories,
    ])
@endsection

@section('js')
@parent
<script src="{{ asset('assets/js/tournament-rankings.js') }}"></script>
@endsection
