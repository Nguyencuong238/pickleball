@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-cards.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
@endsection

@section('content')
<div class="main-content">
    <div class="top-header">
        <h2 class="page-title">Giải đấu của tôi</h2>
        <a href="{{ route('tournament-manage.tournaments.create') }}" class="td-btn td-btn-primary">
            + Tạo giải đấu
        </a>
    </div>

    @if(session('success'))
        <div class="td-alert td-alert-success">{{ session('success') }}</div>
    @endif

    <div x-data="tournamentIndex()" x-init="filterCards()">
        <div class="td-search-bar">
            <input type="text" class="td-search-input" placeholder="Tìm kiếm giải đấu..."
                x-model="search" @input="filterCards()">
            <select class="td-filter-select" x-model="statusFilter" @change="filterCards()">
                <option value="">Tất cả trạng thái</option>
                <option value="1">Hoạt động</option>
                <option value="0">Không hoạt động</option>
            </select>
        </div>

        @if($tournaments->count() > 0)
            <div class="td-tournament-grid">
                @foreach($tournaments as $tournament)
                <div data-tournament
                     data-name="{{ $tournament->name }}"
                     data-status="{{ $tournament->status ? '1' : '0' }}">
                    <div class="td-tournament-card">
                        @if($tournament->getFirstMediaUrl('banner'))
                            <img src="{{ $tournament->getFirstMediaUrl('banner') }}"
                                 alt="{{ $tournament->name }}" class="td-tournament-banner">
                        @else
                            <div class="td-tournament-banner-placeholder">&#127942;</div>
                        @endif

                        <div class="td-tournament-body">
                            <div class="td-tournament-name">{{ $tournament->name }}</div>
                            <div class="td-tournament-meta">
                                <span>{{ $tournament->start_date->format('d/m/Y') }}</span>
                                @if($tournament->location)
                                    <span>{{ $tournament->location }}</span>
                                @endif
                                <span>{{ $tournament->athleteCount() }} VĐV</span>
                            </div>
                            <span class="td-status {{ $tournament->status ? 'td-status-active' : 'td-status-inactive' }}">
                                {{ $tournament->status ? 'Hoạt động' : 'Không hoạt động' }}
                            </span>
                        </div>

                        <div class="td-tournament-footer">
                            <a href="{{ route('tournament-manage.tournaments.show', $tournament) }}"
                               class="td-btn td-btn-primary" style="font-size:0.8rem; padding:7px 12px;">
                                Quản lý
                            </a>
                            <a href="{{ route('tournament-manage.tournaments.edit', $tournament) }}"
                               class="td-btn td-btn-ghost" style="font-size:0.8rem; padding:7px 12px;">
                                Chỉnh sửa
                            </a>
                            <form method="POST"
                                  action="{{ route('tournament-manage.tournaments.destroy', $tournament) }}"
                                  onsubmit="return confirm('Xác nhận xóa giải đấu này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="td-btn td-btn-danger"
                                        style="font-size:0.8rem; padding:7px 12px;">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div id="td-empty-state" style="display:none;">
                <div class="td-empty">
                    <div class="td-empty-icon">&#127942;</div>
                    <div class="td-empty-title">Không tìm thấy giải đấu</div>
                    <div class="td-empty-text">Thử thay đổi từ khóa tìm kiếm</div>
                </div>
            </div>

            <div style="margin-top: 20px;">{{ $tournaments->links() }}</div>
        @else
            <div class="td-card">
                <div class="td-empty">
                    <div class="td-empty-icon">&#127942;</div>
                    <div class="td-empty-title">Chưa có giải đấu nào</div>
                    <div class="td-empty-text">
                        <a href="{{ route('tournament-manage.tournaments.create') }}"
                           style="color: var(--primary-color); font-weight: 600;">
                            Tạo giải đấu mới
                        </a> để bắt đầu
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/tournament-dashboard.js') }}"></script>
@endsection
