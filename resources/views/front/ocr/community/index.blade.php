@extends('layouts.frontend')

@section('title', 'Community Hub - OPRS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Community Hub</h1>
        <a href="{{ route('ocr.profile', auth()->user()) }}" class="text-blue-600 hover:underline">
            [ARROW_LEFT] Back to Profile
        </a>
    </div>

    {{-- Current Stats --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">Your Community Score</h3>
                <p class="text-3xl font-bold text-purple-600">{{ number_format($user->community_score, 0) }}</p>
                <p class="text-sm text-gray-500">Contributes {{ number_format($user->community_score * 0.1, 2) }} to OPRS</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Activities This Month</p>
                <p class="text-2xl font-semibold text-purple-600">{{ $stats['recent_count'] }}</p>
            </div>
        </div>
    </div>

    {{-- Activity Options --}}
    <h2 class="text-xl font-semibold mb-4">Earn Community Points</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {{-- Check-in --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center space-x-3 mb-4">
                <span class="text-2xl">[LOCATION]</span>
                <div>
                    <h3 class="font-semibold">Check-in</h3>
                    <p class="text-sm text-gray-500">+2 pts per check-in</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Check-in tai san OnePickleball hoac doi tac</p>
            <a href="{{ route('ocr.community.checkin') }}"
               class="block w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition">
                Check-in Now
            </a>
        </div>

        {{-- Referral --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center space-x-3 mb-4">
                <span class="text-2xl">[USER_PLUS]</span>
                <div>
                    <h3 class="font-semibold">Refer a Friend</h3>
                    <p class="text-sm text-gray-500">+10 pts per referral</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Gioi thieu ban be dang ky OnePickleball</p>
            <button onclick="copyReferralLink()"
                    class="w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition">
                Copy Referral Link
            </button>
        </div>

        {{-- Weekly 5 Matches --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center space-x-3 mb-4">
                <span class="text-2xl">[CALENDAR]</span>
                <div>
                    <h3 class="font-semibold">Weekly 5 Matches</h3>
                    <p class="text-sm text-gray-500">+5 pts (auto-awarded)</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-2">Hoan thanh 5 tran trong tuan</p>
            <div class="text-center">
                <p class="text-lg font-semibold text-purple-600">{{ $weeklyMatchCount }}/5</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-purple-600 h-2 rounded-full transition-all" style="width: {{ min(100, ($weeklyMatchCount/5)*100) }}%"></div>
                </div>
                @if($weeklyMatchCount >= 5)
                    <span class="text-green-600 text-sm">[CHECK] Completed this week!</span>
                @endif
            </div>
        </div>

        {{-- Events --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center space-x-3 mb-4">
                <span class="text-2xl">[EVENT]</span>
                <div>
                    <h3 class="font-semibold">Join Events</h3>
                    <p class="text-sm text-gray-500">+5 pts per event</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Tham gia workshop, clinic, su kien cong dong</p>
            <a href="{{ route('socials.index') }}"
               class="block w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition">
                View Events
            </a>
        </div>

        {{-- Monthly Challenge --}}
        <div class="bg-white rounded-lg shadow-md p-6 {{ !$availableActivities['monthly_challenge']['available'] ? 'opacity-60' : '' }}">
            <div class="flex items-center space-x-3 mb-4">
                <span class="text-2xl">[TROPHY]</span>
                <div>
                    <h3 class="font-semibold">Monthly Challenge</h3>
                    <p class="text-sm text-gray-500">+15 pts</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Hoan thanh nhiem vu dac biet hang thang</p>
            @if($availableActivities['monthly_challenge']['available'])
                <a href="{{ route('ocr.community.monthly') }}"
                   class="block w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition">
                    View Challenge
                </a>
            @else
                <div class="text-center text-gray-500 py-2 bg-gray-100 rounded-lg">
                    {{ $availableActivities['monthly_challenge']['reason'] ?? 'Completed this month' }}
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold">Recent Activities</h3>
        </div>
        <div class="divide-y">
            @forelse($history as $activity)
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-xl text-purple-600">{{ \App\Models\CommunityActivity::getActivityInfo($activity->activity_type)['icon'] ?? '[STAR]' }}</span>
                    <div>
                        <p class="font-medium">{{ \App\Models\CommunityActivity::getActivityInfo($activity->activity_type)['name'] }}</p>
                        @if($activity->metadata)
                            <p class="text-sm text-gray-500">
                                {{ $activity->metadata['stadium_name'] ?? $activity->metadata['event_name'] ?? $activity->metadata['referred_user_name'] ?? '' }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-purple-600 font-medium">+{{ number_format($activity->points_earned, 0) }} pts</span>
                    <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                <p class="text-lg mb-2">[USERS]</p>
                <p>No activities yet. Start earning community points!</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyReferralLink() {
    const link = '{{ url('/register?ref=' . auth()->user()->id) }}';
    navigator.clipboard.writeText(link).then(() => {
        alert('Referral link copied to clipboard!');
    });
}
</script>
@endpush
@endsection
