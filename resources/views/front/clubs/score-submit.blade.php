<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nhập điểm - Sân {{ $match->scheduled_court }}</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/club-activity-score.css') }}">
</head>
<body>
<div class="ca-score-page"
     x-data="caScoreSubmit({
        submitUrl: '{{ route('club.activity.submit-score', [$club->slug, $activity->id, $match->id]) }}',
        queueUrl: '{{ route('club.activity.queue', [$club->slug, $activity->id]) }}',
        bestOf: {{ $activity->best_of ?? 1 }},
        pointsPerSet: {{ $activity->points_per_set ?? 21 }},
     })">

    {{-- Court hero --}}
    <div class="ca-score-header">
        <p class="ca-score-court">Sân {{ $match->scheduled_court ?? $match->match_number }}</p>
        <p class="ca-score-match-info">Trận #{{ $match->match_number }}</p>
    </div>

    {{-- Team cards --}}
    <div class="ca-teams">
        <div class="ca-team-card" :class="{ 'is-winner': winner === 'team1' }">
            <div class="ca-team-label">Đội A</div>
            <div class="ca-team-players">
                <span>{{ $match->player1->name ?? '' }}</span>
                @if($match->player2)
                    <span> & {{ $match->player2->name }}</span>
                @endif
            </div>
        </div>
        <div class="ca-vs-badge">VS</div>
        <div class="ca-team-card" :class="{ 'is-winner': winner === 'team2' }">
            <div class="ca-team-label">Đội B</div>
            <div class="ca-team-players">
                <span>{{ $match->player3->name ?? '' }}</span>
                @if($match->player4)
                    <span> & {{ $match->player4->name }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Set scores --}}
    <div class="ca-sets">
        <template x-for="(set, idx) in sets" :key="idx">
            <div class="ca-set-row" x-show="bestOf === 1 ? idx === 0 : (idx < 2 || showSet3)">
                <div class="ca-set-label" x-text="bestOf === 1 ? '' : 'Set ' + (idx + 1)"></div>
                <div class="ca-set-scores">
                    <div class="ca-stepper">
                        <button type="button" class="ca-stepper-btn" @click="decrement(idx, 'team1')" :disabled="set.team1 <= 0">-</button>
                        <span class="ca-score-display" x-text="set.team1"></span>
                        <button type="button" class="ca-stepper-btn" @click="increment(idx, 'team1')" :disabled="set.team1 >= pointsPerSet">+</button>
                    </div>
                    <div class="ca-set-divider">:</div>
                    <div class="ca-stepper">
                        <button type="button" class="ca-stepper-btn" @click="decrement(idx, 'team2')" :disabled="set.team2 <= 0">-</button>
                        <span class="ca-score-display" x-text="set.team2"></span>
                        <button type="button" class="ca-stepper-btn" @click="increment(idx, 'team2')" :disabled="set.team2 >= pointsPerSet">+</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Winner preview --}}
    <div class="ca-winner-preview" x-show="winner" x-transition>
        <p class="ca-winner-text">
            Đội thắng: <strong x-text="winner === 'team1' ? 'Đội A' : 'Đội B'"></strong>
        </p>
    </div>

    {{-- Error --}}
    <p class="ca-error-msg" x-show="error" x-text="error"></p>

    {{-- CTA --}}
    <div class="ca-cta-fixed">
        <button class="ca-btn-primary ca-btn-confirm" @click="submit()" :disabled="loading || !isValid">
            <span x-show="!loading">Xác nhận điểm</span>
            <span x-show="loading">Đang gửi...</span>
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/club-activity-score.js') }}"></script>
</body>
</html>
