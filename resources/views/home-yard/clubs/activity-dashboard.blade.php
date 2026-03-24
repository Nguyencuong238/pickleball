@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/club-activity-dashboard.css') }}">
@endsection

@section('content')
<div class="ca-dashboard" x-data="caAdminDashboard({
    stateUrl: '{{ route('club.activity.dashboard-state', [$club->slug, $activity->id]) }}',
    triggerUrl: '{{ route('club.activity.trigger-match', [$club->slug, $activity->id]) }}',
    courtsCount: {{ $activity->courts_count }},
})">
    {{-- Left sidebar --}}
    <div class="ca-dash-sidebar">
        <div class="ca-dash-card">
            <h2 class="ca-dash-card-title">{{ $activity->title }}</h2>
            <p class="ca-dash-club-name">{{ $club->name }}</p>
            <div class="ca-dash-stats-grid">
                <div class="ca-dash-stat">
                    <span class="ca-dash-stat-value" x-text="stats.total_checked_in || 0"></span>
                    <span class="ca-dash-stat-label">Check-in</span>
                </div>
                <div class="ca-dash-stat">
                    <span class="ca-dash-stat-value" x-text="stats.queued_count || 0"></span>
                    <span class="ca-dash-stat-label">Đang chờ</span>
                </div>
                <div class="ca-dash-stat">
                    <span class="ca-dash-stat-value" x-text="stats.playing_count || 0"></span>
                    <span class="ca-dash-stat-label">Đang chơi</span>
                </div>
                <div class="ca-dash-stat">
                    <span class="ca-dash-stat-value" x-text="stats.total_matches || 0"></span>
                    <span class="ca-dash-stat-label">Tổng trận</span>
                </div>
            </div>
        </div>

        <div class="ca-dash-card">
            <h3 class="ca-dash-card-title">Cấu hình</h3>
            <p class="ca-dash-config-row">Số sân: <strong>{{ $activity->courts_count }}</strong></p>
            <p class="ca-dash-config-row">Chế độ: <strong>{{ $activity->rotation_mode }}</strong></p>
            <p class="ca-dash-config-row">OPRS weight: <strong>{{ $activity->oprs_weight }}</strong></p>
        </div>

        @if($activity->qr_code)
        <div class="ca-dash-card">
            <h3 class="ca-dash-card-title">QR Check-in</h3>
            <p class="ca-dash-qr-link">
                <a href="{{ route('club.activity.checkin', [$club->slug, $activity->id]) }}?token={{ $activity->qr_code }}" target="_blank">
                    Mở trang check-in
                </a>
            </p>
        </div>
        @endif
    </div>

    {{-- Main: Court cards --}}
    <div class="ca-dash-main">
        <h3 class="ca-dash-section-title">Sân thi đấu</h3>
        <div class="ca-dash-court-grid">
            <template x-for="court in courts" :key="court.court">
                <div class="ca-court-card-admin" :class="{ 'is-playing': court.status === 'playing' }">
                    <div class="ca-court-card-header">
                        <span class="ca-court-label" x-text="'Sân ' + court.court"></span>
                        <span class="ca-court-status-badge" :class="court.status" x-text="court.status === 'playing' ? 'Đang chơi' : 'Trong'"></span>
                    </div>
                    <template x-if="court.match">
                        <div class="ca-court-card-body">
                            <div class="ca-court-team-row">
                                <span x-text="(court.match.player1 ? court.match.player1.name : '') + (court.match.player2 ? ' & ' + court.match.player2.name : '')"></span>
                            </div>
                            <div class="ca-court-vs-label">vs</div>
                            <div class="ca-court-team-row">
                                <span x-text="(court.match.player3 ? court.match.player3.name : '') + (court.match.player4 ? ' & ' + court.match.player4.name : '')"></span>
                            </div>
                            <div class="ca-court-actions">
                                <button class="ca-btn-sm ca-btn-danger" @click="endMatch(court.match.id)">Kết thúc</button>
                            </div>
                        </div>
                    </template>
                    <template x-if="!court.match">
                        <div class="ca-court-card-body ca-court-empty-state">Chưa có trận đấu</div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Right panel: Queue + trigger --}}
    <div class="ca-dash-panel">
        <div class="ca-dash-card">
            <div class="ca-dash-panel-header">
                <h3 class="ca-dash-card-title">Hàng đợi (<span x-text="queueList.length"></span>)</h3>
                <button class="ca-btn-sm ca-btn-primary" @click="triggerMatchmaking()" :disabled="triggerLoading">
                    <span x-show="!triggerLoading">Ghép trận</span>
                    <span x-show="triggerLoading">Đang ghép...</span>
                </button>
            </div>
            <div class="ca-dash-queue-list">
                <template x-for="(p, idx) in queueList" :key="p.id">
                    <div class="ca-dash-queue-row">
                        <span class="ca-dash-queue-rank" x-text="idx + 1"></span>
                        <span class="ca-dash-queue-name" x-text="p.user ? p.user.name : ''"></span>
                        <span class="ca-dash-queue-oprs" x-text="p.user && p.user.total_oprs ? p.user.total_oprs : '-'"></span>
                    </div>
                </template>
                <div class="ca-dash-empty" x-show="queueList.length === 0">Hàng đợi trống</div>
            </div>
        </div>

        <p class="ca-dash-trigger-msg" x-show="triggerMsg" x-text="triggerMsg" :class="triggerSuccess ? 'ca-msg-success' : 'ca-msg-error'"></p>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('assets/js/club-activity-dashboard.js') }}"></script>
@endsection
