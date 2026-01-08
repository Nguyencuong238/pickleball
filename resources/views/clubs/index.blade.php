@extends('layouts.front')

@section('content')
<style>
    .clubs-container {
        padding: 40px 20px;
        max-width: 1400px;
        margin: 0 auto;
        margin-top: 100px;
    }

    .clubs-header {
        margin-bottom: 50px;
        text-align: center;
    }

    .clubs-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 20px;
    }

    .clubs-header .btn-create {
        display: inline-block;
        padding: 12px 30px;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        margin-top: 20px;
        transition: all 0.3s ease;
    }

    .clubs-header .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
    }

    .clubs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    .club-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .club-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 217, 181, 0.2);
    }

    .club-image {
        width: 100%;
        height: 200px;
        /* background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%); */
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        overflow: hidden;
    }

    .club-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        /* background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%); */
    }

    .club-content {
        padding: 20px;
    }

    .club-type {
        display: inline-block;
        padding: 4px 12px;
        background: #f0f9ff;
        color: #0084ff;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .club-name {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
        word-break: break-word;
    }

    .club-creator {
        color: #9ca3af;
        font-size: 0.85rem;
        margin-bottom: 10px;
    }

    .club-description {
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .club-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-top: 1px solid #f3f4f6;
        margin-bottom: 15px;
        font-size: 0.85rem;
        color: #6b7280;
    }

    .club-meta-item {
        text-align: center;
    }

    .club-meta-item .count {
        font-weight: 700;
        color: #00D9B5;
        font-size: 1.2rem;
    }

    .club-actions {
        display: flex;
        gap: 10px;
    }

    .btn-view {
        flex: 1;
        padding: 10px;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        text-align: center;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-view:hover {
        color: white;
        transform: translateX(3px);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 3rem;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .clubs-header h2 {
            font-size: 1.8rem;
        }

        .clubs-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    /* Pagination Styles */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 40px;
        padding: 20px;
    }

    .pagination li {
        list-style: none;
    }

    .pagination a,
    .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 8px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        color: #6b7280;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border-color: #00D9B5;
    }

    .pagination .active span {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border-color: #00D9B5;
    }

    .pagination .disabled span {
        color: #d1d5db;
        cursor: not-allowed;
        background: #f9fafb;
    }

    .pagination li.active a {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border-color: #00D9B5;
    }
</style>

<div class="clubs-container">
    <div class="clubs-header">
        <h2>🏆 Câu Lạc Bộ & Nhóm Pickleball</h2>
        <p>Khám phá, tham gia hoặc tạo câu lạc bộ và nhóm của bạn</p>
        @auth
            <a href="{{ route('clubs.create') }}" class="btn-create">+ Tạo Câu Lạc Bộ/Nhóm</a>
        @else
            <button class="btn-create" onclick="alertLogin()" style="cursor: pointer; opacity: 0.7;">+ Tạo Câu Lạc Bộ/Nhóm</button>
        @endauth
    </div>

    @if($clubs->count() > 0)
        <div class="clubs-grid">
            @foreach($clubs as $club)
                <div class="club-card">
                    <div class="club-image">
                        @if($club->image)
                            <img src="{{ asset('storage/' . $club->image) }}" alt="{{ $club->name }}">
                        @else
                            <span>{{ ucfirst(substr($club->type, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="club-content">
                        <span class="club-type">{{ $club->type == 'club' ? '🏪 Câu Lạc Bộ' : '👥 Nhóm' }}</span>
                        <div class="club-name">{{ $club->name }}</div>
                        <div class="club-creator">👤 {{ $club->creator->name }}</div>
                        <div class="club-description">{{ Str::limit($club->description, 80) }}</div>
                        
                        <div class="club-meta">
                            <div class="club-meta-item">
                                <div class="count">{{ $club->members->count() }}</div>
                                <div>Thành viên</div>
                            </div>
                            <div class="club-meta-item">
                                <div class="count">{{ $club->provinces->count() }}</div>
                                <div>Tỉnh</div>
                            </div>
                            <div class="club-meta-item">
                                <div class="count">{{ $club->activities->count() }}</div>
                                <div>Hoạt động</div>
                            </div>
                        </div>

                        <div class="club-actions">
                            <a href="{{ route('clubs.show', $club) }}" class="btn-view">Xem Chi Tiết →</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="text-align: center; margin-top: 30px;">
            {{ $clubs->links('vendor.pagination.custom-clubs') }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Chưa có câu lạc bộ/nhóm nào</h3>
            <p>Hãy tạo câu lạc bộ/nhóm đầu tiên của bạn hoặc chờ để tham gia!</p>
            @auth
                <a href="{{ route('clubs.create') }}" class="btn-view" style="display: inline-block; margin-top: 20px; padding: 12px 30px;">
                    + Tạo Câu Lạc Bộ/Nhóm
                </a>
            @else
                <button onclick="alertLogin()" class="btn-view" style="display: inline-block; margin-top: 20px; padding: 12px 30px; cursor: pointer;">
                    + Tạo Câu Lạc Bộ/Nhóm
                </button>
            @endauth
        </div>
    @endif
</div>

<script>
    function alertLogin() {
        const result = confirm('Vui lòng đăng nhập để tạo câu lạc bộ/nhóm!\n\nClick OK để đăng nhập');
        if (result) {
            window.location.href = '{{ route("login") }}';
        }
    }
</script>

@endsection
