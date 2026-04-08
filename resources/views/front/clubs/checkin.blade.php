<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-in - {{ $activity->title }}</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/club-activity-checkin.css') }}">
</head>
<body>
<div class="ca-checkin-page"
     x-data="caCheckin({
        lookupUrl: '{{ route('club.activity.lookup', [$club->slug, $activity->id]) }}?token={{ $activity->qr_code }}',
        confirmUrl: '{{ route('club.activity.confirm', [$club->slug, $activity->id]) }}?token={{ $activity->qr_code }}',
        registerUrl: '{{ route('club.activity.register', [$club->slug, $activity->id]) }}?token={{ $activity->qr_code }}',
     })">

    {{-- Header --}}
    <div class="ca-checkin-header">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="ca-checkin-logo">
        <h1 class="ca-checkin-title">{{ $activity->title }}</h1>
        <p class="ca-checkin-club">{{ $club->name }}</p>
        @if($activity->location)
            <p class="ca-checkin-location">{{ $activity->location }}</p>
        @endif
        @if($activity->hasFee())
            <p style="color:#d97706;font-weight:600;margin-top:8px;">Phí tham gia: {{ $activity->fee_gems }} Gems</p>
        @endif
    </div>

    {{-- Step 1: Phone input --}}
    <div class="ca-step" x-show="step === 1" x-transition.opacity>
        <div class="ca-card">
            <label class="ca-label" for="phone">Nhập số điện thoại để check-in</label>
            <input type="tel"
                   id="phone"
                   class="ca-phone-input"
                   x-model="phone"
                   @input="formatPhone()"
                   @keydown.enter="lookup()"
                   placeholder="0912 345 678"
                   inputmode="tel"
                   autocomplete="tel">
            <p class="ca-error-msg" x-show="error" x-text="error" role="alert" aria-live="polite"></p>
        </div>
    </div>

    {{-- Step 2: User found, confirm --}}
    <div class="ca-step" x-show="step === 2" x-transition.opacity>
        <div class="ca-card ca-user-card" x-show="user">
            <div class="ca-user-info">
                <div class="ca-user-avatar">
                    <template x-if="user && user.avatar">
                        <img :src="user.avatar" :alt="user.name">
                    </template>
                    <template x-if="user && !user.avatar">
                        <span class="ca-avatar-placeholder" x-text="user ? user.name.charAt(0).toUpperCase() : ''"></span>
                    </template>
                </div>
                <div>
                    <p class="ca-user-name" x-text="user ? user.name : ''"></p>
                    <p class="ca-user-level" x-text="user && user.oprs_level ? 'OPRS: ' + user.oprs_level : ''"></p>
                </div>
            </div>
            <p class="ca-confirm-text">Xác nhận check-in với tài khoản này?</p>
        </div>
        <button class="ca-btn-back" @click="step = 1; error = ''">Quay lại</button>
    </div>

    {{-- Step 3: Register new user --}}
    <div class="ca-step" x-show="step === 3" x-transition.opacity>
        <div class="ca-card">
            <p class="ca-register-info">Không tìm thấy tài khoản. Vui lòng nhập tên để đăng ký nhanh.</p>
            <div class="ca-form-group">
                <label class="ca-label" for="reg-name">Họ và tên</label>
                <input type="text" id="reg-name" class="ca-input" x-model="form.name" placeholder="Nguyen Van A">
            </div>
            <div class="ca-form-group">
                <label class="ca-label">Giới tính</label>
                <div class="ca-gender-group">
                    <button type="button" class="ca-gender-btn" :class="{ 'is-active': form.gender === 'male' }" @click="form.gender = 'male'">Nam</button>
                    <button type="button" class="ca-gender-btn" :class="{ 'is-active': form.gender === 'female' }" @click="form.gender = 'female'">Nữ</button>
                </div>
            </div>
            <p class="ca-error-msg" x-show="error" x-text="error" role="alert"></p>
        </div>
        <button class="ca-btn-back" @click="step = 1; error = ''">Quay lại</button>
    </div>

    {{-- Fixed bottom CTA --}}
    <div class="ca-cta-fixed">
        <template x-if="step === 1">
            <button class="ca-btn-primary" @click="lookup()" :disabled="loading || phone.replace(/\s/g,'').length < 10">
                <span x-show="!loading">Tìm kiếm</span>
                <span x-show="loading">Đang tìm...</span>
            </button>
        </template>
        <template x-if="step === 2">
            <button class="ca-btn-primary ca-btn-confirm" @click="confirm()" :disabled="loading">
                <span x-show="!loading">Check-in ngay</span>
                <span x-show="loading">Đang xử lý...</span>
            </button>
        </template>
        <template x-if="step === 3">
            <button class="ca-btn-primary" @click="register()" :disabled="loading || !form.name.trim()">
                <span x-show="!loading">Đăng ký & Check-in</span>
                <span x-show="loading">Đang xử lý...</span>
            </button>
        </template>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/club-activity-checkin.js') }}"></script>
</body>
</html>
