@extends('layouts.front')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/club-activity-leaderboard.css') }}">
@endsection

@section('content')
<div class="ca-leaderboard-page" x-data="caLeaderboard({ period: '{{ $period }}' })">
    <div class="ca-lb-header">
        <h1 class="ca-lb-title">Bảng xếp hạng - {{ $club->name }}</h1>
        <div class="ca-lb-filters">
            <a href="{{ route('club.leaderboard', $club->slug) }}?period=month"
               class="ca-lb-tab {{ $period === 'month' ? 'is-active' : '' }}">Tháng này</a>
            <a href="{{ route('club.leaderboard', $club->slug) }}?period=all"
               class="ca-lb-tab {{ $period === 'all' ? 'is-active' : '' }}">Tất cả</a>
        </div>
    </div>

    {{-- Podium (desktop only, top 3) --}}
    @if($leaderboard->count() >= 3)
    <div class="ca-lb-podium">
        @foreach($leaderboard->take(3) as $idx => $stat)
        <div class="ca-lb-podium-item ca-lb-rank-{{ $idx + 1 }}">
            <div class="ca-lb-podium-rank">{{ $idx + 1 }}</div>
            <div class="ca-lb-podium-avatar">
                @if($stat->user && $stat->user->avatar)
                    <img src="{{ $stat->user->avatar }}" alt="{{ $stat->user->name }}">
                @else
                    <span class="ca-lb-avatar-placeholder">{{ $stat->user ? strtoupper(substr($stat->user->name, 0, 1)) : '?' }}</span>
                @endif
            </div>
            <div class="ca-lb-podium-name">{{ $stat->user->name ?? '-' }}</div>
            <div class="ca-lb-podium-oprs">{{ $stat->current_oprs ?? '-' }} <span class="ca-lb-podium-oprs-label">OPRS</span></div>
            <div class="ca-lb-podium-detail">{{ $stat->total_wins }} thắng / {{ $stat->total_losses }} thua</div>
            <div class="ca-lb-podium-winrate">Tỷ lệ thắng: {{ $stat->win_rate }}%</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Ranked list --}}
    <div class="ca-lb-list">
        @foreach($leaderboard as $idx => $stat)
        <div class="ca-lb-row @if(auth()->check() && auth()->id() === $stat->user_id) ca-lb-row-me @endif">
            <div class="ca-lb-row-rank">{{ $idx + 1 }}</div>
            <div class="ca-lb-row-player">
                <span class="ca-lb-row-name">{{ $stat->user->name ?? '-' }}</span>
                @if($stat->user->opr_level)
                    <span class="ca-lb-row-level">Trình độ: {{ $stat->user->opr_level }}</span>
                @endif
            </div>
            <div class="ca-lb-row-stats">
                <span class="ca-lb-oprs-chip" title="Điểm OPRS">{{ $stat->current_oprs ?? '-' }} OPRS</span>
                <span class="ca-lb-row-record">{{ $stat->total_wins }} thắng - {{ $stat->total_losses }} thua ({{ $stat->total_matches }} trận)</span>
                <div class="ca-lb-winbar" title="Tỷ lệ thắng: {{ $stat->win_rate }}%">
                    <div class="ca-lb-winbar-fill" style="width: {{ $stat->win_rate }}%"></div>
                </div>
            </div>
        </div>
        @endforeach

        @if($leaderboard->isEmpty())
        <div class="ca-lb-empty">Chưa có dữ liệu xếp hạng</div>
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('assets/js/club-activity-leaderboard.js') }}"></script>
@endsection
