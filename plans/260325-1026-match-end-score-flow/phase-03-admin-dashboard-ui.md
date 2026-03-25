# Phase 3: Admin Dashboard UI Changes

**Status**: Complete

## Overview
Update admin dashboard to support new end-match flow (score first, then complete) and show pending confirmation matches.

## Files to Modify
- `resources/views/home-yard/clubs/activity-dashboard.blade.php`
- `public/assets/js/club-activity-dashboard.js`

## Changes

### 1. `endMatch()` in JS (club-activity-dashboard.js)
**Current**: `confirm('Ket thuc tran?')` -> POST end-match -> poll
**New**: Show choice dialog: "Nhap diem" or "Ket thuc khong diem"

```js
async endMatch(matchId) {
    var choice = await this._showEndMatchDialog();
    if (choice === 'cancel') return;

    if (choice === 'skip_score') {
        // End without score
        var url = config.triggerUrl.replace('trigger-match', 'end-match/' + matchId);
        await fetch(url, {
            method: 'POST',
            headers: this._headers(),
            body: JSON.stringify({ skip_score: true }),
        });
        await this._poll();
        return;
    }

    // Navigate to score form
    var scoreUrl = config.baseUrl + '/matches/' + matchId + '/score';
    window.location.href = scoreUrl;
},

_showEndMatchDialog() {
    // Simple approach: use native confirm/prompt chain
    // Or: toggle Alpine modal
    return new Promise(function(resolve) {
        // Will be implemented as Alpine modal in blade template
        // For now, simplified:
        if (confirm('Nhap diem cho tran nay?\n\nOK = Nhap diem\nCancel = Ket thuc khong diem')) {
            resolve('score');
        } else {
            if (confirm('Ket thuc tran khong nhap diem?')) {
                resolve('skip_score');
            } else {
                resolve('cancel');
            }
        }
    });
},
```

**Better approach — Alpine modal in blade**:

Add to dashboard blade (inside ca-dashboard div):

```html
{{-- End match dialog --}}
<div class="ca-modal-overlay" x-show="endMatchDialog.show" x-transition @click.self="endMatchDialog.show = false">
    <div class="ca-modal-card">
        <h3>Ket thuc tran dau</h3>
        <p>San <span x-text="endMatchDialog.court"></span> - Tran #<span x-text="endMatchDialog.matchNumber"></span></p>
        <div class="ca-modal-actions">
            <button class="ca-btn ca-btn-primary" @click="goToScore(endMatchDialog.matchId)">Nhap diem</button>
            <button class="ca-btn ca-btn-secondary" @click="skipScore(endMatchDialog.matchId)">Ket thuc khong diem</button>
            <button class="ca-btn ca-btn-ghost" @click="endMatchDialog.show = false">Huy</button>
        </div>
    </div>
</div>
```

JS state:
```js
endMatchDialog: { show: false, matchId: null, court: null, matchNumber: null },

endMatch(matchId, court, matchNumber) {
    this.endMatchDialog = { show: true, matchId: matchId, court: court, matchNumber: matchNumber };
},

async goToScore(matchId) {
    this.endMatchDialog.show = false;
    window.location.href = config.baseUrl + '/matches/' + matchId + '/score';
},

async skipScore(matchId) {
    this.endMatchDialog.show = false;
    var url = config.triggerUrl.replace('trigger-match', 'end-match/' + matchId);
    await fetch(url, {
        method: 'POST',
        headers: this._headers(),
        body: JSON.stringify({ skip_score: true }),
    });
    await this._poll();
},
```

### 2. Dashboard blade — update "End" button
**Current** (line 78): `@click="endMatch(court.match.id)"`
**New**: `@click="endMatch(court.match.id, court.court, court.match.match_number)"`

### 3. Dashboard blade — remove separate "Nhap diem" link
**Current** (line 77): Separate "Nhap diem" link + "Ket thuc" button
**New**: Single "Ket thuc" button that opens dialog (score form is one of the options)

### 4. Add pending confirmation section
After court grid, before queue panel:

```html
{{-- Pending score confirmations --}}
<template x-if="pendingScores.length > 0">
    <div class="ca-dash-pending">
        <h4 class="ca-dash-section-title">Cho xac nhan diem (<span x-text="pendingScores.length"></span>)</h4>
        <template x-for="pm in pendingScores" :key="pm.id">
            <div class="ca-pending-card">
                <div class="ca-pending-teams">
                    <span x-text="(pm.player1?.name || '') + (pm.player2 ? ' & ' + pm.player2.name : '')"></span>
                    vs
                    <span x-text="(pm.player3?.name || '') + (pm.player4 ? ' & ' + pm.player4.name : '')"></span>
                </div>
                <div class="ca-pending-scores" x-text="pm.team1_score + ' - ' + pm.team2_score"></div>
                <div class="ca-pending-submitter">Nguoi nhap: <span x-text="pm.submitted_by?.name || ''"></span></div>
                <button class="ca-btn-sm ca-btn-primary" @click="adminConfirmScore(pm.id)">Admin xac nhan</button>
            </div>
        </template>
    </div>
</template>
```

JS:
```js
pendingScores: [],

// In _poll(): add
this.pendingScores = data.pending_scores || [];

async adminConfirmScore(matchId) {
    var url = config.baseUrl + '/matches/' + matchId + '/confirm-score';
    await fetch(url, {
        method: 'POST',
        headers: this._headers(),
        body: JSON.stringify({ action: 'confirm' }),
    });
    await this._poll();
},
```

### 5. Pass `baseUrl` to config
In blade: add `baseUrl: '{{ url('clubs/' . $club->slug . '/activities/' . $activity->id) }}'`

### 6. CSS additions
Add to `public/assets/css/club-activity-dashboard.css`:
- `.ca-modal-overlay` — full-screen backdrop
- `.ca-modal-card` — centered card
- `.ca-modal-actions` — button row
- `.ca-dash-pending` — pending confirmation section
- `.ca-pending-card` — individual pending match card

## Success Criteria
- "Ket thuc" button opens dialog with 2 options (score / skip)
- "Nhap diem" redirects to score form
- "Ket thuc khong diem" completes match immediately after confirmation
- Pending confirmation matches visible in dashboard
- Admin can force-confirm pending scores
