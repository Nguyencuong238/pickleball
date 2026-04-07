@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/homepage.css') }}?v=1">
@endsection

@section('content')
<div class="homepage">
    @include('front.partials.home-hero')
    @include('front.partials.home-stats-band')

    <div class="section-inner" style="margin-top: 24px;">
        @include('front.partials._special_challenge_banner')
    </div>

    @include('front.partials.home-tournaments')
    @include('front.partials.home-courts')
    @include('front.partials.home-community')
    @include('front.partials.home-news')
    @include('front.partials.home-features')
    @include('front.partials.home-cta')
</div>

@include('front.partials.home-scroll-reveal-script')
@endsection
