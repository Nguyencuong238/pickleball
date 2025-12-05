@extends('layouts.frontend')

@section('title', 'Challenge Center - OPRS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Challenge Center</h1>
        <a href="{{ route('ocr.profile', auth()->user()) }}" class="text-blue-600 hover:underline">
            [ARROW_LEFT] Back to Profile
        </a>
    </div>

    {{-- Current Stats --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">Your Challenge Score</h3>
                <p class="text-3xl font-bold text-blue-600">{{ number_format($user->challenge_score, 0) }}</p>
                <p class="text-sm text-gray-500">Contributes {{ number_format($user->challenge_score * 0.2, 2) }} to OPRS</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Total Challenges Passed</p>
                <p class="text-2xl font-semibold text-green-600">{{ $stats['passed'] }}</p>
                <p class="text-sm text-gray-500">out of {{ $stats['total'] }} attempts</p>
            </div>
        </div>
    </div>

    {{-- Available Challenges --}}
    <h2 class="text-xl font-semibold mb-4">Available Challenges</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @foreach($availableChallenges as $type => $challenge)
        <div class="bg-white rounded-lg shadow-md p-6 {{ !$challenge['available'] ? 'opacity-60' : '' }}">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">{{ $challenge['info']['icon'] ?? '[STAR]' }}</span>
                    <div>
                        <h3 class="font-semibold">{{ $challenge['info']['name'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $challenge['info']['description'] }}</p>
                    </div>
                </div>
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-medium whitespace-nowrap">
                    +{{ is_array($challenge['info']['points']) ? ($challenge['info']['points']['min'] . '-' . $challenge['info']['points']['max']) : $challenge['info']['points'] }} pts
                </span>
            </div>

            @if($challenge['available'])
                <a href="{{ route('ocr.challenges.submit', $type) }}"
                   class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    Start Challenge
                </a>
            @else
                <div class="text-center text-gray-500 py-2 bg-gray-100 rounded-lg">
                    {{ $challenge['reason'] ?? 'Not available' }}
                </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Recent Challenge History --}}
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold">Recent Challenges</h3>
        </div>
        <div class="divide-y">
            @forelse($history as $result)
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="{{ $result->passed ? 'text-green-600' : 'text-red-600' }} text-xl">
                        {{ $result->passed ? '[CHECK_CIRCLE]' : '[X_CIRCLE]' }}
                    </span>
                    <div>
                        <p class="font-medium">{{ \App\Models\ChallengeResult::getChallengeInfo($result->challenge_type)['name'] }}</p>
                        <p class="text-sm text-gray-500">Score: {{ $result->score }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($result->passed)
                        <span class="text-green-600 font-medium">+{{ number_format($result->points_earned, 0) }} pts</span>
                    @else
                        <span class="text-gray-400">Not passed</span>
                    @endif
                    <p class="text-xs text-gray-400">{{ $result->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                <p class="text-lg mb-2">[TARGET]</p>
                <p>No challenges yet. Start your first challenge!</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
