# Phase 3: Knockout Bracket UI

## Overview
- **Priority**: P1
- **Status**: Completed
- **Effort**: 2h

Render knockout bracket tree with connector lines, matching screenshot style.

## Key Insights
- Screenshot shows horizontal bracket: Round of 16 -> Quarterfinals -> Semifinals -> Final
- Each match card: match code (V2-B1), time, athlete1 (from group ref), score, athlete2, score
- Connector lines: horizontal lines from match to next round, vertical lines connecting pair of matches
- Light/white background with subtle borders
- Reference: admin `bracket-tree.css` has connector logic (::after pseudo-elements)

## Requirements
### Functional
- Bracket rendered per category (below group stage section)
- Section header: "Vong dau loai truc tiep"
- Round headers: "Vong 1/16 (completed/total)", "Tu ket (0/4)", "Ban ket (0/2)", "Chung ket"
- Match cards: match code, time, athlete1 name + score, athlete2 name + score
- Winner highlight (bold or colored background)
- Connector lines between rounds

### Non-functional
- Pure CSS connectors (no SVG, no JS)
- Horizontal scroll on overflow
- Mobile: stack rounds vertically or show one at a time

## Related Code Files
- **Modify**: `resources/views/front/tournaments/tabs-section.blade.php`
- **Reference**: `public/assets/css/tournament-dashboard/bracket-tree.css` (admin bracket CSS)
- **Data**: `$bracketRounds` (from Phase 1) - collection grouped by category_id

## Architecture

```
Knockout Section
├── Section Header ("Vong dau loai truc tiep")
├── Round Headers Row
│   └── Round Name + (completed/total)
├── Bracket Container (horizontal flex)
│   ├── Round Column 1 (e.g., Round of 16)
│   │   └── Match Cards with connectors
│   ├── Round Column 2 (Quarterfinals)
│   │   └── Match Cards with connectors
│   ├── Round Column 3 (Semifinals)
│   │   └── Match Cards with connectors
│   └── Round Column 4 (Final)
│       └── Match Card
```

## Implementation Steps

1. After group stage section, add knockout bracket section per category:
   ```blade
   @php $catBracketRounds = $bracketRounds[$cat->id] ?? collect(); @endphp
   @if($catBracketRounds->isNotEmpty())
       <div class="front-bracket-section">
           <h3 class="front-bracket-title">Vong dau loai truc tiep</h3>
           <div class="front-bracket-container">
               @foreach($catBracketRounds as $round)
                   <div class="front-bracket-round">
                       <div class="front-bracket-round-header">
                           {{ $round->round_name }}
                           ({{ $round->completed_matches }}/{{ $round->total_matches }})
                       </div>
                       <div class="front-bracket-round-matches">
                           @foreach($round->matches as $match)
                               @include('front.tournaments.partials._front-bracket-match', ['match' => $match])
                           @endforeach
                       </div>
                   </div>
               @endforeach
           </div>
       </div>
   @endif
   ```

2. Create partial `resources/views/front/tournaments/partials/_front-bracket-match.blade.php`:
   ```blade
   <div class="front-bracket-match {{ $match->status === 'completed' ? 'completed' : '' }}">
       <div class="front-bracket-match-header">
           <span class="match-code">{{ $match->match_number ? 'V-' . $match->match_number : '' }}</span>
           <span class="match-time">{{ $match->match_time ? \Carbon\Carbon::parse($match->match_time)->format('H:i') : '' }}</span>
       </div>
       <div class="front-bracket-slot {{ $match->winner_id === $match->athlete1_id ? 'winner' : '' }}">
           <span class="name">{{ $match->athlete1_name ?: 'TBD' }}</span>
           <span class="score">{{ $match->athlete1_score ?? 0 }}</span>
       </div>
       <div class="front-bracket-slot {{ $match->winner_id === $match->athlete2_id ? 'winner' : '' }}">
           <span class="name">{{ $match->athlete2_name ?: 'TBD' }}</span>
           <span class="score">{{ $match->athlete2_score ?? 0 }}</span>
       </div>
   </div>
   ```

3. CSS for bracket (adapt from admin `bracket-tree.css`):
   - `.front-bracket-container`: `display: flex; gap: 0; overflow-x: auto;`
   - `.front-bracket-round`: `flex-shrink: 0; min-width: 200px; display: flex; flex-direction: column; justify-content: space-around;`
   - `.front-bracket-match`: border, rounded, white bg, margin for spacing
   - Connector lines: `::after` pseudo-element on match (horizontal line to right), `::before` on round for vertical connectors
   - Winner slot: bold + light blue bg
   - Light theme colors matching front-end site

4. Connector CSS pattern (simplified from admin):
   ```css
   .front-bracket-match { position: relative; }
   .front-bracket-match::after {
       content: '';
       position: absolute;
       right: -25px;
       top: 50%;
       width: 25px;
       height: 1px;
       background: #cbd5e1;
   }
   .front-bracket-round:last-child .front-bracket-match::after { display: none; }
   ```

5. Vertical connectors between paired matches require additional CSS - pairs of matches connect to single next-round match. Use border-right on wrapper divs.

## Todo List
- [ ] Add knockout section in tabs-section.blade.php per category
- [ ] Create _front-bracket-match.blade.php partial
- [ ] Write bracket container CSS (horizontal flex, overflow)
- [ ] Write match card CSS (slots, winner highlight)
- [ ] Write connector CSS (horizontal + vertical lines)
- [ ] Handle TBD matches (no athlete assigned yet)
- [ ] Handle third-place match display

## Success Criteria
- Bracket displays horizontally with rounds left to right
- Connector lines visible between rounds
- Winner highlighted in completed matches
- TBD shown for unassigned slots
- Horizontal scroll works for large brackets

## Risk Assessment
- **Connector CSS complexity**: Pure CSS connectors are tricky for variable bracket sizes. Start with horizontal lines only, add vertical if time permits.
- **Large brackets (64+ players)**: Horizontal scroll handles this, but may need bracket-position-based spacing to align pairs correctly.
- **Mobile**: Hide connectors on mobile, stack matches vertically.
