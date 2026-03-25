# Phase 4: Player Queue + Score UI Changes

**Status**: Complete

## Overview
Add "End match" button for players and update score form to support confirm mode.

## Files to Modify
- `resources/views/front/clubs/queue.blade.php`
- `public/assets/js/club-activity-queue.js`
- `resources/views/front/clubs/score-submit.blade.php`
- `public/assets/js/club-activity-score.js`

## Queue Page Changes

### 1. Add "End match + enter score" button when player is playing
**File**: `queue.blade.php` (inside `myStatus.current_status === 'playing'` template, ~line 42-50)

**Current**:
```html
<p class="ca-hero-label">Dang thi dau</p>
<p class="ca-hero-court">San dang choi</p>
<template x-if="myStatus.current_match_id">
    <a class="ca-btn-score-link" :href="scoreUrl">Nhap diem</a>
</template>
```

**New**:
```html
<p class="ca-hero-label">Dang thi dau</p>
<p class="ca-hero-court">San dang choi</p>
<template x-if="myStatus.current_match_id">
    <button class="ca-btn-end-match" @click="playerEndMatch()" :disabled="endingMatch">
        <span x-show="!endingMatch">Ket thuc & Nhap diem</span>
        <span x-show="endingMatch">Dang xu ly...</span>
    </button>
</template>
```

### 2. Add pending confirmation state in hero card
After the `playing` template, add new template:

```html
<template x-if="myStatus && myStatus.pending_score_match_id">
    <div class="ca-hero-content ca-hero-pending">
        <p class="ca-hero-label">Cho xac nhan diem</p>
        <template x-if="myStatus.can_confirm_score">
            <div>
                <p>Doi ban can xac nhan diem so</p>
                <a class="ca-btn-confirm-link" :href="'/clubs/{{ $club->slug }}/activities/{{ $activity->id }}/matches/' + myStatus.pending_score_match_id + '/score'">Xac nhan diem</a>
            </div>
        </template>
        <template x-if="!myStatus.can_confirm_score">
            <p>Dang cho doi con lai xac nhan...</p>
        </template>
    </div>
</template>
```

### 3. JS changes (`club-activity-queue.js`)

```js
endingMatch: false,

async playerEndMatch() {
    this.endingMatch = true;
    try {
        var url = '/clubs/{{ $club->slug }}/activities/{{ $activity->id }}/player-end-match/' + this.myStatus.current_match_id;
        var res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        var data = await res.json();
        if (data.success && data.score_url) {
            window.location.href = data.score_url;
        }
    } catch (e) {}
    this.endingMatch = false;
},
```

Note: The URLs will be passed via config from blade (like `playerEndMatchUrl`) instead of inline template strings.

**Blade config update**:
```js
playerEndMatchUrlBase: '{{ url('clubs/' . $club->slug . '/activities/' . $activity->id . '/player-end-match') }}',
```

## Score Form Changes

### 1. Score form supports 2 modes: `submit` and `confirm`
**File**: `score-submit.blade.php`

Pass from controller:
```php
'mode' => $canConfirm ? 'confirm' : 'submit',
'isAdmin' => $isAdmin,
```

In blade, update Alpine config:
```js
x-data="caScoreSubmit({
    submitUrl: '{{ route('club.activity.submit-score', [...]) }}',
    confirmUrl: '{{ route('club.activity.confirm-score', [...]) }}',
    queueUrl: '{{ route('club.activity.queue', [...]) }}',
    dashboardUrl: '{{ $isAdmin ? route('club.activity.dashboard', [...]) : '' }}',
    bestOf: {{ $activity->best_of ?? 1 }},
    pointsPerSet: {{ $activity->points_per_set ?? 21 }},
    mode: '{{ $mode ?? 'submit' }}',
    isAdmin: {{ $isAdmin ? 'true' : 'false' }},
    existingScores: {{ $match->set_scores ? json_encode($match->set_scores) : 'null' }},
})"
```

### 2. Confirm mode: show existing scores (read-only) + confirm/reject buttons
**File**: `score-submit.blade.php`

After sets section, add:
```html
<template x-if="config.mode === 'confirm'">
    <div class="ca-confirm-actions">
        <p class="ca-confirm-label">Doi ban da nhap diem. Vui long xac nhan.</p>
        <button class="ca-btn-primary" @click="confirmScore('confirm')" :disabled="loading">Xac nhan diem</button>
        <button class="ca-btn-danger" @click="confirmScore('reject')" :disabled="loading">Tu choi</button>
    </div>
</template>
```

In confirm mode: pre-fill scores from `existingScores`, disable stepper buttons.

### 3. JS changes (`club-activity-score.js`)

```js
init() {
    if (config.existingScores && config.mode === 'confirm') {
        this.sets = config.existingScores;
    }
},

async confirmScore(action) {
    this.loading = true;
    this.error = '';
    try {
        var res = await fetch(config.confirmUrl, {
            method: 'POST',
            headers: { /* same headers */ },
            body: JSON.stringify({ action: action }),
        });
        var data = await res.json();
        if (data.success) {
            window.location.href = config.queueUrl;
        } else {
            this.error = data.message || 'Loi. Vui long thu lai.';
        }
    } catch (e) {
        this.error = 'Khong the ket noi.';
    } finally {
        this.loading = false;
    }
},

async submit() {
    // Existing logic, but after success:
    // Admin -> redirect to dashboard
    // Player -> redirect to queue
    if (data.success) {
        window.location.href = config.isAdmin ? (config.dashboardUrl || config.queueUrl) : config.queueUrl;
    }
},
```

### 4. CSS additions
Add to `public/assets/css/club-activity-queue.css`:
- `.ca-btn-end-match` — prominent button style
- `.ca-hero-pending` — pending state styling
- `.ca-btn-confirm-link` — confirm action button

Add to `public/assets/css/club-activity-score.css`:
- `.ca-confirm-actions` — confirm/reject button group
- `.ca-confirm-label` — informational text
- Disabled stepper styling for confirm mode

## Success Criteria
- Player sees "Ket thuc & Nhap diem" button when playing
- Clicking redirects to score form
- Opposing team player sees "Xac nhan diem" link in queue hero card
- Score form in confirm mode: shows existing scores read-only + confirm/reject
- Score form in submit mode: works as before
- Admin redirected to dashboard after scoring; player to queue
