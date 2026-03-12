@extends('layouts.front')

@php
$leagueImage = $league->logo ? asset('storage/' . $league->logo) : asset('assets/images/logo.png');
$leagueDescription = $league->description ? Str::limit(strip_tags($league->description), 160) : 'Đăng ký tham gia ' . $league->name . ' trên OnePickleball';
@endphp

@section('seo')
    <title>Đăng ký {{ $league->name }} - OnePickleball</title>
    <meta name="description" content="{{ $leagueDescription }}">
    <meta name="keywords" content="{{ $league->name }}, pickleball, đăng ký{{ $league->season_name ? ', ' . $league->season_name : '' }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('leagues.register', $league) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $league->name }}">
    <meta property="og:description" content="{{ $leagueDescription }}">
    <meta property="og:image" content="{{ $leagueImage }}">
    <meta property="og:url" content="{{ route('leagues.register', $league) }}">
    <meta property="og:site_name" content="OnePickleball">
    <meta property="og:locale" content="vi_VN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $league->name }}">
    <meta name="twitter:description" content="{{ $leagueDescription }}">
    <meta name="twitter:image" content="{{ $leagueImage }}">
@endsection

@section('content')
<style>
    @media (min-width: 768px) {
        .reg-header { margin-top: 80px; }
    }
    .reg-container { max-width: 700px; margin: 0 auto; padding: 30px 20px; }
    .reg-card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
    .reg-label { display: block; margin-bottom: 6px; font-weight: 600; color: #1e293b; font-size: 0.9rem; }
    .reg-input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box; transition: border-color 0.2s; }
    .reg-input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0,217,181,0.1); }
    .reg-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .reg-field { margin-bottom: 15px; }
    .player-number { background: var(--primary-color); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
    .captain-badge { background: #f59e0b; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 8px; }
    @media (max-width: 600px) { .reg-row { grid-template-columns: 1fr; } }

    /* Tab styles */
    .league-tabs { display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; gap: 0}
    .league-tab { padding: 12px 20px; font-weight: 600; font-size: 0.95rem; color: #6b7280; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; white-space: nowrap; transition: color 0.2s, border-color 0.2s; background: none; border-top: none; border-left: none; border-right: none; }
    .league-tab:hover { color: #1e293b; }
    .league-tab.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }
    .league-tab-panel { display: none; }
    .league-tab-panel.active { display: block; }
</style>

<!-- Header -->
<div class="reg-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 100px 20px; text-align: center;">
    <div style="max-width: 700px; margin: 0 auto;">
        @if($league->logo)
            <img src="{{ asset('storage/' . $league->logo) }}" alt="{{ $league->name }}" style="width: 80px; height: 80px; border-radius: 12px; object-fit: cover; margin-bottom: 15px; border: 3px solid rgba(255,255,255,0.3);">
        @endif
        <h1 style="color: white; font-size: clamp(1.5rem, 5vw, 2rem); font-weight: 700; margin: 0 0 10px;">{{ $league->name }}</h1>
        @if($league->season_name)
            <p style="color: rgba(255,255,255,0.9); margin: 0 0 15px; font-size: 1rem;">{{ $league->season_name }}</p>
        @endif
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
            @if($league->registration_fee)
                <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; color: white;">
                    <div style="font-size: 0.75rem; opacity: 0.8;">Phí đăng ký</div>
                    <div style="font-weight: 700; color: red;">{{ number_format($league->registration_fee) }}đ</div>
                </div>
            @endif
            @if($league->registration_deadline)
                <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; color: white;">
                    <div style="font-size: 0.75rem; opacity: 0.8;">Hạn đăng ký</div>
                    <div style="font-weight: 700;">{{ $league->registration_deadline->format('d/m/Y H:i') }}</div>
                </div>
            @endif
            @if($league->required_players_per_registration > 1)
                <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; color: white;">
                    <div style="font-size: 0.75rem; opacity: 0.8;">Số VĐV/nhóm</div>
                    <div style="font-weight: 700;">{{ $league->required_players_per_registration }} người</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div style="background: #f9fafb; padding: 30px 20px; min-height: 50vh;">
    <div class="reg-container">

        @if(session('success'))
            <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 20px; border-radius: 12px; margin-bottom: 20px; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                <strong style="font-size: 1.1rem;">{{ session('success') }}</strong>
            </div>
        @endif

        @if(session('error'))
            <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="league-tabs">
            <button class="league-tab active" data-tab="info" type="button">
                <i class="fas fa-info-circle"></i> Thông tin & Đăng ký
            </button>
            <button class="league-tab" data-tab="schedule" type="button">
                <i class="fas fa-calendar-alt"></i> Lịch thi đấu
            </button>
            <button class="league-tab" data-tab="standings" type="button">
                <i class="fas fa-trophy"></i> Bảng xếp hạng
            </button>
        </div>

        <!-- Tab Panels -->
        <div id="tab-info" class="league-tab-panel active">
            @include('front.leagues._tab-info-register')
        </div>

        <div id="tab-schedule" class="league-tab-panel">
            @include('front.leagues._tab-schedule')
        </div>

        <div id="tab-standings" class="league-tab-panel">
            @include('front.leagues._tab-standings')
        </div>

    </div>
</div>

<script>
// Tab switching with hash persistence
(function() {
    var tabs = document.querySelectorAll('.league-tab');
    var panels = document.querySelectorAll('.league-tab-panel');

    function activateTab(tabName) {
        tabs.forEach(function(t) {
            t.classList.toggle('active', t.dataset.tab === tabName);
        });
        panels.forEach(function(p) {
            p.classList.toggle('active', p.id === 'tab-' + tabName);
        });
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var name = this.dataset.tab;
            activateTab(name);
            history.replaceState(null, '', '#' + name);
        });
    });

    // Read hash on load
    var hash = window.location.hash.replace('#', '');
    if (['info', 'schedule', 'standings'].indexOf(hash) !== -1) {
        activateTab(hash);
    }
})();

// Payment proof preview
document.getElementById('paymentProofInput')?.addEventListener('change', function() {
    var preview = document.getElementById('paymentPreview');
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});

// Prevent double submit
document.getElementById('registrationForm')?.addEventListener('submit', function() {
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Đang gửi...';
    btn.style.opacity = '0.6';
});

// MLP sub-game toggle (public view)
function toggleMlpPublicDetails(matchId) {
    var el = document.getElementById('mlp-pub-details-' + matchId);
    var icon = document.getElementById('mlp-pub-icon-' + matchId);
    if (!el || !icon) return;
    if (el.style.display === 'none') {
        el.style.display = 'block';
        icon.innerHTML = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,8 6,4 10,8"/></svg>';
    } else {
        el.style.display = 'none';
        icon.innerHTML = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,4 6,8 10,4"/></svg>';
    }
}
</script>
@endsection
