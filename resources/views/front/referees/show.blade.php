@extends('layouts.front')

@section('title', $referee->name . ' - Trong Tai')

@section('css')
<style>
    .profile-hero {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 3rem 2rem;
        color: white;
    }

    .profile-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: white;
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 2rem;
        opacity: 0.9;
        transition: opacity 0.2s;
    }

    .back-link:hover {
        opacity: 1;
        color: white;
    }

    .profile-header {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        font-weight: 700;
        color: var(--primary-color);
        flex-shrink: 0;
        border: 4px solid rgba(255,255,255,0.3);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-info h1 {
        font-size: 2.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .profile-location {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 1rem;
    }

    .profile-status {
        display: inline-block;
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .status-active {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    .status-inactive {
        background: rgba(0,0,0,0.2);
        color: white;
    }

    .profile-rating {
        margin-top: 1rem;
        font-size: 1.25rem;
    }

    .content-section {
        max-width: 1000px;
        margin: -2rem auto 0;
        padding: 0 2rem 4rem;
        position: relative;
        z-index: 10;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: #64748B;
        font-size: 0.9rem;
    }

    .section-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }

    .section-header {
        padding: 1.25rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .section-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .section-body {
        padding: 1.5rem;
    }

    .bio-text {
        color: #475569;
        line-height: 1.8;
    }

    .matches-table {
        width: 100%;
        border-collapse: collapse;
    }

    .matches-table th {
        text-align: left;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        font-weight: 600;
        color: #1e293b;
        border-bottom: 2px solid #e2e8f0;
    }

    .matches-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
    }

    .matches-table tr:hover td {
        background: #f8fafc;
    }

    .tournament-name {
        font-weight: 600;
        color: #1e293b;
    }

    .category-name {
        font-size: 0.85rem;
        color: #64748B;
    }

    .match-score {
        font-weight: 700;
        color: var(--primary-color);
    }

    .empty-message {
        text-align: center;
        padding: 2rem;
        color: #64748B;
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-info h1 {
            font-size: 1.75rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .content-section {
            padding: 0 1rem 2rem;
        }

        .matches-table {
            font-size: 0.9rem;
        }

        .matches-table th,
        .matches-table td {
            padding: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="profile-hero" style="margin-top: 90px">
    <div class="profile-container">
        <a href="{{ route('academy.referees.index') }}" class="back-link">
            ← Quay lại danh sách trọng tài
        </a>

        <div class="profile-header">
            <div class="profile-avatar">
                @if($referee->avatar)
                    <img src="{{ asset('storage/' . $referee->avatar) }}" alt="{{ $referee->name }}">
                @else
                    {{ strtoupper(substr($referee->name, 0, 1)) }}
                @endif
            </div>

            <div class="profile-info">
                <h1>{{ $referee->name }}</h1>

                @if($referee->location)
                    <p class="profile-location">📍 {{ $referee->location }}</p>
                @endif

                @if($referee->referee_status == 'active')
                    <span class="profile-status status-active">Đang hoạt động</span>
                @else
                    <span class="profile-status status-inactive">Tạm nghỉ</span>
                @endif

                @if($referee->referee_rating)
                    <div class="profile-rating">
                        ⭐ {{ number_format($referee->referee_rating, 1) }} / 5.0
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="content-section">
    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_matches'] }}</div>
            <div class="stat-label">Tổng trận</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['completed_matches'] }}</div>
            <div class="stat-label">Hoàn thành</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['completion_rate'] }}%</div>
            <div class="stat-label">Tỉ lệ hoàn thành</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['tournaments'] }}</div>
            <div class="stat-label">Giải đấu</div>
        </div>
    </div>

    {{-- Bio Section --}}
    @if($referee->referee_bio)
        <div class="section-card">
            <div class="section-header">
                <h3>Giới thiệu</h3>
            </div>
            <div class="section-body">
                <p class="bio-text">{{ $referee->referee_bio }}</p>
            </div>
        </div>
    @endif

    {{-- Recent Matches --}}
    <div class="section-card">
        <div class="section-header">
            <h3>Trận đấu gần đây</h3>
        </div>
        <div class="section-body" style="padding: 0;">
            @if($referee->refereeMatches->isEmpty())
                <div class="empty-message">
                    Chưa có trận đấu nào đã hoàn thành
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="matches-table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Giải đấu</th>
                                <th>Trận đấu</th>
                                <th>Tỉ số</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referee->refereeMatches as $match)
                                <tr>
                                    <td>{{ $match->match_date ? $match->match_date->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <span class="tournament-name">{{ $match->tournament->name ?? 'N/A' }}</span>
                                        @if($match->category)
                                            <br><span class="category-name">{{ $match->category->name }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $match->athlete1_name ?? 'TBD' }} vs {{ $match->athlete2_name ?? 'TBD' }}</td>
                                    <td class="match-score">{{ $match->final_score ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
