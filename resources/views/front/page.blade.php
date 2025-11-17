@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div style="background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%); padding: clamp(40px, 8vw, 80px) 20px;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        <h1 style="color: white; font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 700; margin: 0; line-height: 1.2;">
            {{ $page->title }}
        </h1>
        @if($page->meta_description)
            <p style="color: rgba(255, 255, 255, 0.9); font-size: clamp(0.95rem, 2vw, 1.1rem); margin-top: 10px;">
                {{ $page->meta_description }}
            </p>
        @endif
    </div>
</div>

<!-- Main Content -->
<div style="background: #f9fafb; padding: clamp(40px, 8vw, 80px) 20px; min-height: 60vh;">
    <div class="container" style="max-width: 900px; margin: 0 auto; background: white; border-radius: 15px; padding: clamp(30px, 5vw, 50px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);">
        <div style="color: #4b5563; line-height: 1.8; font-size: 1.05rem;">
            {!! $page->content !!}
        </div>

        <!-- Published Info -->
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #9ca3af; font-size: 0.9rem;">
            @if($page->author)
                <p>By <strong>{{ $page->author }}</strong></p>
            @endif
            <p>Published: <strong>{{ $page->published_at->format('F d, Y') }}</strong></p>
        </div>

        <!-- Back Link -->
        <div style="margin-top: 30px;">
            <a href="{{ url('/') }}" style="color: #00D9B5; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
