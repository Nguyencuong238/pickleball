@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-cards.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<style>
    .league-tab { padding: 12px 24px; border: none; background: none; color: #6b7280; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; font-size: 0.95rem; transition: all 0.2s; }
    .league-tab:hover { color: #1e293b; }
    .league-tab.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }
    .league-tab-content { display: none; }
    .league-tab-content.active { display: block; }
</style>
@endsection

@section('content')
<div class="main-content">
    <div class="top-header" style="flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('homeyard.leagues.index') }}" class="td-btn td-btn-ghost">
                &larr; Quay lại
            </a>
            <h2 class="page-title" style="margin: 0; word-break: break-word;">{{ $league->name }}</h2>
            @if($league->season_name)
                <span style="background-color: #f0f9ff; color: #0369a1; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">{{ $league->season_name }}</span>
            @endif
            @switch($league->status)
                @case('draft')
                    <span style="background-color: #f3f4f6; color: #4b5563; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Nháp</span>
                    @break
                @case('registration')
                    <span style="background-color: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đăng Ký</span>
                    @break
                @case('active')
                    <span style="background-color: #dcfce7; color: #15803d; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đang Diễn Ra</span>
                    @break
                @case('completed')
                    <span style="background-color: #f3e8ff; color: #7c3aed; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Hoàn Thành</span>
                    @break
                @case('cancelled')
                    <span style="background-color: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đã Hủy</span>
                    @break
                @default
                    <span style="background-color: #f3f4f6; color: #4b5563; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">{{ ucfirst($league->status) }}</span>
            @endswitch
            @if($league->start_date && $league->end_date)
                <span style="color: #6b7280; font-size: 0.85rem;">{{ $league->start_date->format('d/m/Y') }} - {{ $league->end_date->format('d/m/Y') }}</span>
            @endif
        </div>
        <a href="{{ route('homeyard.leagues.edit', $league) }}" class="td-btn td-btn-primary">
            Chỉnh Sửa
        </a>
    </div>

    <!-- Tab Navigation -->
    <div style="background: white; border-radius: 15px 15px 0 0; box-shadow: 0 2px 10px rgba(0,0,0,0.06); display: flex; overflow-x: auto; border-bottom: 2px solid #e2e8f0;">
        <button class="league-tab active" onclick="switchTab('overview', this)">Tổng Quan</button>
        <button class="league-tab" onclick="switchTab('registrations', this)">
            Vận Động Viên
            @if($pendingRegistrationCount > 0)
                <span style="background:#ef4444;color:white;border-radius:50%;padding:2px 6px;font-size:0.7rem;margin-left:4px;">{{ $pendingRegistrationCount }}</span>
            @endif
        </button>
        <button class="league-tab" onclick="switchTab('teams', this)">Đội ({{ $league->teams->count() }})</button>
        <button class="league-tab" onclick="switchTab('schedule', this)">Lịch Thi Đấu</button>
        <button class="league-tab" onclick="switchTab('standings', this)">Bảng Xếp Hạng</button>
    </div>

    <!-- Tab Contents -->
    <div style="background: white; border-radius: 0 0 15px 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: clamp(20px, 5vw, 30px);">
        <div id="tab-overview" class="league-tab-content active">
            @include('home-yard.leagues._tab-overview')
        </div>
        <div id="tab-registrations" class="league-tab-content">
            @include('home-yard.leagues._tab-registrations')
        </div>
        <div id="tab-teams" class="league-tab-content">
            @include('home-yard.leagues._tab-teams')
        </div>
        <div id="tab-schedule" class="league-tab-content">
            @include('home-yard.leagues._tab-matches')
        </div>
        <div id="tab-standings" class="league-tab-content">
            @include('home-yard.leagues._tab-standings')
        </div>
    </div>
</div>

<script>
window.storageUrl = '{{ Storage::disk(config("filesystems.default"))->url("") }}';

function switchTab(tabName, btn) {
    document.querySelectorAll('.league-tab').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.league-tab-content').forEach(function(content) { content.classList.remove('active'); });

    document.getElementById('tab-' + tabName).classList.add('active');
    btn.classList.add('active');

    if (tabName === 'registrations' && typeof loadRegistrationsIfNeeded === 'function') {
        loadRegistrationsIfNeeded();
    }

    window.location.hash = tabName;
}

document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('tab-' + hash)) {
        var tabs = document.querySelectorAll('.league-tab');
        var tabNames = ['overview', 'registrations', 'teams', 'schedule', 'standings'];
        var index = tabNames.indexOf(hash);
        if (index !== -1) {
            document.querySelectorAll('.league-tab').forEach(function(btn) { btn.classList.remove('active'); });
            document.querySelectorAll('.league-tab-content').forEach(function(content) { content.classList.remove('active'); });
            tabs[index].classList.add('active');
            document.getElementById('tab-' + hash).classList.add('active');
            if (hash === 'registrations' && typeof loadRegistrationsIfNeeded === 'function') {
                loadRegistrationsIfNeeded();
            }
        }
    }
});
</script>
@endsection
