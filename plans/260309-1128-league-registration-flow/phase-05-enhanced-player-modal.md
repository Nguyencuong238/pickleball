# Phase 5: Frontend - Enhanced "Them VDV" Modal

## Context Links
- [Phase 2: Backend Logic](./phase-02-backend-logic.md)
- [Existing _tab-teams.blade.php](../../resources/views/home-yard/leagues/_tab-teams.blade.php)
- [Existing player modal JS (lines 144-316)](../../resources/views/home-yard/leagues/_tab-teams.blade.php)

## Overview
- **Priority**: P1
- **Status**: pending
- **Effort**: 1h

## Key Insights
- Current modal has single tab: search user by name/email via `ocr.search-users` endpoint
- Need 2 tabs: "VDV da duyet" (approved pool) + "Tim user" (existing, unchanged)
- Pool tab fetches from `/homeyard/leagues/{slug}/registrations/pool` (Phase 2)
- Pool shows players grouped by registration group
- "Them ca nhom" button adds all players from a group to the target team
- Individual "Them" button for picking single player from pool
- Players already in any team are hidden from pool

## Requirements

### Functional
- Modal opens with 2 tabs
- Tab 1 "VDV da duyet": shows approved players grouped by registration
  - Each group shows: player names, phones, genders
  - "Them ca nhom" button per group (adds all, sets captain = first player)
  - Individual "Them" button per player
  - After adding, player/group disappears from pool
- Tab 2 "Tim user": existing search functionality (no changes)
- Toast success/error messages on add

### Non-functional
- Pool loaded on modal open (fresh data each time)
- Consistent styling with existing modal

## Architecture

### Modified View
`resources/views/home-yard/leagues/_tab-teams.blade.php` -- modify existing playerModal

### Data Flow
```
Open modal --> Tab 1 active by default (if pool not empty)
  --> fetch /homeyard/leagues/{slug}/registrations/pool
  --> render grouped player cards
  --> "Them ca nhom" --> POST /homeyard/leagues/{slug}/teams/{teamId}/add-group/{regId}
  --> "Them" individual --> POST existing addPlayer route (with user_id + gender from pool data)
  --> refresh pool list after each action
```

## Related Code Files

### Files to MODIFY
- `resources/views/home-yard/leagues/_tab-teams.blade.php` -- restructure playerModal

### Files to reference (no changes)
- `app/Http/Controllers/Front/LeagueRegistrationController.php` (pool + addGroup endpoints)
- `app/Http/Controllers/Front/LeagueTeamController.php` (existing addPlayer)

## Implementation Steps

### 1. Restructure Player Modal HTML

Replace current playerModal content (lines 145-178) with tabbed layout:

```html
<div id="playerModal" ...>
  <div style="background:white; border-radius:15px; padding:30px; width:90%; max-width:550px; margin:auto; max-height:80vh; overflow-y:auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
      <h3>Them VDV vao <span id="playerModalTeamName"></span></h3>
      <button onclick="closePlayerModal()">x</button>
    </div>

    <!-- Tab Buttons -->
    <div style="display:flex; border-bottom:2px solid #e2e8f0; margin-bottom:15px;">
      <button class="player-modal-tab active" onclick="switchPlayerTab('pool', this)">VDV da duyet</button>
      <button class="player-modal-tab" onclick="switchPlayerTab('search', this)">Tim user</button>
    </div>

    <!-- Tab 1: Pool -->
    <div id="player-tab-pool">
      <div id="poolLoading">Dang tai...</div>
      <div id="poolList"></div>
      <div id="poolEmpty" style="display:none;">Khong co VDV nao trong pool</div>
    </div>

    <!-- Tab 2: Search (existing form, moved here) -->
    <div id="player-tab-search" style="display:none;">
      <!-- existing search form content (unchanged) -->
    </div>
  </div>
</div>
```

### 2. Pool Rendering JS

```javascript
var currentTeamId = null;

function openPlayerModal(teamId, teamName) {
    currentTeamId = teamId;
    // ... existing setup code ...
    switchPlayerTab('pool');
    fetchPool();
    modal.style.display = 'flex';
}

function fetchPool() {
    document.getElementById('poolLoading').style.display = 'block';
    document.getElementById('poolList').innerHTML = '';
    document.getElementById('poolEmpty').style.display = 'none';

    fetch(poolUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
    .then(r => r.json())
    .then(data => {
        document.getElementById('poolLoading').style.display = 'none';
        if (data.length === 0) {
            document.getElementById('poolEmpty').style.display = 'block';
            return;
        }
        renderPoolGroups(data);
    });
}

function renderPoolGroups(groups) {
    var container = document.getElementById('poolList');
    groups.forEach(function(group) {
        // Group card with header + player list + "Them ca nhom" button
        var card = document.createElement('div');
        card.style.cssText = 'border:1px solid #e2e8f0; border-radius:8px; margin-bottom:10px; overflow:hidden;';

        // Header: "Nhom #ID - N VDV"
        // Players: name, phone, gender per row with individual "Them" button
        // Footer: "Them ca nhom" button (if >1 player)

        container.appendChild(card);
    });
}
```

### 3. Add Group Action

```javascript
function addGroupToTeam(registrationId) {
    fetch(addGroupUrl.replace('__REG_ID__', registrationId), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            fetchPool(); // refresh pool
            // Optionally reload page to update team roster
            setTimeout(() => location.reload(), 500);
        } else {
            toastr.error(data.message);
        }
    });
}
```

### 4. Add Individual Player from Pool

Reuse existing `addPlayer` endpoint but auto-fill from pool data:
```javascript
function addPlayerFromPool(userId, gender) {
    fetch(addPlayerUrl.replace('__TEAM_ID__', currentTeamId), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ user_id: userId, gender: gender })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            fetchPool();
            setTimeout(() => location.reload(), 500);
        } else {
            toastr.error(data.message);
        }
    });
}
```

### 5. Preserve Existing Search Tab

Move existing search form HTML (playerSearchInput, playerSearchResults, selectedUserId, gender select, position input, submit button) into `#player-tab-search` div. No logic changes needed.

## Todo List
- [ ] Add tab buttons to player modal
- [ ] Create pool tab HTML structure
- [ ] Move existing search form into search tab
- [ ] Implement switchPlayerTab() JS
- [ ] Implement fetchPool() JS
- [ ] Implement renderPoolGroups() JS
- [ ] Implement addGroupToTeam() JS
- [ ] Implement addPlayerFromPool() JS
- [ ] Test: pool shows only approved, unassigned VDV
- [ ] Test: "Them ca nhom" adds all players + sets captain
- [ ] Test: individual add works
- [ ] Test: existing "Tim user" tab unchanged

## Success Criteria
- Modal opens with 2 tabs
- Pool tab shows grouped approved VDV
- "Them ca nhom" adds group, sets captain, refreshes pool
- Individual add removes player from pool
- Search tab works exactly as before
- Players in teams don't appear in pool

## Risk Assessment
- **Empty pool**: show clear message, auto-switch to search tab
- **Adding player already in team**: backend validates (existing constraint in LeagueService::addPlayer)

## Security
- All AJAX calls include CSRF token
- Backend validates league ownership on all endpoints
- user_id comes from pool data (server-validated), not user input
