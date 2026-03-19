# Knockout Bracket UX/UI Research Report
**Date:** 2026-03-13 | **Project:** Pickleball Tournament Management | **Stack:** Laravel + Blade + Alpine.js + Custom CSS

---

## Executive Summary

Single elimination brackets are best implemented with **pure CSS layouts (Flexbox/CSS Grid)** on Blade templates, supplemented by Alpine.js for interactive features. Real-world tournament platforms (Challonge, Start.gg, playOn) converge on these patterns:

1. **Bracket visualization:** Flexbox columns with pseudo-element connectors
2. **Mobile:** Single-round-per-screen with navigation buttons (75% of traffic is mobile)
3. **Score entry:** Inline expandable rows preferred over modals for admin workflows
4. **Bracket editing:** Drag-drop for swaps + visual feedback (highlight changed matches)
5. **Auto-advancement:** Winner flows via data binding + CSS pseudo-elements

Implementation is lean—no external JS libraries required. Blade components + Alpine.js can handle all interactive behavior.

---

## 1. Bracket Visualization

### Best Approach: CSS Flexbox + Pseudo-Elements

**Why:** Lightest weight, semantic HTML, no JS library overhead, renders fast on Blade templates.

**Structure:**

```html
<div class="bracket">
  <div class="round round-1">
    <!-- Column 1: First round matches -->
    <div class="match">
      <div class="participant">Player A</div>
      <div class="participant">Player B</div>
      <div class="score">3-1</div>
    </div>
  </div>

  <div class="round round-2">
    <!-- Column 2: Semifinals -->
    ...
  </div>
</div>
```

**CSS Pattern (Flexbox):**

```css
.bracket {
  display: flex;
  gap: 3rem;
  overflow-x: auto;
  padding: 2rem;
}

.round {
  display: flex;
  flex-direction: column;
  justify-content: space-around;
  flex-shrink: 0;
  width: 200px;
}

.match {
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 0.75rem;
  background: white;
  position: relative;
  min-height: 100px;
}

/* Winner connector line (pseudo-element) */
.match.winner::after {
  content: '';
  position: absolute;
  right: -3rem;
  top: 50%;
  height: 2px;
  width: 3rem;
  background: #0364d3;
  transform: translateY(-50%);
}

.participant {
  padding: 0.5rem 0;
  font-size: 0.9rem;
  border-bottom: 1px solid #eee;
}

.participant:last-child {
  border-bottom: none;
}

.participant.winner {
  font-weight: bold;
  color: #0364d3;
}
```

**Key Advantages:**
- No DOM manipulation required
- Responsive via flexbox gap/sizing
- Pseudo-elements handle visual connectors (no SVG or extra DOM nodes)
- Blade renders once; Alpine.js enhances interactivity
- ~80% smaller than React components

**Alternative: CSS Grid**
For more complex bracket topologies (multi-round with uneven spacing), CSS Grid + pseudo-elements offers more control over connector positioning. River.me blog demonstrates grid-based approach with detailed spacer columns.

---

## 2. Mobile Bracket UX

### Core Patterns

**Pattern 1: Single-Round-Per-Screen (Recommended for Mobile)**
- Show 1 round at a time
- Navigation buttons: "← Previous Round" | "Next Round →"
- Full-width match cards (no horizontal scroll)
- Result: 75% better engagement on mobile per CIAA case study

**Pattern 2: Horizontal Scroll (Desktop/Tablet)**
- All rounds visible in scrollable container
- Breakpoint: `md: (640px)` shows horizontal scroll; `sm: (< 640px)` shows single-round view

**Implementation (Alpine.js + Blade):**

```blade
<div x-data="bracket()" class="bracket-container">
  <!-- Mobile: Single round view -->
  <div class="hidden md:block">
    <div class="flex justify-between items-center mb-4">
      <button @click="previousRound()" :disabled="currentRound === 1">← Previous</button>
      <span>Round {{ currentRound }} of {{ totalRounds }}</span>
      <button @click="nextRound()" :disabled="currentRound === totalRounds">Next →</button>
    </div>

    <div class="space-y-3">
      <template x-for="match in currentRoundMatches" :key="match.id">
        <div class="match-card">...</div>
      </template>
    </div>
  </div>

  <!-- Desktop: Horizontal scroll -->
  <div class="md:hidden overflow-x-auto">
    <div class="flex gap-8 pb-4">
      <!-- All rounds rendered -->
    </div>
  </div>
</div>

<script>
function bracket() {
  return {
    currentRound: 1,
    totalRounds: 4,
    matches: @json($bracket), // Blade passes data

    get currentRoundMatches() {
      return this.matches.filter(m => m.round === this.currentRound);
    },

    previousRound() {
      if (this.currentRound > 1) this.currentRound--;
    },

    nextRound() {
      if (this.currentRound < this.totalRounds) this.currentRound++;
    }
  }
}
</script>
```

**Match Card Sizing:**
- Mobile: 100% width, min-height 80px, padding 12px
- Tablet: 280px card width
- Desktop: 200px card width

**Optimization for One-Handed Use:**
- Buttons 48px tall (thumb reach)
- Score input fields 44px minimum tap target
- Avoid nested modals
- Touch-friendly swipe indicators

---

## 3. Score Entry UX

### Recommendation: Inline Expandable Cards (Not Modals)

**Why:** Admin is at court between matches, holding phone in one hand, entering scores quickly. Modals disrupt context and require closing after each entry.

**Pattern:**

```blade
@forelse ($matches as $match)
  <div x-data="{ expanded: false }" class="match-entry-card">
    <!-- Collapsed state -->
    <div @click="expanded = !expanded" class="match-summary">
      <div class="flex justify-between items-center p-3">
        <span class="font-semibold">{{ $match->team_a }} vs {{ $match->team_b }}</span>
        <span class="text-sm text-gray-500">{{ $match->status }}</span>
        <span class="ml-2">⋮</span>
      </div>
    </div>

    <!-- Expanded state -->
    <div x-show="expanded" class="match-form p-3 border-t bg-gray-50">
      <form wire:submit.prevent="updateScore({{ $match->id }})">
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div>
            <label class="block text-xs font-medium mb-1">{{ $match->team_a }}</label>
            <input
              type="number"
              x-model="scoreA"
              min="0"
              max="11"
              class="w-full px-3 py-2 border rounded text-lg font-bold"
              @change="validateScore">
          </div>
          <div>
            <label class="block text-xs font-medium mb-1">{{ $match->team_b }}</label>
            <input
              type="number"
              x-model="scoreB"
              min="0"
              max="11"
              class="w-full px-3 py-2 border rounded text-lg font-bold"
              @change="validateScore">
          </div>
        </div>

        <div class="flex gap-2">
          <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded font-medium">
            Save Score
          </button>
          <button type="button" @click="expanded = false" class="flex-1 bg-gray-300 py-2 rounded">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
@empty
  <div class="text-center py-8 text-gray-500">No matches yet</div>
@endforelse
```

**UX Details:**
- Input fields: Large, 44px+ tall, number input with spinner (mobile-friendly)
- Validation: Real-time feedback ("Score must be 0-11" if invalid)
- Auto-focus first input when expanded
- Debounce saves to prevent accidental submissions
- Toast notification on success ("Score saved")

**When to Use Modal Alternative:**
- Complex match data (4+ teams, tiebreaker logic, conditional scoring)
- Confirmation required before save (score validation against rules)
- Otherwise: inline expandable keeps workflow smooth

---

## 4. Auto-Advancement Visualization

### Implementation Pattern

**Data flow:** Winner selected → match.winner_id updated → Blade re-renders bracket with winner propagated to next round.

**Blade Template:**

```blade
@foreach($bracket as $match)
  <div class="match" :class="{ 'match--winner': {{ $match->winner_id ? 'true' : 'false' }} }">
    @foreach($match->participants as $p)
      <div class="participant" :class="{ 'participant--winner': {{ $p->id === $match->winner_id ? 'true' : 'false' }} }">
        <span>{{ $p->name }}</span>
        @if($p->id === $match->winner_id)
          <span class="badge-winner">✓</span>
        @endif
      </div>
    @endforeach

    @if($match->winner_id)
      <!-- Visual indicator for advancement -->
      <div class="advancement-arrow">→</div>
    @endif
  </div>
@endforeach
```

**CSS for Visual Flow:**

```css
/* Highlight winner and draw line to next match */
.match.match--winner {
  border-color: #0364d3;
  box-shadow: 0 0 8px rgba(3, 100, 211, 0.2);
}

.participant.participant--winner {
  background: #e3f2fd;
  font-weight: bold;
  color: #0364d3;
}

/* Connecting line from winner to next round */
.match.match--winner::after {
  content: '';
  position: absolute;
  right: -40px;
  top: 50%;
  width: 40px;
  height: 2px;
  background: linear-gradient(to right, #0364d3, transparent);
  transform: translateY(-50%);
}

.advancement-arrow {
  position: absolute;
  right: -28px;
  top: 50%;
  transform: translateY(-50%);
  color: #0364d3;
  font-weight: bold;
  z-index: 1;
}
```

**Real-Time Updates (Alpine.js):**

```javascript
Alpine.data('bracket', () => ({
  matches: @json($matches),

  updateWinner(matchId, winnerId) {
    fetch(`/api/matches/${matchId}/winner`, {
      method: 'POST',
      body: JSON.stringify({ winner_id: winnerId })
    })
    .then(r => r.json())
    .then(data => {
      // Update local match
      this.matches = this.matches.map(m =>
        m.id === matchId ? { ...m, winner_id: winnerId } : m
      );

      // Alpine reactivity triggers re-render
      // Pseudo-elements + CSS handle visual connectors
    });
  }
}));
```

---

## 5. Bracket Editing (Bracket Reassignment)

### Pattern: Drag-Drop Swap + Visual Feedback

**Real-world pattern from Challonge/Start.gg:**
- Click participant to select
- Drag to swap position with another participant
- Changed matches highlight in green
- Confirmation before save

**Implementation (Alpine.js + Blade):**

```blade
<div x-data="bracketEditor()" class="bracket-editor">
  <div class="bracket-participants">
    @foreach($participants as $p)
      <div
        draggable="true"
        @dragstart="dragStart($event, {{ $p->id }})"
        @dragover.prevent="dragOver($event)"
        @drop="drop($event, {{ $p->id }})"
        class="participant-item"
        :class="{ 'participant--selected': selectedId === {{ $p->id }} }">

        <span class="drag-handle">⋮⋮</span>
        <span class="participant-name">{{ $p->name }}</span>
        <span class="participant-seed">#{{ $p->seed }}</span>
      </div>
    @endforeach
  </div>

  <!-- Changes preview -->
  <template x-if="affectedMatches.length > 0">
    <div class="changes-preview bg-yellow-50 p-3 rounded mt-4">
      <strong class="text-sm">Affected Matches (will be highlighted):</strong>
      <ul class="text-sm mt-2 list-disc pl-4">
        <template x-for="m in affectedMatches" :key="m.id">
          <li><span x-text="`${m.team_a} vs ${m.team_b}`"></span></li>
        </template>
      </ul>
      <div class="mt-3 flex gap-2">
        <button @click="commitSwap()" class="px-4 py-2 bg-green-600 text-white rounded">
          Confirm Swap
        </button>
        <button @click="cancelSwap()" class="px-4 py-2 bg-gray-300 rounded">
          Cancel
        </button>
      </div>
    </div>
  </template>
</div>

<script>
function bracketEditor() {
  return {
    participants: @json($participants),
    matches: @json($matches),
    selectedId: null,
    draggedId: null,
    affectedMatches: [],

    dragStart(e, id) {
      this.draggedId = id;
    },

    dragOver(e) {
      e.preventDefault();
    },

    drop(e, targetId) {
      e.preventDefault();

      // Calculate which matches change
      this.affectedMatches = this.calculateAffectedMatches(
        this.draggedId,
        targetId
      );
    },

    calculateAffectedMatches(fromId, toId) {
      // Returns matches that will change if swap occurs
      return this.matches.filter(m =>
        [m.team_a_id, m.team_b_id].includes(fromId) ||
        [m.team_a_id, m.team_b_id].includes(toId)
      );
    },

    commitSwap() {
      fetch(`/api/bracket/swap`, {
        method: 'POST',
        body: JSON.stringify({
          from_id: this.draggedId,
          to_id: this.selectedId
        })
      }).then(() => {
        window.location.reload(); // Or update via Alpine
      });
    },

    cancelSwap() {
      this.affectedMatches = [];
      this.draggedId = null;
    }
  }
}
</script>
```

**Visual Feedback (CSS):**

```css
.participant-item {
  padding: 0.75rem;
  border: 2px solid #ccc;
  border-radius: 4px;
  cursor: move;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: white;
  transition: all 150ms ease;
}

.participant-item:hover {
  border-color: #0364d3;
  box-shadow: 0 2px 8px rgba(3, 100, 211, 0.15);
}

.participant-item.participant--selected {
  background: #e3f2fd;
  border-color: #0364d3;
}

.participant--selected .drag-handle {
  color: #0364d3;
  font-weight: bold;
}

/* Highlight affected matches in green after swap */
.match.match--affected {
  border-color: #4caf50;
  background: #f1f8f4;
  outline: 2px solid rgba(76, 175, 80, 0.3);
}
```

**Alternative: Click-to-Swap (Simpler UX)**
If drag-drop proves difficult on mobile, implement click-to-swap:
1. Click participant → highlight
2. Click target position → swap
3. Changes highlighted in green
4. Confirm/cancel buttons

---

## 6. Real-World Reference Patterns

### Challonge
- **Participant mgmt:** Drag dots on left to reorder, or click seed number to manually assign
- **Bracket preview:** Shows live seeding changes reflected immediately
- **Mobile:** Participant list (swipeable) + separate bracket view

### Start.gg
- **Admin interface:** Settings panel with "Edit Round Settings" gear icon
- **Bracket formats:** Single elim, double elim, swiss, round-robin
- **UX:** Phase-based organization (multiple events = multiple phases)

### PlayyOn (Case Study Highlights)
- **Open registration:** System auto-creates bracket after registration closes (no pre-set team count)
- **Admin notifications:** Real-time score conflict alerts
- **Payment tracking:** Teams page shows paid/unpaid status
- **Desktop + mobile:** Responsive design, same feature parity
- **Workflow:** Create tournament → Set teams → Run bracket → Track scores

### Bracketry (JS Library - Reference Only)
- **Mobile optimization:** Single round per screen, navigation buttons
- **Config options:** `applyNewOptions()` for dynamic responsive changes
- **Pattern:** Even JS libraries emphasize single-round view on mobile

---

## 7. Implementation Checklist for Pickleball Bracket

### Phase 1: Bracket Display (Blade + CSS)
- [ ] Flexbox bracket layout with proper spacing
- [ ] Pseudo-element connectors between rounds
- [ ] Responsive breakpoints (mobile single-round / desktop scroll)
- [ ] Match card component (participant names, score display, status badge)
- [ ] Dark mode support (CSS variables for colors)

### Phase 2: Interactive Features (Alpine.js)
- [ ] Round navigation (mobile view)
- [ ] Highlight winner with auto-advance visualization
- [ ] Real-time score updates via fetch API
- [ ] Expandable match cards for score entry

### Phase 3: Admin Features (Alpine.js + Server)
- [ ] Drag-drop participant swapping
- [ ] Show affected matches on change (green highlight)
- [ ] Confirmation dialog before swap
- [ ] Bracket lock/unlock (prevent changes once matches start)

### Phase 4: Polish & Optimization
- [ ] Accessibility (ARIA labels, keyboard nav, semantic HTML)
- [ ] Performance (cache rendered bracket in browser)
- [ ] Print-friendly layout option
- [ ] Real-time sync if multiple admins editing simultaneously

---

## 8. CSS-Only Approach (No Alpine.js)

If interactivity must be minimal, Blade templates alone can render brackets:

```blade
<div class="bracket">
  @foreach($rounds as $roundNum => $matches)
    <div class="round round-{{ $roundNum }}">
      @foreach($matches as $match)
        <div class="match" data-match-id="{{ $match->id }}">
          @foreach($match->participants as $p)
            <div class="participant" :class="{ 'winner': $p->id === $match->winner_id }">
              {{ $p->name }}
              @if($p->id === $match->winner_id)
                <span class="badge">✓</span>
              @endif
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  @endforeach
</div>
```

**Limitations:** No live updates, no drag-drop editing. Admin must refresh or use separate modal form to update scores. Use Alpine.js unless score entry is truly read-only.

---

## 9. Performance Considerations

### Blade Template Rendering
- **Bracket depth (max rounds):** 16-round bracket (65,536 max participants) renders fine
- **Caching:** Cache entire bracket HTML if tournament is "locked" (no changes expected)
- **Pagination:** If 100+ matches per round, paginate or lazy-load

### DOM Efficiency
- **Pseudo-elements:** Use `::before`/`::after` for connectors (0 DOM overhead)
- **Avoid tables:** Flexbox is faster than complex `<table>` layouts
- **CSS Grid:** Only if connector positioning is critical; adds ~10% complexity

### Alpine.js Performance
- **Debounce:** Score input updates (300ms debounce prevents race conditions)
- **x-cloak:** Hide bracket until Alpine initializes (prevent flicker)
- **Event delegation:** Avoid binding Alpine to every match card; use parent listeners

---

## 10. Unresolved Questions / Edge Cases

1. **Tiebreaker Handling:** How to display tiebreak scores in 3-point games (pickleball rules)? Model as separate match or sub-field?
2. **Bye Handling:** Visual indication for "bye" matches (automatic advancement). Suggested: empty participant slot with "Bye" label + lighter styling.
3. **Seeding Visibility:** Should unseeded brackets show random ordering, or hide seed numbers entirely on mobile?
4. **Concurrent Matches:** If multiple bracket rounds play simultaneously, how to prevent "winner advancement" conflicts? Suggest match state machine: `Pending → InProgress → Completed → Winner_Advancing`
5. **Admin Undo:** Should bracket edits (score changes, swaps) have undo/redo? Consider audit log instead (simpler).
6. **Printing:** How to print multi-page brackets? Suggest landscape orientation + CSS page break rules.

---

## Sources & References

- [DEV: Accessible Tournament Brackets HTML+CSS](https://dev.to/yuridevat/can-tournament-brackets-be-accessible-34og)
- [CSS Script: Tournament Bracket Flexbox](https://www.cssscript.com/tournament-bracket-flexbox/)
- [New Media Campaigns: Mobile-First Bracket Design](https://www.newmediacampaigns.com/blog/designing-a-tournament-bracket-mobile-first-approach)
- [Bracketry: Mobile Layout Guide](https://bracketry.app/mobile/)
- [Medium: PlayyOn UX Case Study](https://medium.com/@aaronjc_26903/case-study-playyon-com-tournament-generator-7ba567a0b1ff)
- [PatternFly: Inline Edit Design](https://www.patternfly.org/components/inline-edit/design-guidelines/)
- [Challonge: Participant Management](https://kb.challonge.com/en/article/participant-management-1m6ooqe/)
- [Start.gg: Bracket Setup](https://help.start.gg/article/bracket-setup)
- [Alpine.js: Form Validation Patterns](https://alpinejs.dev/directives/model)
- [Laravel: Blade Templates Performance](https://laravel.com/docs/12.x/blade)

