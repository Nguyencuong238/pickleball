{{-- Front-end bracket match card (read-only) --}}
@php
    $a1Name = $match->athlete1_name ?: ($match->athlete1?->pair_name ?? null);
    $a2Name = $match->athlete2_name ?: ($match->athlete2?->pair_name ?? null);
    $played = in_array($match->status, ['completed', 'in_progress']);
    $setScores = $match->set_scores ?? [];
    $hasSetScores = !empty($setScores);
    $isBye = (empty($a1Name) xor empty($a2Name));
    $timeStr = $match->match_time ? \Carbon\Carbon::parse($match->match_time)->format('H:i') : null;
    $dateStr = $match->match_date ? \Carbon\Carbon::parse($match->match_date)->format('d/m') : null;
    $courtNum = $match->court?->number ?? null;
    $isWinner1 = $match->winner_id && $match->winner_id == $match->athlete1_id;
    $isWinner2 = $match->winner_id && $match->winner_id == $match->athlete2_id;
    $metaParts = [];
    if ($dateStr) { $metaParts[] = $dateStr; }
    if ($timeStr) { $metaParts[] = $timeStr; }
    if ($courtNum) { $metaParts[] = 'Sân ' . $courtNum; }
    $matchMeta = implode(' • ', $metaParts);
    // Only show banner when we have time or court (date alone is noise when whole tournament is 1 day)
    $showBanner = !$isBye && ($timeStr || $courtNum);
@endphp
<div class="front-bracket-match {{ $match->status === 'completed' ? 'front-bracket-match--done' : '' }} {{ $match->status === 'in_progress' ? 'front-bracket-match--live' : '' }} {{ $isBye ? 'front-bracket-match--bye' : '' }}">
    @if ($showBanner)
        <div class="front-bracket-match-banner">
            <svg class="front-bracket-banner-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5v5l4 2-.75 1.5L11 13V7h2z" />
            </svg>
            <span class="front-bracket-banner-meta">{{ $matchMeta }}</span>
        </div>
    @endif
    @if ($match->status === 'in_progress')
        <span class="front-bracket-match-live">LIVE</span>
    @endif
    <div class="front-bracket-slot {{ $isWinner1 ? 'front-bracket-slot--winner' : ($match->winner_id ? 'front-bracket-slot--loser' : '') }}">
        <span class="front-bracket-slot-name" title="{{ $a1Name ?: 'Chưa xác định' }}">{{ $a1Name ?: 'Chưa xác định' }}</span>
        @if ($hasSetScores)
            <span class="front-bracket-slot-sets">
                @foreach ($setScores as $set)
                    @php $sv = $set['athlete1_score'] ?? $set['athlete1'] ?? $set['s1'] ?? 0; @endphp
                    <span class="front-bracket-set-score">{{ $sv }}</span>
                @endforeach
            </span>
        @endif
        <span class="front-bracket-slot-total">{{ $played ? ($match->athlete1_score ?? 0) : '-' }}</span>
    </div>
    <div class="front-bracket-slot {{ $isWinner2 ? 'front-bracket-slot--winner' : ($match->winner_id ? 'front-bracket-slot--loser' : '') }} {{ $isBye && empty($a2Name) ? 'front-bracket-slot--bye' : '' }}">
        @if ($isBye && empty($a2Name))
            <span class="front-bracket-slot-name front-bracket-slot-bye-label">BYE</span>
        @else
            <span class="front-bracket-slot-name" title="{{ $a2Name ?: 'Chưa xác định' }}">{{ $a2Name ?: 'Chưa xác định' }}</span>
            @if ($hasSetScores)
                <span class="front-bracket-slot-sets">
                    @foreach ($setScores as $set)
                        @php $sv = $set['athlete2_score'] ?? $set['athlete2'] ?? $set['s2'] ?? 0; @endphp
                        <span class="front-bracket-set-score">{{ $sv }}</span>
                    @endforeach
                </span>
            @endif
            <span class="front-bracket-slot-total">{{ $played ? ($match->athlete2_score ?? 0) : '-' }}</span>
        @endif
    </div>
</div>
