@props(['user', 'breakdown'])

<div class="oprs-score-card">
    <div class="oprs-header">
        <h3 class="oprs-title">OPRS Score</h3>
        <!-- <x-oprs.level-badge :level="$user->opr_level" /> -->
        <x-oprs.skill-level-badge :elo="$user->elo_rating" />
    </div>

    <div class="oprs-total">
        <span class="oprs-value">{{ number_format($user->total_oprs, 0) }}</span>
        <p class="oprs-label">OnePickleball Rating Score</p>
    </div>

    <div class="oprs-breakdown">
        {{-- Elo Component (70%) --}}
        <div class="oprs-component">
            <div class="component-info">
                <span class="component-icon">🏆</span>
                <span class="component-name">Elo (70%)</span>
            </div>
            <div class="component-values">
                <span class="component-weighted">{{ number_format($breakdown['elo']['weighted'], 0) }}</span>
                <span class="component-raw">({{ $breakdown['elo']['raw'] }})</span>
            </div>
        </div>

        {{-- Challenge Component (20%) --}}
        <div class="oprs-component">
            <div class="component-info">
                <span class="component-icon">🎯</span>
                <span class="component-name">Challenge (20%)</span>
            </div>
            <div class="component-values">
                <span class="component-weighted">{{ number_format($breakdown['challenge']['weighted'], 0) }}</span>
                <span class="component-raw">({{ $breakdown['challenge']['raw'] }})</span>
            </div>
        </div>

        {{-- Community Component (10%) --}}
        <div class="oprs-component">
            <div class="component-info">
                <span class="component-icon">👥</span>
                <span class="component-name">Community (10%)</span>
            </div>
            <div class="component-values">
                <span class="component-weighted">{{ number_format($breakdown['community']['weighted'], 0) }}</span>
                <span class="component-raw">({{ $breakdown['community']['raw'] }})</span>
            </div>
        </div>
    </div>

    {{-- Level Progress Bar --}}
    @php
        $levels = App\Models\User::getOprLevels();
        $currentLevel = $user->opr_level;
        $currentLevelInfo = $levels[$currentLevel] ?? null;
        $levelKeys = array_keys($levels);
        $currentIndex = array_search($currentLevel, $levelKeys);
        $nextLevel = isset($levelKeys[$currentIndex + 1]) ? $levelKeys[$currentIndex + 1] : null;
        $nextLevelInfo = $nextLevel ? $levels[$nextLevel] : null;

        $progressPercent = 0;
        $pointsToNext = 0;
        if ($currentLevelInfo && $nextLevelInfo) {
            $rangeSize = $currentLevelInfo['max'] - $currentLevelInfo['min'];
            $progress = $user->total_oprs - $currentLevelInfo['min'];
            $progressPercent = min(100, ($progress / $rangeSize) * 100);
            $pointsToNext = $nextLevelInfo['min'] - $user->total_oprs;
        }
    @endphp

    @if($nextLevelInfo)
    <div class="oprs-progress-section">
        <div class="progress-header">
            <span class="progress-next">Next: {{ $nextLevelInfo['name'] }} ({{ $nextLevel }})</span>
            <span class="progress-pts">{{ number_format($pointsToNext, 0) }} pts needed</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $progressPercent }}%"></div>
        </div>
    </div>
    @endif
</div>

<style>
.oprs-score-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
}

.oprs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.oprs-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.oprs-total {
    text-align: center;
    margin-bottom: 1.5rem;
}

.oprs-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #3b82f6;
}

.oprs-label {
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 0.25rem;
}

.oprs-breakdown {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.oprs-component {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.component-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.component-icon {
    color: #64748b;
}

.component-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.component-values {
    text-align: right;
}

.component-weighted {
    font-weight: 600;
    color: #1e293b;
}

.component-raw {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-left: 0.25rem;
}

.oprs-progress-section {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.progress-next,
.progress-pts {
    color: #64748b;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    border-radius: 4px;
    transition: width 0.3s;
}
</style>
