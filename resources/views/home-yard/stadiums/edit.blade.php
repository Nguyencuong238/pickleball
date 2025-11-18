@extends('layouts.front')

@section('content')
<style>
    @media (min-width: 768px) {
        .page-header {
            margin-top: 80px;
        }
    }
</style>
<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 80px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        <a href="{{ route('homeyard.stadiums.index') }}" style="color: rgba(255, 255, 255, 0.9); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
        <h1 style="color: white; font-size: clamp(1.75rem, 5vw, 2.5rem); font-weight: 700; margin: 0; line-height: 1.2; word-break: break-word;">Chỉnh Sửa Sân: {{ $stadium->name }}</h1>
    </div>
</div>

<div style="background: #f9fafb; padding: 50px 20px; min-height: 60vh;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        @include('home-yard.stadiums.form')
    </div>
</div>
@endsection
