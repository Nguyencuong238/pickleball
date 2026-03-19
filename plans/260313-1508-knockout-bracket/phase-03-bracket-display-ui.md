# Phase 3: Bracket Display UI

## Context Links
- [UX Research Report](/Users/thaopv/Desktop/php/pickleball/plans/reports/researcher-260313-1505-knockout-bracket-ux-research.md)
- [Dashboard layout](/Users/thaopv/Desktop/php/pickleball/resources/views/home-yard/tournaments/dashboard.blade.php)
- [Matches partial](/Users/thaopv/Desktop/php/pickleball/resources/views/home-yard/tournaments/partials/_matches.blade.php) (Alpine.js pattern reference)
- [Existing CSS dir](/Users/thaopv/Desktop/php/pickleball/public/assets/css/tournament-dashboard/)

## Overview
- **Priority**: P1
- **Status**: completed
- **Description**: Blade view + CSS + Alpine.js for bracket visualization. Desktop: horizontal scroll all rounds. Mobile: single round per screen with prev/next navigation.

## Requirements
- R1: Horizontal bracket tree layout with flexbox columns (one per round)
- R2: CSS pseudo-element connectors between matches across rounds
- R3: Mobile responsive: single-round view with navigation buttons
- R4: Category tabs (reuse existing pattern from _matches.blade.php)
- R5: Match cards show: athlete names, seed number, score (if completed), winner highlight
- R6: "Generate Bracket" button when no bracket exists for selected category
- R7: TBD placeholder for unassigned slots
- R8: Bye matches shown with lighter styling

## Architecture

### File Structure
```
resources/views/home-yard/tournaments/
    bracket.blade.php              -- main page extending dashboard
    partials/_bracket-tree.blade.php   -- bracket flexbox layout
    partials/_bracket-match.blade.php  -- single match card component

public/assets/css/tournament-dashboard/
    bracket-tree.css               -- bracket layout + connectors

public/assets/js/
    bracket-manager.js             -- Alpine.js component
```

### CSS Approach
- `.bracket-container` -- horizontal flex, overflow-x auto
- `.bracket-round` -- vertical flex column, justify-content space-around
- `.bracket-match` -- match card with border, position relative
- `::after` pseudo-elements for horizontal connector lines
- `::before` pseudo-elements for vertical connector lines between match pairs
- Breakpoint `768px`: switch to single-round mobile view

### Alpine.js Data Flow
```
bracket.blade.php loads with $tournament, $categories
  → Alpine init: fetch bracket data for first category
  → Render rounds + matches from JSON
  → Mobile: track currentRound, show one round at a time
```

## Related Code Files

### Files to Create
- `resources/views/home-yard/tournaments/bracket.blade.php` (~60 LOC)
- `resources/views/home-yard/tournaments/partials/_bracket-tree.blade.php` (~80 LOC)
- `resources/views/home-yard/tournaments/partials/_bracket-match.blade.php` (~40 LOC)
- `public/assets/css/tournament-dashboard/bracket-tree.css` (~150 LOC)
- `public/assets/js/bracket-manager.js` (~120 LOC)

### Files to Modify
- `resources/views/home-yard/tournaments/dashboard.blade.php` -- add bracket CSS in @section('css')

## Implementation Steps

### Step 1: bracket.blade.php
```blade
@extends('home-yard.tournaments.dashboard')

@section('css')
@parent
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/bracket-tree.css') }}">
@endsection

@section('tournament-content')
    @include('home-yard.tournaments.partials._bracket-tree')
@endsection

@section('js')
@parent
<script src="{{ asset('assets/js/bracket-manager.js') }}"></script>
@endsection
```

### Step 2: _bracket-tree.blade.php (Alpine.js container)
```blade
<div x-data="bracketManager({
    tournamentSlug: '{{ $tournament->slug }}',
    dataUrl: '{{ route('tournament-manage.bracket.data', $tournament) }}',
    generateUrl: '{{ route('tournament-manage.bracket.generate', $tournament) }}',
    categories: @json($tournament->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->category_name])),
    csrf: document.querySelector('meta[name=csrf-token]').content,
})" x-init="init()">

    {{-- Header --}}
    <div class="td-card" style="margin-bottom:0;border-radius:10px 10px 0 0;">
        <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0;">Bracket</h2>
        <p style="font-size:0.82rem;color:#64748b;margin:4px 0 0;">
            Nhanh loai truc tiep theo noi dung
        </p>
    </div>

    {{-- Category tabs --}}
    <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;">
        <div class="match-category-tabs">
            <template x-for="cat in categories" :key="cat.id">
                <button class="match-category-tab"
                        :class="{ active: activeCategoryId === cat.id }"
                        @click="selectCategory(cat.id)"
                        x-text="cat.name"></button>
            </template>
        </div>
    </div>

    {{-- Generate button (when no bracket) --}}
    <template x-if="activeCategoryId && !hasBracket && !loading">
        <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;text-align:center;padding:40px;">
            <p style="color:#64748b;margin-bottom:16px;">Chua co bracket cho noi dung nay</p>
            <div style="margin-bottom:12px;">
                <label style="font-size:0.85rem;color:#475569;">
                    <input type="checkbox" x-model="enableThirdPlace">
                    Tran tranh hang ba
                </label>
            </div>
            <button class="td-btn td-btn-primary" @click="generateBracket()" :disabled="generating">
                <span x-show="!generating">Tao Bracket</span>
                <span x-show="generating">Dang tao...</span>
            </button>
        </div>
    </template>

    {{-- Loading --}}
    <template x-if="loading">
        <div class="td-card" style="border-radius:0;text-align:center;padding:40px;">
            <p style="color:#94a3b8;">Dang tai...</p>
        </div>
    </template>

    {{-- Bracket display --}}
    <template x-if="hasBracket && !loading">
        <div class="td-card" style="border-radius:0 0 10px 10px;border-top:1px solid #f1f5f9;padding:0;">

            {{-- Mobile round navigation --}}
            <div class="bracket-mobile-nav">
                <button @click="prevRound()" :disabled="currentRoundIdx === 0"
                        class="td-btn td-btn-outline td-btn-sm">Truoc</button>
                <span class="bracket-round-label" x-text="currentRoundName"></span>
                <button @click="nextRound()" :disabled="currentRoundIdx >= rounds.length - 1"
                        class="td-btn td-btn-outline td-btn-sm">Sau</button>
            </div>

            {{-- Desktop: horizontal scroll bracket --}}
            <div class="bracket-container">
                <template x-for="(round, rIdx) in mainRounds" :key="round.id">
                    <div class="bracket-round"
                         :class="{ 'bracket-round-active': currentRoundIdx === rIdx }"
                         :data-round="rIdx">
                        <div class="bracket-round-header" x-text="round.round_name"></div>
                        <div class="bracket-round-matches">
                            <template x-for="match in round.matches" :key="match.id">
                                @include('home-yard.tournaments.partials._bracket-match')
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Third place match (separate row) --}}
            <template x-if="thirdPlaceMatch">
                <div style="padding:16px;border-top:1px solid #e2e8f0;">
                    <div style="font-size:0.82rem;font-weight:600;color:#64748b;margin-bottom:8px;">
                        Tranh hang ba
                    </div>
                    <div x-data="{ match: thirdPlaceMatch }">
                        @include('home-yard.tournaments.partials._bracket-match')
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
```

### Step 3: _bracket-match.blade.php
```blade
<div class="bracket-match"
     :class="{
         'bracket-match--completed': match.status === 'completed',
         'bracket-match--bye': match.status === 'bye',
         'bracket-match--live': match.status === 'in_progress',
     }"
     :data-match-id="match.id">

    {{-- Athlete 1 --}}
    <div class="bracket-slot"
         :class="{ 'bracket-slot--winner': match.winner_id && match.winner_id === match.athlete1_id }">
        <span class="bracket-slot-name" x-text="match.athlete1_name"></span>
        <span class="bracket-slot-score"
              x-show="match.status === 'completed' || match.status === 'in_progress'"
              x-text="match.set_scores ? match.set_scores.filter(s => s.athlete1_score > s.athlete2_score).length : ''">
        </span>
    </div>

    {{-- Athlete 2 --}}
    <div class="bracket-slot"
         :class="{ 'bracket-slot--winner': match.winner_id && match.winner_id === match.athlete2_id }">
        <span class="bracket-slot-name" x-text="match.athlete2_name"></span>
        <span class="bracket-slot-score"
              x-show="match.status === 'completed' || match.status === 'in_progress'"
              x-text="match.set_scores ? match.set_scores.filter(s => s.athlete2_score > s.athlete1_score).length : ''">
        </span>
    </div>

    {{-- Status badge --}}
    <div class="bracket-match-status" x-show="match.status === 'in_progress'">LIVE</div>
</div>
```

### Step 4: bracket-tree.css (~150 LOC)
Key styles:
- `.bracket-container`: `display:flex; gap:48px; overflow-x:auto; padding:24px;`
- `.bracket-round`: `display:flex; flex-direction:column; justify-content:space-around; flex-shrink:0; min-width:180px;`
- `.bracket-match`: `border:1px solid #e2e8f0; border-radius:6px; background:#fff; position:relative; margin:8px 0;`
- `.bracket-slot`: `padding:8px 12px; font-size:0.85rem; border-bottom:1px solid #f1f5f9;`
- `.bracket-slot--winner`: `background:#e0f2fe; font-weight:700; color:#0369a1;`
- Connector pseudo-elements: `::after` on `.bracket-match` draws horizontal line right; vertical connectors via round-level `::before`
- `.bracket-mobile-nav`: `display:none;` on desktop, `display:flex; justify-content:space-between; padding:12px;` on mobile
- `@media (max-width: 768px)`: hide all rounds except `.bracket-round-active`, show mobile nav
- `.bracket-match--bye`: `opacity:0.5;`

### Step 5: bracket-manager.js (~120 LOC)
```javascript
function bracketManager(config) {
    return {
        categories: config.categories,
        activeCategoryId: null,
        rounds: [],
        loading: false,
        generating: false,
        enableThirdPlace: false,
        currentRoundIdx: 0,

        get hasBracket() { return this.rounds.length > 0; },
        get mainRounds() { return this.rounds.filter(r => r.round_type !== 'bronze'); },
        get thirdPlaceMatch() {
            const bronze = this.rounds.find(r => r.round_type === 'bronze');
            return bronze?.matches?.[0] ?? null;
        },
        get currentRoundName() {
            return this.mainRounds[this.currentRoundIdx]?.round_name ?? '';
        },

        init() {
            if (this.categories.length > 0) {
                this.selectCategory(this.categories[0].id);
            }
        },

        selectCategory(id) {
            this.activeCategoryId = id;
            this.currentRoundIdx = 0;
            this.fetchBracket();
        },

        async fetchBracket() {
            this.loading = true;
            try {
                const res = await fetch(
                    `${config.dataUrl}?category_id=${this.activeCategoryId}`
                );
                const json = await res.json();
                this.rounds = json.success ? json.bracket : [];
            } catch (e) {
                console.error('Fetch bracket failed', e);
                this.rounds = [];
            } finally {
                this.loading = false;
            }
        },

        async generateBracket() {
            this.generating = true;
            try {
                const res = await fetch(config.generateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf,
                    },
                    body: JSON.stringify({
                        category_id: this.activeCategoryId,
                        enable_third_place: this.enableThirdPlace,
                    }),
                });
                const json = await res.json();
                if (json.success) {
                    await this.fetchBracket();
                } else {
                    alert(json.message || 'Tao bracket that bai');
                }
            } catch (e) {
                alert('Loi ket noi');
            } finally {
                this.generating = false;
            }
        },

        prevRound() { if (this.currentRoundIdx > 0) this.currentRoundIdx--; },
        nextRound() { if (this.currentRoundIdx < this.mainRounds.length - 1) this.currentRoundIdx++; },
    };
}
```

## Todo List
- [ ] Create `bracket.blade.php` extending dashboard layout
- [ ] Create `partials/_bracket-tree.blade.php` with Alpine.js container
- [ ] Create `partials/_bracket-match.blade.php` match card component
- [ ] Create `bracket-tree.css` with flexbox layout + pseudo-element connectors
- [ ] Create `bracket-manager.js` Alpine.js component
- [ ] Add bracket CSS link to dashboard.blade.php @section('css')
- [ ] Test desktop horizontal scroll layout
- [ ] Test mobile single-round navigation

## Success Criteria
1. Bracket page loads at `/tournament-manage/{slug}/bracket`
2. Category tabs switch between categories
3. "Generate Bracket" button appears when no bracket exists
4. Desktop: all rounds visible, horizontal scroll, connector lines between matches
5. Mobile (<768px): single round visible, prev/next buttons work
6. Match cards show athlete names, score, winner highlight
7. Bye matches shown with reduced opacity
8. Third-place match shown separately below main bracket

## Risk Assessment
| Risk | Impact | Mitigation |
|------|--------|------------|
| CSS connectors misalign with varying match counts | Medium | Use `justify-content: space-around` on round columns; test with 4/8/16 brackets |
| Alpine.js not loaded | Low | Already loaded in dashboard.blade.php via CDN |
| Mobile nav conflicts with bottom tabs | Low | bracket-mobile-nav placed inside content area, not fixed position |
