{{-- Tournament Athletes Page - extends dashboard layout --}}
@extends('home-yard.tournaments.dashboard')

@section('css')
@parent
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-athletes.css') }}?v=1.0">
@endsection

@section('tournament-content')
<div class="td-card">
    <div class="td-card-title">Vận động viên</div>
    @include('home-yard.tournaments.partials._athletes')
</div>
@endsection

@section('js')
@parent
<script src="{{ asset('assets/js/tournament-athletes.js') }}?v=1.1"></script>
@endsection
