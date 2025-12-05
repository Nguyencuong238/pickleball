@props(['breakdown'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md p-6']) }}>
    <h3 class="text-lg font-semibold text-gray-800 mb-4">OPRS Breakdown</h3>

    <div class="space-y-4">
        {{-- Elo Component --}}
        <div>
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-medium text-gray-700">Elo Rating (70%)</span>
                <span class="text-sm text-gray-600">{{ number_format($breakdown['elo']['weighted'], 2) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-green-500 h-3 rounded-full" style="width: {{ min(100, ($breakdown['elo']['weighted'] / max(1, $breakdown['total'])) * 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Raw: {{ $breakdown['elo']['raw'] }} pts</p>
        </div>

        {{-- Challenge Component --}}
        <div>
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-medium text-gray-700">Challenge Score (20%)</span>
                <span class="text-sm text-gray-600">{{ number_format($breakdown['challenge']['weighted'], 2) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-500 h-3 rounded-full" style="width: {{ min(100, ($breakdown['challenge']['weighted'] / max(1, $breakdown['total'])) * 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Raw: {{ $breakdown['challenge']['raw'] }} pts</p>
        </div>

        {{-- Community Component --}}
        <div>
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-medium text-gray-700">Community Score (10%)</span>
                <span class="text-sm text-gray-600">{{ number_format($breakdown['community']['weighted'], 2) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-purple-500 h-3 rounded-full" style="width: {{ min(100, ($breakdown['community']['weighted'] / max(1, $breakdown['total'])) * 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Raw: {{ $breakdown['community']['raw'] }} pts</p>
        </div>
    </div>

    {{-- Total --}}
    <div class="mt-4 pt-4 border-t">
        <div class="flex justify-between items-center">
            <span class="font-semibold text-gray-800">Total OPRS</span>
            <span class="text-xl font-bold text-blue-600">{{ number_format($breakdown['total'], 0) }}</span>
        </div>
    </div>
</div>
