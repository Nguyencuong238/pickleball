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

        @if($closed)
            <div style="background: white; border-radius: 12px; padding: 40px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
                <i class="fas fa-lock" style="font-size: 3rem; color: #d1d5db; margin-bottom: 15px;"></i>
                <h3 style="color: #6b7280; margin: 0 0 10px;">Đăng ký đã đóng</h3>
                <p style="color: #9ca3af; margin: 0;">Giải đấu hiện không nhận đăng ký mới.</p>
            </div>
        @else
            @if($league->description)
                <div class="reg-card">
                    <h3 style="margin: 0 0 10px; color: #1e293b; font-size: 1rem;">Thông tin giải đấu</h3>
                    <p style="color: #6b7280; margin: 0; line-height: 1.6;">{{ $league->description }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('leagues.register.store', $league) }}" enctype="multipart/form-data" id="registrationForm">
                @csrf

                @for($i = 0; $i < $league->required_players_per_registration; $i++)
                    <div class="reg-card">
                        <div style="display: flex; align-items: center; margin-bottom: 18px;">
                            <span class="player-number">VĐV {{ $i + 1 }}</span>
                            @if($i === 0)
                                <span class="captain-badge">Đội trưởng</span>
                            @endif
                        </div>

                        <div class="reg-row">
                            <div class="reg-field">
                                <label class="reg-label">Số điện thoại *</label>
                                <input type="tel" name="players[{{ $i }}][phone]" class="reg-input" required placeholder="0901234567" value="{{ old("players.{$i}.phone") }}">
                            </div>
                            <div class="reg-field">
                                <label class="reg-label">Họ và tên *</label>
                                <input type="text" name="players[{{ $i }}][name]" class="reg-input" required placeholder="Nguyễn Văn A" value="{{ old("players.{$i}.name") }}">
                            </div>
                        </div>

                        <div class="reg-row">
                            <div class="reg-field">
                                <label class="reg-label">Giới tính *</label>
                                <select name="players[{{ $i }}][gender]" class="reg-input" required>
                                    <option value="male" {{ old("players.{$i}.gender") === 'female' ? '' : 'selected' }}>Nam</option>
                                    <option value="female" {{ old("players.{$i}.gender") === 'female' ? 'selected' : '' }}>Nữ</option>
                                </select>
                            </div>
                            <div class="reg-field">
                                <label class="reg-label">Điểm trình</label>
                                <input type="text" name="players[{{ $i }}][skill_level]" class="reg-input" placeholder="VD: 3.5, 4.0" value="{{ old("players.{$i}.skill_level") }}">
                            </div>
                        </div>

                        <div class="reg-row">
                            <div class="reg-field">
                                <label class="reg-label">Tỉnh/Thành</label>
                                <input type="text" name="players[{{ $i }}][province]" class="reg-input" placeholder="VD: TP.HCM, Hà Nội" value="{{ old("players.{$i}.province") }}">
                            </div>
                            <div class="reg-field">
                                <label class="reg-label">Ngày sinh</label>
                                <input type="date" name="players[{{ $i }}][birthday]" class="reg-input" value="{{ old("players.{$i}.birthday") }}">
                            </div>
                        </div>

                        <div class="reg-field">
                            <label class="reg-label">Ảnh VĐV</label>
                            <input type="file" name="players[{{ $i }}][photo]" class="reg-input" accept="image/*">
                        </div>

                        <div class="reg-field" style="margin-bottom: 0;">
                            <label class="reg-label">Lời nhắn</label>
                            <textarea name="players[{{ $i }}][message]" class="reg-input" rows="2" maxlength="500" placeholder="Lời nhắn cho ban tổ chức...">{{ old("players.{$i}.message") }}</textarea>
                        </div>
                    </div>
                @endfor

                <!-- Payment Proof -->
                <div class="reg-card">

                    @if($league->qr_code_image)
                        <div style="margin-bottom: 20px; text-align: center; background: #f8fafc; padding: 15px; border-radius: 8px;">
                            <p style="margin: 0 0 10px; font-weight: 600; color: #1e293b;">Quét mã QR để thanh toán:</p>
                            <img src="{{ Storage::url($league->qr_code_image) }}" alt="QR Code" style="max-height: 250px; border-radius: 8px; border: 1px solid #e2e8f0; max-width: 100%;margin: 0 auto;">
                            @if($league->registration_fee)
                                <p style="margin: 10px 0 0; font-weight: 600;">Số tiền: <span style="color: red;">{{ number_format($league->registration_fee) }}đ</span></p>
                            @endif
                        </div>
                    @endif
                    <h3 style="margin: 0 0 15px; color: #1e293b; font-size: 1rem;">
                        <i class="fas fa-receipt"></i> Ảnh chuyển khoản
                    </h3>
                    <input type="file" name="payment_proof" class="reg-input" accept="image/*" id="paymentProofInput">
                    <img id="paymentPreview" style="display:none; max-width:100%; margin-top:15px; border-radius:8px; border:1px solid #e2e8f0;">
                    <p style="color: #9ca3af; font-size: 0.8rem; margin: 10px 0 0;">Chấp nhận ảnh JPG, PNG. Tối đa 5MB.</p>
                </div>

                <button type="submit" id="submitBtn" style="width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 10px; font-size: 1.05rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s;">
                    Gửi đăng ký
                </button>
            </form>
        @endif
    </div>
</div>

<script>
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
</script>
@endsection
