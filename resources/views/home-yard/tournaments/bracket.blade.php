@extends('home-yard.tournaments.dashboard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-cards.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-forms.css') }}?v=1.1">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-matches.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/bracket-tree.css') }}">
@endsection

@section('tournament-content')
    @include('home-yard.tournaments.partials._bracket-tree')
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/tournament-dashboard.js') }}"></script>
<script src="{{ asset('assets/js/bracket-data-fetcher.js') }}"></script>
<script src="{{ asset('assets/js/bracket-score-entry.js') }}?v=1.0"></script>
<script src="{{ asset('assets/js/bracket-swap-editor.js') }}?v=1.2"></script>
<script src="{{ asset('assets/js/bracket-match-editor.js') }}?v=1.3"></script>
<script src="{{ asset('assets/js/bracket-manager.js') }}"></script>
@endsection
