<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hàng đợi - {{ $activity->title }}</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/club-activity-queue.css') }}">
</head>
<body>
<div class="ca-queue-page"
     x-data="caQueue({
        statusUrl: '{{ route('club.activity.queue-status', [$club->slug, $activity->id]) }}',
        playerEndMatchUrlBase: '{{ url('clubs/' . $club->slug . '/activities/' . $activity->id . '/player-end-match') }}',
        scoreUrlBase: '{{ url('clubs/' . $club->slug . '/activities/' . $activity->id . '/matches') }}',
        courtsCount: {{ $activity->courts_count }},
        avgMatchDuration: {{ $activity->avg_match_duration ?? 15 }},
        activityTitle: '{{ addslashes($activity->title) }}',
     })">

    {{-- Fixed top bar --}}
    <div class="ca-queue-topbar">
        <div class="ca-queue-topbar-left">
            <h1 class="ca-queue-activity-name" x-text="config.activityTitle"></h1>
            <span class="ca-badge ca-badge-waiting">Đang chờ</span>
        </div>
        <div class="ca-stale-badge" x-show="isStale" x-transition>
            Mất kết nối
        </div>
    </div>

    {{-- Hero card: my status --}}
    <div class="ca-hero-card" x-show="myStatus">
        <template x-if="myStatus && myStatus.current_status === 'queued'">
            <div class="ca-hero-content">
                <p class="ca-hero-label">Vị trí của bạn</p>
                <p class="ca-queue-position" x-text="myStatus.queue_position || '-'"></p>
                <p class="ca-hero-sub" x-show="estimatedWait">
                    Thời gian chờ ước tính: <strong x-text="estimatedWait + ' phút'"></strong>
                </p>
            </div>
        </template>
        <template x-if="myStatus && myStatus.current_status === 'playing' && !myStatus.pending_score_match_id && !myStatus.rejected_match_id">
            <div class="ca-hero-content ca-hero-playing">
                <p class="ca-hero-label">Đang thi đấu</p>
                <p class="ca-hero-court">Sân đang chơi</p>
                <template x-if="myStatus.current_match_id">
                    <button class="ca-btn-end-match" @click="playerEndMatch()" :disabled="endingMatch">
                        <span x-show="!endingMatch">Kết thúc & Nhập điểm</span>
                        <span x-show="endingMatch">Đang xử lý...</span>
                    </button>
                </template>
            </div>
        </template>
        <template x-if="myStatus && myStatus.pending_score_match_id">
            <div class="ca-hero-content ca-hero-pending">
                <template x-if="myStatus.can_confirm_score">
                    <div>
                        <p class="ca-hero-label">Chờ xác nhận điểm</p>
                        <p class="ca-hero-sub">Đội bạn cần xác nhận điểm số</p>
                        <a class="ca-btn-confirm-link" :href="config.scoreUrlBase + '/' + myStatus.pending_score_match_id + '/score'">Xác nhận điểm</a>
                    </div>
                </template>
                <template x-if="!myStatus.can_confirm_score">
                    <div>
                        <p class="ca-hero-label">Chờ xác nhận điểm</p>
                        <p class="ca-hero-sub">Đang chờ đội còn lại xác nhận...</p>
                    </div>
                </template>
            </div>
        </template>
        <template x-if="myStatus && myStatus.rejected_match_id && !myStatus.pending_score_match_id">
            <div class="ca-hero-content ca-hero-rejected">
                <p class="ca-hero-label">Điểm bị từ chối</p>
                <p class="ca-hero-sub">Điểm bạn nhập đã bị từ chối. Vui lòng nhập lại.</p>
                <a class="ca-btn-score-link" :href="config.scoreUrlBase + '/' + myStatus.rejected_match_id + '/score'">Nhập lại điểm</a>
            </div>
        </template>
    </div>

    {{-- Court strip --}}
    <div class="ca-section-title">Sân thi đấu</div>
    <div class="ca-court-grid">
        <template x-for="court in courts" :key="court.court">
            <div class="ca-court-card" :class="{ 'is-playing': court.status === 'playing' }">
                <div class="ca-court-number">Sân <span x-text="court.court"></span></div>
                <template x-if="court.status === 'playing' && court.match">
                    <div class="ca-court-match">
                        <div class="ca-court-team">
                            <span x-text="court.match.player1 ? court.match.player1.name : ''"></span>
                            <span x-show="court.match.player2"> & </span>
                            <span x-text="court.match.player2 ? court.match.player2.name : ''"></span>
                        </div>
                        <div class="ca-court-vs">vs</div>
                        <div class="ca-court-team">
                            <span x-text="court.match.player3 ? court.match.player3.name : ''"></span>
                            <span x-show="court.match.player4"> & </span>
                            <span x-text="court.match.player4 ? court.match.player4.name : ''"></span>
                        </div>
                    </div>
                </template>
                <template x-if="court.status === 'available'">
                    <div class="ca-court-empty">Trống</div>
                </template>
            </div>
        </template>
    </div>

    {{-- Queue list --}}
    <div class="ca-section-title">Hàng đợi (<span x-text="queue.filter(q => q.status === 'queued').length"></span>)</div>
    <div class="ca-queue-list">
        <template x-for="(player, idx) in queue.filter(q => q.status === 'queued')" :key="player.id">
            <div class="ca-queue-row" :class="{ 'is-me': myStatus && player.user_id == myStatus.user_id }">
                <div class="ca-queue-rank" x-text="idx + 1"></div>
                <div class="ca-queue-player-info">
                    <span class="ca-queue-name" x-text="player.name"></span>
                    <span class="ca-queue-oprs" x-text="player.oprs ? 'OPRS ' + player.oprs : ''"></span>
                </div>
                <div class="ca-status-dot" :class="'ca-status-' + player.status"></div>
            </div>
        </template>
        <div class="ca-queue-empty" x-show="queue.filter(q => q.status === 'queued').length === 0">
            Chưa có ai trong hàng đợi
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/club-activity-queue.js') }}"></script>
</body>
</html>
