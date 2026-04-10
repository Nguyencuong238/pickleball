@extends('layouts.front')

@section('seo')
<title>{{ $user->name }} | OPRS {{ number_format($user->total_oprs, 0) }} - OnePickleball</title>
<meta name="description" content="{{ $user->name }} - {{ $user->elo_rank }} | #{{ $globalRank }} Toàn Quốc | {{ $user->ocr_wins }}T-{{ $user->ocr_losses }}B | OnePickleball">
<meta property="og:title" content="{{ $user->name }} | OPRS {{ number_format($user->total_oprs, 0) }}">
<meta property="og:description" content="{{ $user->elo_rank }} | #{{ $globalRank }} Toàn Quốc | {{ $user->ocr_wins }}T-{{ $user->ocr_losses }}B">
<meta property="og:image" content="{{ asset('assets/images/og-profile-card.png') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="profile">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $user->name }} | OPRS {{ number_format($user->total_oprs, 0) }}">
<meta name="twitter:description" content="{{ $user->elo_rank }} | #{{ $globalRank }} Toàn Quốc | {{ $user->ocr_wins }}T-{{ $user->ocr_losses }}B">
<meta name="twitter:image" content="{{ asset('assets/images/og-profile-card.png') }}">
<link rel="canonical" href="{{ url()->current() }}">
@endsection

@section('css')
<style>
    /* ===== Hero Section ===== */
    .profile-hero {
        background: linear-gradient(135deg, #006646 0%, #004d33 50%, #1a1a2e 100%);
        padding: 6rem 0 3rem;
        color: white;
    }

    .hero-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .hero-card {
        background: rgba(255, 255, 255, 0.08);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .hero-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 2.5rem;
        margin: 0 auto 1rem;
        border: 3px solid rgba(82, 201, 140, 0.6);
        box-shadow: 0 0 20px rgba(82, 201, 140, 0.3);
    }

    .hero-name {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.5rem;
        line-height: 1.2;
    }

    .hero-badges-row {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .hero-rank-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .rank-bronze { background: #fef3c7; color: #b45309; }
    .rank-silver { background: #f1f5f9; color: #475569; }
    .rank-gold { background: #fef3c7; color: #d97706; }
    .rank-platinum { background: #e0f2fe; color: #0284c7; }
    .rank-diamond { background: #ede9fe; color: #7c3aed; }
    .rank-master { background: #fce7f3; color: #db2777; }
    .rank-grandmaster { background: #fee2e2; color: #dc2626; }

    .hero-verified {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .verified-yes { background: rgba(34, 197, 94, 0.2); color: #86efac; }
    .verified-pending { background: rgba(250, 204, 21, 0.2); color: #fde68a; }
    .verified-waiting { background: rgba(96, 165, 250, 0.2); color: #93c5fd; }

    /* OPRS Primary Display */
    .hero-oprs {
        margin: 1.5rem 0;
    }

    .hero-oprs-value {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -1px;
    }

    .hero-oprs-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        opacity: 0.7;
        margin-top: 0.25rem;
    }

    .hero-global-rank {
        font-size: 0.9rem;
        opacity: 0.8;
        margin-top: 0.5rem;
    }

    /* Stat Pills */
    .hero-stats {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin: 1.5rem 0;
    }

    .stat-pill {
        background: rgba(0, 60, 40, 0.5);
        border-radius: 12px;
        padding: 0.75rem 1.25rem;
        text-align: center;
        min-width: 80px;
        border: 1px solid rgba(82, 201, 140, 0.25);
    }

    .stat-pill-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
        color: white;
    }

    .stat-pill-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.7);
        letter-spacing: 0.5px;
    }

    /* Action Buttons */
    .hero-actions {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: opacity 0.2s;
        min-height: 44px;
    }

    .hero-btn:hover { opacity: 0.85; }

    .hero-btn-primary {
        background: linear-gradient(135deg, #52c98c, #006646);
        color: white;
    }

    .hero-btn-outline {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .hero-btn-share {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* ===== Content Section ===== */
    .profile-content {
        padding: 2rem 0 3rem;
        background: #f8fafc;
    }

    .content-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .content-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .content-card.full-span {
        grid-column: 1 / -1;
    }

    .card-hdr {
        padding: 1rem 1.25rem;
        font-weight: 700;
        font-size: 0.95rem;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-hdr-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .card-bdy {
        padding: 1.25rem;
    }

    .empty-msg {
        text-align: center;
        padding: 1.5rem;
        color: #94a3b8;
        font-size: 0.875rem;
    }

    /* ===== OPRS Detail Card ===== */
    .oprs-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .oprs-detail-score {
        font-size: 2rem;
        font-weight: 700;
        color: #006646;
    }

    .oprs-breakdown-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .oprs-breakdown-row:last-child {
        margin-bottom: 0;
    }

    .oprs-bk-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .oprs-bk-info {
        flex: 1;
        min-width: 0;
    }

    .oprs-bk-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }

    .oprs-bk-name {
        font-size: 0.8rem;
        font-weight: 500;
        color: #374151;
    }

    .oprs-bk-val {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1e293b;
    }

    .oprs-bk-bar {
        width: 100%;
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .oprs-bk-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s;
    }

    .oprs-bk-fill.elo { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .oprs-bk-fill.challenge { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
    .oprs-bk-fill.community { background: linear-gradient(90deg, #a855f7, #7c3aed); }

    /* Level Progress */
    .oprs-level-progress {
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    .oprs-level-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .oprs-level-bar {
        width: 100%;
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .oprs-level-fill {
        height: 100%;
        background: linear-gradient(90deg, #006646, #52c98c);
        border-radius: 3px;
    }

    /* ===== Badges ===== */
    .badges-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .badge-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.4rem;
    }

    .badge-circle {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: white;
    }

    .badge-circle.gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); box-shadow: 0 3px 10px rgba(251, 191, 36, 0.3); }
    .badge-circle.silver { background: linear-gradient(135deg, #94a3b8, #64748b); box-shadow: 0 3px 10px rgba(148, 163, 184, 0.3); }
    .badge-circle.green { background: linear-gradient(135deg, #52c98c, #006646); box-shadow: 0 3px 10px rgba(82, 201, 140, 0.3); }
    .badge-circle.blue { background: linear-gradient(135deg, #60a5fa, #2563eb); box-shadow: 0 3px 10px rgba(96, 165, 250, 0.3); }
    .badge-circle.purple { background: linear-gradient(135deg, #a78bfa, #7c3aed); box-shadow: 0 3px 10px rgba(167, 139, 250, 0.3); }

    .badge-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #475569;
        max-width: 70px;
        line-height: 1.2;
    }

    .badge-progress-section {
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    .badge-progress-title {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #64748b;
    }

    .badge-progress-item {
        margin-bottom: 0.75rem;
    }

    .badge-progress-top {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }

    .badge-progress-name {
        font-weight: 600;
        color: #1e293b;
    }

    .badge-progress-count {
        color: #64748b;
    }

    .badge-progress-bar {
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .badge-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #006646, #52c98c);
        border-radius: 3px;
        transition: width 0.3s;
    }

    /* ===== Elo History ===== */
    .elo-list {
        max-height: 250px;
        overflow-y: auto;
    }

    .elo-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .elo-item:last-child { border-bottom: none; }

    .elo-reason {
        font-size: 0.8rem;
        color: #374151;
    }

    .elo-date {
        font-size: 0.7rem;
        color: #94a3b8;
        margin-top: 2px;
    }

    .elo-change-badge {
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        flex-shrink: 0;
    }

    .elo-change-badge.positive { background: #dcfce7; color: #166534; }
    .elo-change-badge.negative { background: #fee2e2; color: #991b1b; }

    /* ===== Recent Matches ===== */
    .match-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .match-item:last-child { border-bottom: none; }

    .match-players {
        font-size: 0.85rem;
        color: #1e293b;
    }

    .match-vs {
        color: #94a3b8;
        margin: 0 0.25rem;
        font-size: 0.75rem;
    }

    .match-result {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .match-score {
        font-weight: 700;
        font-size: 0.85rem;
        color: #1e293b;
    }

    .match-outcome-badge {
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .outcome-w { background: #dcfce7; color: #166534; }
    .outcome-l { background: #fee2e2; color: #991b1b; }

    /* ===== Bottom Actions ===== */
    .profile-bottom-actions {
        text-align: center;
        margin-top: 2rem;
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .bottom-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.6rem 1.5rem;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        min-height: 48px;
        transition: opacity 0.2s;
    }

    .bottom-btn:hover { opacity: 0.85; }

    .bottom-btn-outline {
        background: white;
        color: #006646;
        border: 2px solid #006646;
    }

    .bottom-btn-primary {
        background: linear-gradient(135deg, #006646, #52c98c);
        color: white;
        border: none;
    }

    /* ===== Share Toast ===== */
    .share-toast {
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #1e293b;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        z-index: 9999;
        transition: transform 0.3s ease;
        pointer-events: none;
    }

    .share-toast.show {
        transform: translateX(-50%) translateY(0);
    }

    /* ===== Responsive ===== */
    @media (max-width: 1024px) {
        .hero-card {
            padding: 2rem 1.5rem;
        }
    }

    @media (max-width: 640px) {
        .profile-hero {
            padding: 5rem 0 2rem;
        }

        .hero-card {
            padding: 1.5rem 1rem;
            border-radius: 16px;
        }

        .hero-avatar {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }

        .hero-name {
            font-size: 1.5rem;
        }

        .hero-oprs-value {
            font-size: 2.5rem;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .stat-pill {
            padding: 0.6rem 0.75rem;
            min-width: unset;
        }

        .stat-pill-value {
            font-size: 1.25rem;
        }

        .hero-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .hero-btn {
            justify-content: center;
            min-height: 48px;
        }

        .content-grid {
            grid-template-columns: 1fr;
        }

        .content-card.full-span {
            grid-column: auto;
        }

        .profile-bottom-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .bottom-btn {
            justify-content: center;
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endsection

@section('content')
{{-- ===== HERO SECTION ===== --}}
<section class="profile-hero">
    <div class="hero-container">
        <div class="hero-card">
            {{-- Avatar --}}
            <div class="hero-avatar">
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>

            {{-- Name --}}
            <h1 class="hero-name">{{ $user->name }}</h1>

            {{-- Rank + Verification badges --}}
            <div class="hero-badges-row">
                @if($user->elo_rank)
                    <span class="hero-rank-badge rank-{{ strtolower($user->elo_rank) }}">
                        {{ $user->elo_rank }}
                    </span>
                @endif

                @if($user->is_elo_verified)
                    <span class="hero-verified verified-yes">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Đã Xác Minh
                    </span>
                @elseif($user->elo_is_provisional)
                    <span class="hero-verified verified-pending">Chưa Xác Minh</span>
                    @auth
                        @if(auth()->id() === $user->id)
                            @if($user->canRequestVerification())
                                <a href="{{ route('opr-verification.create') }}" class="hero-verified" style="background: rgba(82,201,140,0.3); color: #86efac; text-decoration: none;">
                                    Gửi Xác Minh
                                </a>
                            @elseif($user->hasPendingVerificationRequest())
                                <span class="hero-verified verified-waiting">Đang Chờ Duyệt</span>
                            @endif
                        @endif
                    @endauth
                @endif
            </div>

            {{-- OPRS Primary --}}
            <div class="hero-oprs">
                <div class="hero-oprs-value">{{ number_format($user->total_oprs, 0) }}</div>
                <div class="hero-oprs-label">OPRS</div>
                <div class="hero-global-rank">#{{ $globalRank }} Toàn Quốc</div>
            </div>

            {{-- 4 Stat Pills --}}
            <div class="hero-stats">
                <div class="stat-pill">
                    <div class="stat-pill-value">{{ $user->elo_rating }}</div>
                    <div class="stat-pill-label">Elo</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-value">{{ $user->total_ocr_matches }}</div>
                    <div class="stat-pill-label">Trận Đấu</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-value">{{ $user->ocr_wins }}</div>
                    <div class="stat-pill-label">Thắng</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-value">{{ $user->ocr_losses }}</div>
                    <div class="stat-pill-label">Thua</div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="hero-actions">
                <button type="button" class="hero-btn hero-btn-share" onclick="shareProfile()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                    Chia Sẻ
                </button>

                @auth
                    @if(auth()->id() === $user->id)
                        <a href="{{ route('ocr.community.index') }}" class="hero-btn hero-btn-outline">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            Cộng Đồng
                        </a>
                    @else
                        <a href="{{ route('ocr.matches.create') }}?opponent={{ $user->id }}" class="hero-btn hero-btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                            Thách Đấu
                        </a>
                    @endif
                @else
                    <a href="{{ route('ocr.matches.create') }}?opponent={{ $user->id }}" class="hero-btn hero-btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        Thách Đấu
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>

{{-- ===== CONTENT SECTION ===== --}}
<section class="profile-content">
    <div class="content-container">
        <div class="content-grid">

            {{-- OPRS Chi Tiet --}}
            <div class="content-card">
                <div class="card-hdr">
                    <span class="card-hdr-icon" style="background: linear-gradient(135deg, #006646, #52c98c);">O</span>
                    Chi Tiết OPRS
                </div>
                <div class="card-bdy">
                    <div class="oprs-detail-header">
                        <div class="oprs-detail-score">{{ number_format($user->total_oprs, 0) }}</div>
                        <x-oprs.skill-level-badge :elo="$user->elo_rating" :gender="$user->gender" />
                    </div>

                    @php
                        $maxWeighted = max($oprsBreakdown['elo']['weighted'], $oprsBreakdown['challenge']['weighted'], $oprsBreakdown['community']['weighted'], 1);
                    @endphp

                    {{-- Elo (70%) --}}
                    <div class="oprs-breakdown-row">
                        <div class="oprs-bk-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">E</div>
                        <div class="oprs-bk-info">
                            <div class="oprs-bk-top">
                                <span class="oprs-bk-name">Elo (70%)</span>
                                <span class="oprs-bk-val">{{ number_format($oprsBreakdown['elo']['weighted'], 0) }}</span>
                            </div>
                            <div class="oprs-bk-bar">
                                <div class="oprs-bk-fill elo" style="width: {{ min(100, ($oprsBreakdown['elo']['weighted'] / $maxWeighted) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Challenge (20%) --}}
                    <div class="oprs-breakdown-row">
                        <div class="oprs-bk-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">C</div>
                        <div class="oprs-bk-info">
                            <div class="oprs-bk-top">
                                <span class="oprs-bk-name">Thử Thách (20%)</span>
                                <span class="oprs-bk-val">{{ number_format($oprsBreakdown['challenge']['weighted'], 0) }}</span>
                            </div>
                            <div class="oprs-bk-bar">
                                <div class="oprs-bk-fill challenge" style="width: {{ min(100, ($oprsBreakdown['challenge']['weighted'] / $maxWeighted) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Community (10%) --}}
                    <div class="oprs-breakdown-row">
                        <div class="oprs-bk-icon" style="background: linear-gradient(135deg, #a855f7, #7c3aed);">D</div>
                        <div class="oprs-bk-info">
                            <div class="oprs-bk-top">
                                <span class="oprs-bk-name">Cộng Đồng (10%)</span>
                                <span class="oprs-bk-val">{{ number_format($oprsBreakdown['community']['weighted'], 0) }}</span>
                            </div>
                            <div class="oprs-bk-bar">
                                <div class="oprs-bk-fill community" style="width: {{ min(100, ($oprsBreakdown['community']['weighted'] / $maxWeighted) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Level Progress --}}
                    @php
                        $levels = App\Models\User::getOprLevels();
                        $currentLevel = $user->opr_level;
                        $currentLevelInfo = $levels[$currentLevel] ?? null;
                        $levelKeys = array_keys($levels);
                        $currentIndex = array_search($currentLevel, $levelKeys);
                        $nextLevel = isset($levelKeys[$currentIndex + 1]) ? $levelKeys[$currentIndex + 1] : null;
                        $nextLevelInfo = $nextLevel ? $levels[$nextLevel] : null;
                        $progressPercent = 0;
                        $pointsToNext = 0;
                        if ($currentLevelInfo && $nextLevelInfo) {
                            $rangeSize = $currentLevelInfo['max'] - $currentLevelInfo['min'];
                            $progress = $user->total_oprs - $currentLevelInfo['min'];
                            $progressPercent = min(100, ($progress / max(1, $rangeSize)) * 100);
                            $pointsToNext = $nextLevelInfo['min'] - $user->total_oprs;
                        }
                    @endphp

                    @if($nextLevelInfo)
                    <div class="oprs-level-progress">
                        <div class="oprs-level-info">
                            <span>Tiếp: {{ $nextLevelInfo['name'] }}</span>
                            <span>Còn {{ number_format($pointsToNext, 0) }} điểm</span>
                        </div>
                        <div class="oprs-level-bar">
                            <div class="oprs-level-fill" style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Thanh Tich (Badges) --}}
            <div class="content-card">
                <div class="card-hdr">
                    <span class="card-hdr-icon" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">H</span>
                    Huy Hiệu ({{ $user->badges->count() }})
                </div>
                <div class="card-bdy">
                    @if($user->badges->isEmpty())
                        <div class="empty-msg">Chưa có huy hiệu nào</div>
                    @else
                        <div class="badges-grid">
                            @foreach($user->badges as $badge)
                                @php
                                    $badgeInfo = \App\Models\UserBadge::getBadgeInfo($badge->badge_type) ?? [];
                                    $badgeColors = ['first_win' => 'gold', 'streak_3' => 'gold', 'streak_5' => 'blue', 'streak_10' => 'purple', 'rank_silver' => 'silver', 'rank_gold' => 'gold', 'rank_platinum' => 'blue', 'rank_diamond' => 'purple'];
                                    $colorClass = $badgeColors[$badge->badge_type] ?? 'green';
                                    $badgeLetter = strtoupper(mb_substr($badgeInfo['name'] ?? $badge->badge_type, 0, 1));
                                @endphp
                                <div class="badge-item" title="{{ $badgeInfo['description'] ?? '' }}">
                                    <div class="badge-circle {{ $colorClass }}">{{ $badgeLetter }}</div>
                                    <div class="badge-label">{{ $badgeInfo['name'] ?? $badge->badge_type }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($badgeProgress))
                        <div class="badge-progress-section">
                            <div class="badge-progress-title">Tiến Trình</div>
                            @foreach($badgeProgress as $type => $progress)
                                @if(isset($progress['current']) && isset($progress['target']) && $progress['current'] < $progress['target'])
                                    <div class="badge-progress-item">
                                        <div class="badge-progress-top">
                                            <span class="badge-progress-name">{{ $progress['name'] ?? $type }}</span>
                                            <span class="badge-progress-count">{{ $progress['current'] }}/{{ $progress['target'] }}</span>
                                        </div>
                                        <div class="badge-progress-bar">
                                            <div class="badge-progress-fill" style="width: {{ min(100, ($progress['current'] / max(1, $progress['target'])) * 100) }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Lịch Sử Elo --}}
            <div class="content-card">
                <div class="card-hdr">
                    <span class="card-hdr-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">E</span>
                    Lịch Sử Elo
                </div>
                <div class="card-bdy">
                    @if($eloHistory->isEmpty())
                        <div class="empty-msg">Chưa có lịch sử Elo</div>
                    @else
                        <div class="elo-list">
                            @foreach($eloHistory as $history)
                                <div class="elo-item">
                                    <div>
                                        <div class="elo-reason">{{ $history->reason }}</div>
                                        <div class="elo-date">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <span class="elo-change-badge {{ $history->change > 0 ? 'positive' : ($history->change < 0 ? 'negative' : '') }}">
                                        {{ $history->change > 0 ? '+' : '' }}{{ $history->change }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Trận Đấu Gần Đây --}}
            <div class="content-card">
                <div class="card-hdr">
                    <span class="card-hdr-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">T</span>
                    Trận Đấu Gần Đây
                </div>
                <div class="card-bdy">
                    @if($recentMatches->isEmpty())
                        <div class="empty-msg">Chưa có trận đấu nào</div>
                    @else
                        @foreach($recentMatches as $match)
                            @php
                                $isChallenger = $match->challenger_id === $user->id || $match->challenger_partner_id === $user->id;
                                $userWon = ($isChallenger && $match->winner_team === 'challenger') ||
                                           (!$isChallenger && $match->winner_team === 'opponent');
                            @endphp
                            <div class="match-item">
                                <div class="match-players">
                                    <span>{{ $match->challenger->name ?? 'Unknown' }}</span>
                                    <span class="match-vs">vs</span>
                                    <span>{{ $match->opponent->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="match-result">
                                    <span class="match-score">{{ $match->challenger_score }} - {{ $match->opponent_score }}</span>
                                    <span class="match-outcome-badge {{ $userWon ? 'outcome-w' : 'outcome-l' }}">
                                        {{ $userWon ? 'Thắng' : 'Thua' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

        </div>

        {{-- Bottom Actions --}}
        <div class="profile-bottom-actions">
            <a href="{{ route('ocr.leaderboard') }}" class="bottom-btn bottom-btn-outline">
                Xem Bảng Xếp Hạng
            </a>
            @auth
                @if(auth()->id() !== $user->id)
                    <a href="{{ route('ocr.matches.create') }}?opponent={{ $user->id }}" class="bottom-btn bottom-btn-primary">
                        Thách Đấu
                    </a>
                @endif
            @endauth
        </div>
    </div>
</section>

{{-- Share Toast --}}
<div id="shareToast" class="share-toast">Đã sao chép liên kết!</div>
@endsection

@section('js')
<script>
function shareProfile() {
    var url = '{{ url()->current() }}';
    var title = @json($user->name) + ' | OPRS {{ number_format($user->total_oprs, 0) }} - OnePickleball';

    if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(function() {});
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function() {
            showShareToast();
        });
    } else {
        var input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showShareToast();
    }
}

function showShareToast() {
    var toast = document.getElementById('shareToast');
    toast.classList.add('show');
    setTimeout(function() { toast.classList.remove('show'); }, 2000);
}
</script>
@endsection
