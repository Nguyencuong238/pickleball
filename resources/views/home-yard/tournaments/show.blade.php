@extends('home-yard.tournaments.dashboard')

@section('tournament-content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <h2 style="font-size:1.2rem; font-weight:700; color:#1e293b; margin:0;">
        Tổng quan giải đấu
    </h2>
    <a href="{{ route('tournament-manage.tournaments.index') }}" class="td-btn td-btn-ghost">
        &larr; Danh sách giải đấu
    </a>
</div>

@include('home-yard.tournaments.partials._overview')
@endsection
