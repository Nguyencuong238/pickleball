@extends('layouts.front')

@section('seo')
<title>{{ $activity->title }} | {{ $club->name }}</title>
<meta name="description" content="{{ Str::limit(strip_tags($activity->description), 160) }}">
<meta property="og:title" content="{{ $activity->title }} | {{ $club->name }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($activity->description), 160) }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ route('clubs.activities.show', [$club, $activity]) }}">
@if($club->image)
<meta property="og:image" content="{{ storage_url($club->image) }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $activity->title }} | {{ $club->name }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($activity->description), 160) }}">
@endsection

@section('content')
@php
    $typeLabels = ['one_off' => 'Buổi chơi', 'recurring' => 'Lịch cố định', 'competition' => 'Giải đấu'];
@endphp
@include('clubs.activities.partials._show-styles')

<div class="activity-container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Gradient header banner --}}
    @include('clubs.activities.partials._header-banner')

    <div class="activity-main-card">
        {{-- Tab navigation --}}
        @include('clubs.activities.partials._tab-navigation')

        {{-- Tab: Chi tiet --}}
        <div class="tab-content active" id="tab-detail" role="tabpanel">
            @include('clubs.activities.partials._detail-tab')
        </div>

        {{-- Tab: Nguoi tham gia --}}
        <div class="tab-content" id="tab-participants" role="tabpanel">
            @include('clubs.activities.partials._participants-tab')
        </div>

        {{-- Tab: Tran dau (competition only) --}}
        @if($activity->type === 'competition')
        <div class="tab-content" id="tab-competition" role="tabpanel">
            @include('clubs.activities.partials._competition-panel')
        </div>
        @endif

        {{-- Tab: Tran dau (non-competition casual matches) --}}
        @if($activity->type !== 'competition' && !$activity->isRecurringTemplate())
        <div class="tab-content" id="tab-matches" role="tabpanel">
            @include('clubs.activities.partials._matches-panel')
        </div>
        @endif
    </div>
</div>

{{-- RSVP script --}}
@include('clubs.activities.partials._rsvp-panel')

{{-- Tab scripts --}}
@include('clubs.activities.partials._tab-scripts')
@endsection
