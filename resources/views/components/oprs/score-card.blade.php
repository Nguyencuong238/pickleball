@props(['user', 'breakdown'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">OPRS Score</h3>
        <x-oprs.level-badge :level="$user->opr_level" />
    </div>

    <div class="text-center mb-6">
        <span class="text-4xl font-bold text-blue-600">{{ number_format($user->total_oprs, 0) }}</span>
        <p class="text-sm text-gray-500 mt-1">OnePickleball Rating Score</p>
    </div>

    {{-- Component Breakdown --}}
    <div class="space-y-4">
        {{-- Elo Component (70%) --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600">[TROPHY]</span>
                <span class="text-sm font-medium">Elo (70%)</span>
            </div>
            <div class="text-right">
                <span class="font-semibold">{{ number_format($breakdown['elo']['weighted'], 0) }}</span>
                <span class="text-xs text-gray-500 ml-1">({{ $breakdown['elo']['raw'] }})</span>
            </div>
        </div>

        {{-- Challenge Component (20%) --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600">[TARGET]</span>
                <span class="text-sm font-medium">Challenge (20%)</span>
            </div>
            <div class="text-right">
                <span class="font-semibold">{{ number_format($breakdown['challenge']['weighted'], 0) }}</span>
                <span class="text-xs text-gray-500 ml-1">({{ $breakdown['challenge']['raw'] }})</span>
            </div>
        </div>

        {{-- Community Component (10%) --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600">[USERS]</span>
                <span class="text-sm font-medium">Community (10%)</span>
            </div>
            <div class="text-right">
                <span class="font-semibold">{{ number_format($breakdown['community']['weighted'], 0) }}</span>
                <span class="text-xs text-gray-500 ml-1">({{ $breakdown['community']['raw'] }})</span>
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
    <div class="mt-6 pt-4 border-t">
        <div class="flex justify-between text-sm mb-2">
            <span class="text-gray-600">Next: {{ $nextLevelInfo['name'] }} ({{ $nextLevel }})</span>
            <span class="text-gray-600">{{ number_format($pointsToNext, 0) }} pts needed</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
        </div>
    </div>
    @endif
</div>
