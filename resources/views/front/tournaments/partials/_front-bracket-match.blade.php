{{-- Front-end bracket match card (read-only) --}}
@php
    $a1Name = $match->athlete1_name ?: ($match->athlete1?->pair_name ?? null);
    $a2Name = $match->athlete2_name ?: ($match->athlete2?->pair_name ?? null);
    $played = in_array($match->status, ['completed', 'in_progress']);
    $setScores = $match->set_scores ?? [];
    $hasSetScores = !empty($setScores);
@endphp
<div class="front-bracket-match {{ $match->status === 'completed' ? 'front-bracket-match--done' : '' }} {{ $match->status === 'in_progress' ? 'front-bracket-match--live' : '' }}">
    @if($match->status === 'in_progress')
        <span class="front-bracket-match-live">LIVE</span>
    @endif
    <div class="front-bracket-slot {{ $match->winner_id && $match->winner_id == $match->athlete1_id ? 'front-bracket-slot--winner' : '' }}">
        <span class="front-bracket-slot-name" title="{{ $a1Name ?: 'Chưa xác định' }}">{{ $a1Name ?: 'Chưa xác định' }}</span>
        @if($hasSetScores)
            <span class="front-bracket-slot-sets">
                @foreach($setScores as $set)
                    <span class="front-bracket-set-score">{{ $set['athlete1_score'] ?? 0 }}</span>
                @endforeach
            </span>
        @endif
        <span class="front-bracket-slot-total">{{ $played ? ($match->athlete1_score ?? 0) : '-' }}</span>
    </div>
    <div class="front-bracket-slot {{ $match->winner_id && $match->winner_id == $match->athlete2_id ? 'front-bracket-slot--winner' : '' }}">
        <span class="front-bracket-slot-name" title="{{ $a2Name ?: 'Chưa xác định' }}">{{ $a2Name ?: 'Chưa xác định' }}</span>
        @if($hasSetScores)
            <span class="front-bracket-slot-sets">
                @foreach($setScores as $set)
                    <span class="front-bracket-set-score">{{ $set['athlete2_score'] ?? 0 }}</span>
                @endforeach
            </span>
        @endif
        <span class="front-bracket-slot-total">{{ $played ? ($match->athlete2_score ?? 0) : '-' }}</span>
    </div>
</div>
