# Phase 2: Group Stage + Standings UI

## Overview
- **Priority**: P1
- **Status**: Completed
- **Effort**: 2h

Render group stage matches and standings tables in the schedule tab, matching screenshot style.

## Key Insights
- Screenshot shows: 4-column grid of groups, each group has match cards + standings table below
- Match card shows: time, match code, athlete1 name, athlete1 level, athlete2 name, athlete2 level, scores (green/red)
- Standings table below each group: rank, team/athlete name, W, L columns
- Summary section: overall standings with W/L across all groups
- Light theme (white bg, dark borders) - fits current front-end style

## Requirements
### Functional
- Category selector tabs (if multiple categories)
- Group cards in responsive grid (4 cols desktop, 2 tablet, 1 mobile)
- Each group card: header with group name, match list, standings table
- Match row: time, match code (V1-B1-#1), athlete names, skill level badge, scores
- Standings table: #, Athlete, W (won), L (lost) - simplified from admin (no sets columns)
- Summary table after all groups

### Non-functional
- Server-side rendered, no JS
- CSS scoped with `.front-schedule-` prefix to avoid conflicts
- Responsive design

## Related Code Files
- **Modify**: `resources/views/front/tournaments/tabs-section.blade.php` (schedule tab, lines 658-685)
- **Reference**: `resources/views/home-yard/tournaments/partials/_rankings-group-table.blade.php` (admin standings for reference)

## Architecture

```
Schedule Tab
├── Category Tabs (if multiple categories)
├── Per Category:
│   ├── Group Grid (4 cols)
│   │   └── Group Card
│   │       ├── Group Header (Bảng 1, Bảng 2...)
│   │       ├── Match List
│   │       │   └── Match Row (time, code, athletes, scores)
│   │       └── Standings Table (rank, name, W, L)
│   └── Summary Table (overall standings)
```

## Implementation Steps

1. Replace schedule tab content (lines 658-685) with new structure
2. Add category tabs at top of schedule section:
   ```blade
   @foreach($tournament->categories as $cat)
       <button class="front-schedule-cat-tab" ...>{{ $cat->category_name }}</button>
   @endforeach
   ```
   Use simple JS toggle (show/hide divs by category), or render all and use CSS display toggle

3. For each category, render groups in grid:
   ```blade
   @foreach($cat->groups as $group)
       <div class="front-schedule-group-card">
           <div class="front-schedule-group-header">{{ $group->group_name }}</div>
           {{-- Match rows --}}
           @foreach($group->matches as $match)
               <div class="front-schedule-match-row">
                   <span class="match-time">{{ $match->match_time ?? '08:00' }}</span>
                   <span class="match-code">V1-{{ $group->group_code }}-#{{ $match->match_number }}</span>
                   <span class="athlete1">{{ $match->athlete1_name }}</span>
                   <span class="athlete2">{{ $match->athlete2_name }}</span>
                   <span class="score score-won">{{ $match->athlete1_score ?? 0 }}</span>
                   <span class="score score-lost">{{ $match->athlete2_score ?? 0 }}</span>
               </div>
           @endforeach
           {{-- Standings --}}
           <div class="front-schedule-standings">
               @foreach($group->standings as $standing)
                   <div class="standing-row">
                       <span class="rank">{{ $standing->rank_position }}</span>
                       <span class="name">{{ $standing->athlete?->athlete_name }}</span>
                       <span class="won">{{ $standing->matches_won }}</span>
                       <span class="lost">{{ $standing->matches_lost }}</span>
                   </div>
               @endforeach
           </div>
       </div>
   @endforeach
   ```

4. Add CSS styles with `.front-schedule-` prefix:
   - Group grid: `display: grid; grid-template-columns: repeat(4, 1fr);`
   - Group card: dark header (matching screenshot dark blue-gray), white body
   - Match row: compact layout with time, names, scores
   - Score colors: green for winner, red for loser (as in screenshot)
   - Standings: compact table with alternating rows

5. Summary table: aggregate standings across all groups for the category

## Todo List
- [ ] Add category tabs with JS toggle
- [ ] Render group cards in grid layout
- [ ] Render match rows in each group
- [ ] Render standings table per group
- [ ] Add summary/overall standings section
- [ ] Style with CSS (dark header, score colors, grid)
- [ ] Handle empty states (no groups, no matches)

## Success Criteria
- Groups display in 4-column grid like screenshot
- Match rows show time, code, athletes, scores with correct colors
- Standings show rank, name, W, L
- Falls back to text schedule if no groups exist

## Risk Assessment
- **Athlete names too long**: Use `text-overflow: ellipsis`
- **Many groups**: Grid wraps naturally, scrollable on mobile
- **Missing data**: Null checks for athlete names, scores default to 0
