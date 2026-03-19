{{-- Tournament Dashboard Mobile Bottom Tabs --}}
@php
    $currentRoute = request()->route()->getName();
@endphp

<nav class="td-mobile-tabs">
    <a href="{{ route('tournament-manage.tournaments.show', $tournament) }}"
       class="td-tab-item {{ $currentRoute === 'tournament-manage.tournaments.show' ? 'active' : '' }}">
        <span class="td-tab-icon">&#9776;</span>
        <span>Tổng quan</span>
    </a>

    <a href="{{ route('tournament-manage.athletes.index', $tournament) }}"
       class="td-tab-item {{ str_contains($currentRoute, 'athletes') ? 'active' : '' }}">
        <span class="td-tab-icon">&#128100;</span>
        <span>VĐV</span>
    </a>

    <a href="{{ route('tournament-manage.draw.index', $tournament) }}"
       class="td-tab-item {{ str_contains($currentRoute, 'draw') ? 'active' : '' }}">
        <span class="td-tab-icon">&#9878;</span>
        <span>Bốc thăm</span>
    </a>

    <a href="{{ route('tournament-manage.matches.index', $tournament) }}"
       class="td-tab-item {{ str_contains($currentRoute, 'matches') ? 'active' : '' }}">
        <span class="td-tab-icon">&#127934;</span>
        <span>Trận đấu</span>
    </a>

    <a href="{{ route('tournament-manage.rankings.index', $tournament) }}"
       class="td-tab-item {{ str_contains($currentRoute, 'rankings') ? 'active' : '' }}">
        <span class="td-tab-icon">&#127942;</span>
        <span>Xếp hạng</span>
    </a>
</nav>
