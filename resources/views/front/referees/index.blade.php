@extends('layouts.front')

@section('title', 'Trong Tai - OnePickleball')

@section('css')
<style>
    .page-hero {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 4rem 2rem;
        text-align: center;
        color: white;
        margin-bottom: 3rem;
    }

    .page-hero h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .page-hero p {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
    }

    .referee-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem 4rem;
    }

    .filter-bar {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .filter-bar input,
    .filter-bar select {
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        flex: 1;
        min-width: 200px;
    }

    .filter-bar input:focus,
    .filter-bar select:focus {
        border-color: var(--primary-color);
        outline: none;
    }

    .filter-bar button {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-search {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
    }

    .btn-clear {
        background: #e2e8f0;
        color: #475569;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .referees-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .referee-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .referee-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-color);
        box-shadow: 0 8px 25px rgba(0,217,181,0.15);
    }

    .referee-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .referee-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .referee-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .referee-stats {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin: 1rem 0;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-matches {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .badge-active {
        background: #D1FAE5;
        color: #059669;
    }

    .badge-inactive {
        background: #F1F5F9;
        color: #64748B;
    }

    .referee-rating {
        color: #F59E0B;
        font-weight: 600;
        margin: 0.5rem 0;
    }

    .btn-profile {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        margin-top: 1rem;
        transition: transform 0.2s;
    }

    .btn-profile:hover {
        transform: scale(1.05);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
    }

    .empty-state p {
        color: #64748B;
        font-size: 1.1rem;
    }

    .pagination-wrapper {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .page-hero {
            padding: 3rem 1rem;
        }

        .page-hero h1 {
            font-size: 2rem;
        }

        .referee-container {
            padding: 0 1rem 2rem;
        }

        .filter-bar {
            flex-direction: column;
        }

        .filter-bar input,
        .filter-bar select {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="page-hero">
    <h1>[WHISTLE] Trong Tai</h1>
    <p>Doi ngu trong tai chuyen nghiep, tan tam voi cac giai dau pickleball tai Viet Nam</p>
</div>

<div class="referee-container">
    {{-- Search and Filter --}}
    <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="[SEARCH] Tim kiem theo ten..." value="{{ request('search') }}">
        <select name="status">
            <option value="">-- Tat ca trang thai --</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Dang hoat dong</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tam nghi</option>
        </select>
        <button type="submit" class="btn-search">[SEARCH] Tim kiem</button>
        <a href="{{ route('academy.referees.index') }}" class="btn-clear">[CLEAR] Xoa</a>
    </form>

    {{-- Referee Cards --}}
    @if($referees->isEmpty())
        <div class="empty-state">
            <p>[INFO] Khong tim thay trong tai nao</p>
        </div>
    @else
        <div class="referees-grid">
            @foreach($referees as $referee)
                <div class="referee-card">
                    <div class="referee-avatar">
                        @if($referee->avatar)
                            <img src="{{ asset('storage/' . $referee->avatar) }}" alt="{{ $referee->name }}">
                        @else
                            {{ strtoupper(substr($referee->name, 0, 1)) }}
                        @endif
                    </div>

                    <h3 class="referee-name">{{ $referee->name }}</h3>

                    @if($referee->location)
                        <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 0.5rem;">
                            [LOCATION] {{ $referee->location }}
                        </p>
                    @endif

                    <div class="referee-stats">
                        <span class="stat-badge badge-matches">
                            [MATCH] {{ $referee->matches_completed ?? 0 }} tran
                        </span>
                        @if($referee->referee_status == 'active')
                            <span class="stat-badge badge-active">[CHECK] Hoat dong</span>
                        @else
                            <span class="stat-badge badge-inactive">[PAUSE] Tam nghi</span>
                        @endif
                    </div>

                    @if($referee->referee_rating)
                        <div class="referee-rating">
                            [STAR] {{ number_format($referee->referee_rating, 1) }} / 5.0
                        </div>
                    @endif

                    <a href="{{ route('academy.referees.show', $referee) }}" class="btn-profile">
                        [VIEW] Xem ho so
                    </a>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $referees->links() }}
        </div>
    @endif
</div>
@endsection
