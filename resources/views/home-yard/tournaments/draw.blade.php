@extends('home-yard.tournaments.dashboard')

@section('css')
@parent
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-athletes.css') }}?v=1.0">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-draw.css') }}">
@endsection

@section('tournament-content')
    @include('home-yard.tournaments.partials._draw', [
        'tournament' => $tournament,
        'categories' => $categories,
    ])
@endsection

@section('js')
@parent
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script src="{{ asset('assets/js/tournament-draw-group-setup-mixin.js') }}"></script>
<script src="{{ asset('assets/js/tournament-draw-reset-mixin.js') }}"></script>
<script src="{{ asset('assets/js/tournament-draw-manual-sortable-mixin.js') }}"></script>
<script src="{{ asset('assets/js/tournament-draw.js') }}"></script>
@endsection
