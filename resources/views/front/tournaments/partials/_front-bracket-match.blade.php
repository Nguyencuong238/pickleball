{{-- Front-end bracket match card (read-only) --}}
@php
    // Fallback: if athlete1_name is empty, use relationship (pair_name for doubles, athlete_name for singles)
    $a1Name = $match->athlete1_name ?: ($match->athlete1?->pair_name ?? null);
    $a2Name = $match->athlete2_name ?: ($match->athlete2?->pair_name ?? null);
    $played = in_array($match->status, ['completed', 'in_progress']);
@endphp
<div class="front-bracket-match {{ $match->status === 'completed' ? 'front-bracket-match--done' : '' }} {{ $match->status === 'in_progress' ? 'front-bracket-match--live' : '' }}">
    @if($match->status === 'in_progress')
        <span class="front-bracket-match-live">LIVE</span>
    @endif
    <div class="front-bracket-slot {{ $match->winner_id && $match->winner_id == $match->athlete1_id ? 'front-bracket-slot--winner' : '' }}">
        <span class="front-bracket-slot-name">{{ $a1Name ?: 'Chưa xác định' }}</span>
        <span class="front-bracket-slot-score">{{ $played ? ($match->athlete1_score ?? 0) : '-' }}</span>
    </div>
    <div class="front-bracket-slot {{ $match->winner_id && $match->winner_id == $match->athlete2_id ? 'front-bracket-slot--winner' : '' }}">
        <span class="front-bracket-slot-name">{{ $a2Name ?: 'Chưa xác định' }}</span>
        <span class="front-bracket-slot-score">{{ $played ? ($match->athlete2_score ?? 0) : '-' }}</span>
    </div>
</div>
