@extends('layouts.frontend')

@section('title', 'Submit Challenge - ' . $challengeInfo['name'])

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <a href="{{ route('ocr.challenges.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
        [ARROW_LEFT] Back to Challenges
    </a>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center space-x-3 mb-6">
            <span class="text-3xl">{{ $challengeInfo['icon'] ?? '[STAR]' }}</span>
            <div>
                <h1 class="text-2xl font-bold">{{ $challengeInfo['name'] }}</h1>
                <p class="text-gray-600">{{ $challengeInfo['description'] }}</p>
            </div>
        </div>

        {{-- Challenge Requirements --}}
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Requirements</h3>
            @switch($challengeType)
                @case('dinking_rally')
                    <p class="text-blue-700">Rally lien tuc 20 lan khong loi de pass</p>
                    <p class="text-sm text-blue-600 mt-1">Enter the number of consecutive rallies you achieved</p>
                    @break
                @case('drop_shot')
                    <p class="text-blue-700">Dat 5/10 drop shot vao vung kitchen de pass</p>
                    <p class="text-sm text-blue-600 mt-1">Enter the number of successful drop shots (out of 10)</p>
                    @break
                @case('serve_accuracy')
                    <p class="text-blue-700">Dat 7/10 serve vao vung muc tieu de pass</p>
                    <p class="text-sm text-blue-600 mt-1">Enter the number of successful serves (out of 10)</p>
                    @break
                @case('monthly_test')
                    <p class="text-blue-700">Dat diem 70+ de pass bai test ky thuat hang thang</p>
                    <p class="text-sm text-blue-600 mt-1">Enter your test score (0-100)</p>
                    @break
            @endswitch
        </div>

        {{-- Rewards --}}
        <div class="bg-green-50 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-green-800 mb-2">Reward</h3>
            <p class="text-green-700">
                @if(is_array($challengeInfo['points']))
                    {{ $challengeInfo['points']['min'] }} - {{ $challengeInfo['points']['max'] }} Challenge Points
                @else
                    +{{ $challengeInfo['points'] }} Challenge Points
                @endif
            </p>
        </div>

        {{-- Submit Form --}}
        <form action="{{ route('ocr.challenges.store') }}" method="POST">
            @csrf
            <input type="hidden" name="challenge_type" value="{{ $challengeType }}">

            <div class="mb-6">
                <label for="score" class="block text-sm font-medium text-gray-700 mb-2">
                    Your Score
                </label>
                <input type="number"
                       id="score"
                       name="score"
                       min="0"
                       max="100"
                       required
                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('score') border-red-500 @enderror"
                       placeholder="Enter your score">
                @error('score')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Notes (optional)
                </label>
                <textarea id="notes"
                          name="notes"
                          rows="2"
                          class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Any additional notes..."></textarea>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                Submit Challenge
            </button>
        </form>
    </div>
</div>
@endsection
