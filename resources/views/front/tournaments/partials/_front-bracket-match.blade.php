{{-- Front-end bracket match card (read-only) --}}
<div class="front-bracket-match {{ $match->status === 'completed' ? 'front-bracket-match--done' : '' }} {{ $match->status === 'in_progress' ? 'front-bracket-match--live' : '' }}">
    @if($match->status === 'in_progress')
        <span class="front-bracket-match-live">LIVE</span>
    @endif
    @php $played = in_array($match->status, ['completed', 'in_progress']); @endphp
    <div class="front-bracket-slot {{ $match->winner_id && $match->winner_id == $match->athlete1_id ? 'front-bracket-slot--winner' : '' }}">
        <span class="front-bracket-slot-name">{{ $match->athlete1_name ?: 'Chưa xác định' }}</span>
        <span class="front-bracket-slot-score">{{ $played ? ($match->athlete1_score ?? 0) : '-' }}</span>
    </div>
    <div class="front-bracket-slot {{ $match->winner_id && $match->winner_id == $match->athlete2_id ? 'front-bracket-slot--winner' : '' }}">
        <span class="front-bracket-slot-name">{{ $match->athlete2_name ?: 'Chưa xác định' }}</span>
        <span class="front-bracket-slot-score">{{ $played ? ($match->athlete2_score ?? 0) : '-' }}</span>
    </div>
</div>
