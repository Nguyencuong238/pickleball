@extends('layouts.frontend')

@section('title', 'Check-in - Community Hub')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <a href="{{ route('ocr.community.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
        [ARROW_LEFT] Back to Community Hub
    </a>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center space-x-3 mb-6">
            <span class="text-3xl">[LOCATION]</span>
            <div>
                <h1 class="text-2xl font-bold">Check-in</h1>
                <p class="text-gray-600">Check-in tai san de nhan +2 Community Points</p>
            </div>
        </div>

        {{-- Check-in Form --}}
        <form action="{{ route('ocr.community.checkin.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label for="stadium_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Stadium
                </label>
                <select id="stadium_id"
                        name="stadium_id"
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('stadium_id') border-red-500 @enderror">
                    <option value="">Choose a stadium...</option>
                    @foreach($stadiums as $stadium)
                        <option value="{{ $stadium->id }}"
                                {{ !$canCheckIn[$stadium->id] ? 'disabled' : '' }}>
                            {{ $stadium->name }}
                            @if(!$canCheckIn[$stadium->id])
                                (Already checked in today)
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('stadium_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-purple-600 text-white py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
                Check-in
            </button>
        </form>
    </div>

    {{-- Today's Check-ins --}}
    @if($todayCheckIns->count() > 0)
    <div class="bg-white rounded-lg shadow-md mt-6">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold">Today's Check-ins</h3>
        </div>
        <div class="divide-y">
            @foreach($todayCheckIns as $checkIn)
            <div class="px-6 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-green-600">[CHECK]</span>
                    <span>{{ $checkIn->metadata['stadium_name'] ?? 'Stadium' }}</span>
                </div>
                <span class="text-sm text-gray-500">{{ $checkIn->created_at->format('H:i') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
