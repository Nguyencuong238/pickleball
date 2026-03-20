@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-cards.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-forms.css') }}?v=1.1">
@endsection

@section('content')
<div class="main-content" style="padding: 0; flex: 1;">
    <div class="td-layout td-body-padding">

        {{-- Sidebar (desktop) --}}
        @include('home-yard.tournaments.partials._sidebar')

        {{-- Main content area --}}
        <main class="td-content">
            @if(session('success'))
                <div class="td-alert td-alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="td-alert td-alert-error">{{ session('error') }}</div>
            @endif

            @yield('tournament-content')
        </main>

    </div>

    {{-- Mobile bottom tabs --}}
    @include('home-yard.tournaments.partials._mobile-tabs')
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/tournament-dashboard.js') }}"></script>
@endsection
