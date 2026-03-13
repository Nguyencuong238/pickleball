{{-- Tournament Dashboard Sidebar --}}
@php
    $athleteCount    = $tournament->athletes()->count();
    $approvedCount   = $tournament->athletes()->where('status', 'approved')->count();
    $matchTotal      = $tournament->matches()->count();
    $matchDone       = $tournament->matches()->whereNotNull('winner_id')->count();
    $drawDone        = $tournament->tournament_stage !== 'registration';
    $categoriesCount = $tournament->categories()->count();

    $steps = [
        (bool) $tournament->id,
        $categoriesCount > 0,
        $athleteCount > 0,
        $drawDone,
        $matchTotal > 0,
        $matchDone === $matchTotal && $matchTotal > 0,
    ];
    $progress = count(array_filter($steps));
    $progressPct = (int) round($progress / count($steps) * 100);

    $currentRoute = request()->route()->getName();
@endphp

<aside class="td-sidebar">
    <div class="td-sidebar-header">
        <div class="td-sidebar-title">Giải đấu</div>
        <div class="td-sidebar-name">{{ $tournament->name }}</div>
    </div>

    <div class="td-progress-bar">
        <div class="td-progress-fill" style="width: {{ $progressPct }}%"></div>
    </div>
    <div class="td-progress-label">Hoàn thành {{ $progressPct }}%</div>

    <nav class="td-nav">
        <a href="{{ route('tournament-manage.tournaments.show', $tournament) }}"
           class="td-nav-item {{ $currentRoute === 'tournament-manage.tournaments.show' ? 'active' : '' }}">
            <span class="td-nav-icon">&#9776;</span>
            <span class="td-nav-label">Tổng quan</span>
            @if($progressPct === 100)
                <span class="td-nav-badge done">Xong</span>
            @endif
        </a>

        <a href="{{ route('tournament-manage.athletes.index', $tournament) }}"
           class="td-nav-item {{ str_contains($currentRoute, 'athletes') ? 'active' : '' }}">
            <span class="td-nav-icon">&#128100;</span>
            <span class="td-nav-label">Vận động viên</span>
            <span class="td-nav-badge {{ $approvedCount > 0 ? 'done' : '' }}">
                {{ $approvedCount }}/{{ $athleteCount }}
            </span>
        </a>

        <a href="{{ route('tournament-manage.draw.index', $tournament) }}"
           class="td-nav-item {{ str_contains($currentRoute, 'draw') ? 'active' : '' }}">
            <span class="td-nav-icon">&#9878;</span>
            <span class="td-nav-label">Bốc thăm</span>
            <span class="td-nav-badge {{ $drawDone ? 'done' : 'pending' }}">
                {{ $drawDone ? 'Xong' : 'Chờ' }}
            </span>
        </a>

        <a href="{{ route('tournament-manage.matches.index', $tournament) }}"
           class="td-nav-item {{ str_contains($currentRoute, 'matches') ? 'active' : '' }}">
            <span class="td-nav-icon">&#127934;</span>
            <span class="td-nav-label">Trận đấu</span>
            @if($matchTotal > 0)
                <span class="td-nav-badge {{ $matchDone === $matchTotal ? 'done' : '' }}">
                    {{ $matchDone }}/{{ $matchTotal }}
                </span>
            @endif
        </a>

        <a href="{{ route('tournament-manage.rankings.index', $tournament) }}"
           class="td-nav-item {{ str_contains($currentRoute, 'rankings') ? 'active' : '' }}">
            <span class="td-nav-icon">&#127942;</span>
            <span class="td-nav-label">Xếp hạng</span>
        </a>
    </nav>

    <div style="padding: 16px; border-top: 1px solid #f1f5f9; margin-top: auto;">
        <a href="{{ route('tournament-manage.tournaments.edit', $tournament) }}"
           class="td-btn td-btn-outline" style="width: 100%; justify-content: center;">
            Chỉnh sửa giải đấu
        </a>
    </div>
</aside>
