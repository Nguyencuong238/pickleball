@extends('layouts.front')

@section('title', 'Check-in - Cộng Đồng')

@section('css')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        padding: 3rem 0;
        color: white;
        margin-top: 100px;
    }

    .page-header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .page-breadcrumb {
        font-size: 0.875rem;
        opacity: 0.8;
    }

    .page-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .page-breadcrumb a:hover {
        text-decoration: underline;
    }

    .checkin-section {
        padding: 2rem 0;
    }

    .checkin-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .checkin-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 2rem;
    }

    .checkin-header {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .checkin-icon {
        font-size: 2rem;
    }

    .checkin-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.25rem 0;
    }

    .checkin-desc {
        color: #64748b;
        margin: 0;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        background-color: white;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-select:focus {
        outline: none;
        border-color: #a855f7;
        box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
    }

    .form-select.error {
        border-color: #ef4444;
    }

    .form-select option:disabled {
        color: #94a3b8;
    }

    .form-error {
        font-size: 0.875rem;
        color: #ef4444;
        margin-top: 0.25rem;
    }

    .submit-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(90deg, #a855f7, #7c3aed);
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .submit-btn:hover {
        background: linear-gradient(90deg, #9333ea, #6d28d9);
    }

    .submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .today-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-top: 1.5rem;
    }

    .today-header {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 700;
        color: #1e293b;
    }

    .today-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .today-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .today-item:last-child {
        border-bottom: none;
    }

    .today-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .today-check {
        color: #22c55e;
        font-size: 1.125rem;
    }

    .today-name {
        color: #1e293b;
    }

    .today-time {
        font-size: 0.875rem;
        color: #94a3b8;
    }

    @media (max-width: 768px) {
        .checkin-card {
            padding: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <div>
                <p class="page-breadcrumb">
                    <a href="{{ route('ocr.index') }}">OCR</a> /
                    <a href="{{ route('ocr.community.index') }}">Cộng Đồng</a> /
                    Check-in
                </p>
                <h1 class="page-title">Check-in</h1>
            </div>
            <a href="{{ route('ocr.community.index') }}" class="btn btn-outline" style="color: white">
                ← Quay Lại Cộng Đồng
            </a>
        </div>
    </div>
</section>

<section class="checkin-section">
    <div class="container">
        <div class="checkin-container">
            <div class="checkin-card">
                <div class="checkin-header">
                    <span class="checkin-icon">📍</span>
                    <div>
                        <h1 class="checkin-title">Check-in</h1>
                        <p class="checkin-desc">Check-in tại sân để nhận +2 Điểm Cộng Đồng</p>
                    </div>
                </div>

                {{-- Check-in Form --}}
                <form action="{{ route('ocr.community.checkin.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="stadium_id" class="form-label">Chọn Sân</label>
                        <select id="stadium_id"
                                name="stadium_id"
                                required
                                class="form-select @error('stadium_id') error @enderror">
                            <option value="">Chọn một sân...</option>
                            @foreach($stadiums as $stadium)
                                <option value="{{ $stadium->id }}"
                                        {{ !$canCheckIn[$stadium->id] ? 'disabled' : '' }}>
                                    {{ $stadium->name }}
                                    @if(!$canCheckIn[$stadium->id])
                                        (Đã check-in hôm nay)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('stadium_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="submit-btn">
                        Check-in
                    </button>
                </form>
            </div>

            {{-- Today's Check-ins --}}
            @if($todayCheckIns->count() > 0)
            <div class="today-card">
                <div class="today-header">Check-in Hôm Nay</div>
                <div class="today-list">
                    @foreach($todayCheckIns as $checkIn)
                    <div class="today-item">
                        <div class="today-info">
                            <span class="today-check">✅</span>
                            <span class="today-name">{{ $checkIn->metadata['stadium_name'] ?? 'Sân' }}</span>
                        </div>
                        <span class="today-time">{{ $checkIn->created_at->format('H:i') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
