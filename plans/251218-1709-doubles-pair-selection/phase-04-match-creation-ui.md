# Phase 04: Match Creation UI

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 03 - Backend API](./phase-03-backend-api.md)
**Date**: 2025-12-18
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Overview

Modify the match creation modal in `config.blade.php` to:
1. Detect if selected category is doubles
2. Show pair selection UI for doubles instead of individual athlete selection
3. Update form submission to work with pairs

## Key Insights

1. **Current UI** (lines 1041-1055): Shows 2 dropdowns "VDV 1" and "VDV 2" for all categories
2. **JavaScript handler** (line 1918): `handleCategoryChange()` fetches athletes and populates dropdowns
3. **API response**: Will now include `is_doubles` flag and `pairs` array for doubles categories
4. **Label changes**: For doubles, labels should be "Cap 1" and "Cap 2" instead of "VDV 1" and "VDV 2"

## Requirements

### Functional
- When doubles category selected:
  - Change labels from "VDV 1/VDV 2" to "Cap 1/Cap 2"
  - Populate dropdowns with pairs (e.g., "Nguyen A / Tran B")
  - Store `primary_athlete_id` as the value
- When singles category selected: Keep existing behavior
- Show helpful message when no pairs available for doubles

### Non-Functional
- Smooth UI transition between singles/doubles modes
- No page reload required
- Backward compatible with existing singles functionality

## Architecture

### UI State Machine

```
Category Selected
       │
       ▼
  ┌─────────────────┐
  │ Fetch Athletes  │
  └────────┬────────┘
           │
           ▼
    ┌──────────────┐
    │ is_doubles?  │
    └──────┬───────┘
           │
     ┌─────┴─────┐
     │           │
     ▼           ▼
  Doubles     Singles
    │           │
    ▼           ▼
Show Pairs  Show Athletes
"Cap 1/2"   "VDV 1/2"
```

## Related Code Files

### Files to Modify

1. **`resources/views/home-yard/config.blade.php`**
   - Match creation modal HTML (lines 1041-1055)
   - `handleCategoryChange()` function (line 1918)
   - Form submission handler

## Implementation Steps

### Step 1: Update Modal HTML Labels

Location: `resources/views/home-yard/config.blade.php:1040-1055`

```html
<!-- Buoc 2: Chon VDV/Cap thuoc noi dung thi dau do -->
<div class="grid grid-2">
    <div class="form-group">
        <label class="form-label" id="athlete1Label">[USER] Buoc 2: Chon VDV 1 *</label>
        <select id="athlete1Select" name="athlete1_id" class="form-select" required disabled>
            <option value="">-- Hay chon noi dung thi dau truoc --</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" id="athlete2Label">[USER] Chon VDV 2 *</label>
        <select id="athlete2Select" name="athlete2_id" class="form-select" required disabled>
            <option value="">-- Hay chon noi dung thi dau truoc --</option>
        </select>
    </div>
</div>
```

### Step 2: Update handleCategoryChange() Function

Location: `resources/views/home-yard/config.blade.php:1918`

Replace the fetch handler:

```javascript
function handleCategoryChange() {
    const categorySelect = document.getElementById('matchCategoryId');
    const athlete1Select = document.getElementById('athlete1Select');
    const athlete2Select = document.getElementById('athlete2Select');
    const athlete1Label = document.getElementById('athlete1Label');
    const athlete2Label = document.getElementById('athlete2Label');
    const groupSelect = document.getElementById('matchGroupSelect');
    const tournamentId = {!! $tournament->id ?? 0 !!};

    if (!categorySelect.value) {
        // Reset if no category selected
        athlete1Select.innerHTML = '<option value="">-- Hay chon noi dung thi dau truoc --</option>';
        athlete2Select.innerHTML = '<option value="">-- Hay chon noi dung thi dau truoc --</option>';
        athlete1Select.disabled = true;
        athlete2Select.disabled = true;
        athlete1Label.textContent = '[USER] Buoc 2: Chon VDV 1 *';
        athlete2Label.textContent = '[USER] Chon VDV 2 *';

        groupSelect.innerHTML = '<option value="">-- Chon noi dung thi dau truoc --</option>';
        groupSelect.disabled = true;
        return;
    }

    const categoryId = categorySelect.value;

    // Fetch athletes/pairs for category
    fetch(`/homeyard/tournaments/${tournamentId}/categories/${categoryId}/athletes`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.is_doubles) {
                    // Doubles category: show pairs
                    athlete1Label.textContent = '[PAIR] Buoc 2: Chon Cap 1 *';
                    athlete2Label.textContent = '[PAIR] Chon Cap 2 *';

                    if (data.pairs && data.pairs.length > 0) {
                        const pairOptions = data.pairs.map(pair =>
                            `<option value="${pair.primary_athlete_id}">${pair.pair_name}</option>`
                        ).join('');

                        athlete1Select.innerHTML = `<option value="">-- Chon Cap 1 --</option>${pairOptions}`;
                        athlete2Select.innerHTML = `<option value="">-- Chon Cap 2 --</option>${pairOptions}`;
                        athlete1Select.disabled = false;
                        athlete2Select.disabled = false;
                    } else {
                        athlete1Select.innerHTML = '<option value="">Chua co cap VDV nao (can dang ky partner)</option>';
                        athlete2Select.innerHTML = '<option value="">Chua co cap VDV nao (can dang ky partner)</option>';
                        athlete1Select.disabled = true;
                        athlete2Select.disabled = true;
                    }
                } else {
                    // Singles category: show individual athletes
                    athlete1Label.textContent = '[USER] Buoc 2: Chon VDV 1 *';
                    athlete2Label.textContent = '[USER] Chon VDV 2 *';

                    if (data.athletes && data.athletes.length > 0) {
                        const athleteOptions = data.athletes.map(athlete =>
                            `<option value="${athlete.id}">${athlete.athlete_name}</option>`
                        ).join('');

                        athlete1Select.innerHTML = `<option value="">-- Chon VDV 1 --</option>${athleteOptions}`;
                        athlete2Select.innerHTML = `<option value="">-- Chon VDV 2 --</option>${athleteOptions}`;
                        athlete1Select.disabled = false;
                        athlete2Select.disabled = false;
                    } else {
                        athlete1Select.innerHTML = '<option value="">Khong co VDV nao</option>';
                        athlete2Select.innerHTML = '<option value="">Khong co VDV nao</option>';
                        athlete1Select.disabled = true;
                        athlete2Select.disabled = true;
                    }
                }
            } else {
                athlete1Select.innerHTML = '<option value="">Loi tai du lieu</option>';
                athlete2Select.innerHTML = '<option value="">Loi tai du lieu</option>';
                athlete1Select.disabled = true;
                athlete2Select.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error fetching athletes:', error);
            athlete1Select.innerHTML = '<option value="">Loi tai du lieu</option>';
            athlete2Select.innerHTML = '<option value="">Loi tai du lieu</option>';
            athlete1Select.disabled = true;
            athlete2Select.disabled = true;
        });

    // Fetch groups (unchanged)
    fetch(`/homeyard/tournaments/${tournamentId}/categories/${categoryId}/groups`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.groups && data.groups.length > 0) {
                const groupOptions = data.groups.map(group =>
                    `<option value="${group.id}">${group.group_name}</option>`
                ).join('');
                groupSelect.innerHTML = `<option value="">-- Chon bang/nhom --</option>${groupOptions}`;
                groupSelect.disabled = false;
            } else {
                groupSelect.innerHTML = '<option value="">Khong co bang/nhom</option>';
                groupSelect.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error fetching groups:', error);
            groupSelect.innerHTML = '<option value="">Loi tai du lieu</option>';
            groupSelect.disabled = true;
        });
}
```

### Step 3: Update Edit Match Modal (Similar Changes)

Location: `resources/views/home-yard/config.blade.php:1128-1199`

Apply similar logic to the edit match modal:
- Add labels with IDs
- Modify edit form to handle doubles categories

```html
<!-- In edit match modal, update athlete selection section -->
<div class="grid grid-2">
    <div class="form-group">
        <label class="form-label" id="editAthlete1Label">VDV 1 *</label>
        <select id="editAthlete1" name="athlete1_id" class="form-select" required>
            <option value="">-- Chon VDV --</option>
            @if ($tournament && $tournament->athletes)
                @foreach ($tournament->athletes as $athlete)
                    <option value="{{ $athlete->id }}">{{ $athlete->athlete_name }}</option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" id="editAthlete2Label">VDV 2 *</label>
        <select id="editAthlete2" name="athlete2_id" class="form-select" required>
            <option value="">-- Chon VDV --</option>
            @if ($tournament && $tournament->athletes)
                @foreach ($tournament->athletes as $athlete)
                    <option value="{{ $athlete->id }}">{{ $athlete->athlete_name }}</option>
                @endforeach
            @endif
        </select>
    </div>
</div>
```

### Step 4: Add Duplicate Pair Selection Prevention

Add validation to prevent selecting same pair for both sides:

```javascript
// Add to athlete1Select change handler
document.getElementById('athlete1Select').addEventListener('change', function() {
    const selectedValue = this.value;
    const athlete2Select = document.getElementById('athlete2Select');

    // Re-enable all options first
    Array.from(athlete2Select.options).forEach(option => {
        option.disabled = false;
    });

    // Disable the selected pair in the other dropdown
    if (selectedValue) {
        const matchingOption = athlete2Select.querySelector(`option[value="${selectedValue}"]`);
        if (matchingOption) {
            matchingOption.disabled = true;
        }
    }
});

// Same for athlete2Select (mirror logic)
document.getElementById('athlete2Select').addEventListener('change', function() {
    const selectedValue = this.value;
    const athlete1Select = document.getElementById('athlete1Select');

    Array.from(athlete1Select.options).forEach(option => {
        option.disabled = false;
    });

    if (selectedValue) {
        const matchingOption = athlete1Select.querySelector(`option[value="${selectedValue}"]`);
        if (matchingOption) {
            matchingOption.disabled = true;
        }
    }
});
```

## Todo List

- [ ] Add IDs to athlete labels in create match modal
- [ ] Update `handleCategoryChange()` to handle doubles response
- [ ] Update labels dynamically based on category type
- [ ] Add duplicate selection prevention
- [ ] Update edit match modal similarly
- [ ] Test UI flow for both singles and doubles categories
- [ ] Test with no pairs available scenario

## Success Criteria

1. Selecting doubles category shows "Cap 1" / "Cap 2" labels
2. Dropdown options show pair names (e.g., "A / B")
3. Selecting singles category shows "VDV 1" / "VDV 2" labels
4. Cannot select same pair for both Cap 1 and Cap 2
5. Helpful message when no pairs registered for doubles category
6. Form submission works correctly for both types

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Label not updating | Low | Use element IDs and textContent |
| Duplicate selection | Medium | Add change event listeners |
| Edit modal inconsistency | Medium | Apply same logic to edit modal |

## Security Considerations

- No additional security concerns - all validation done server-side
- Frontend validation is UX only, backend validates in `storeMatch()`

## Next Steps

After completing this phase:
1. Run full integration test
2. Test athlete/pair registration flow
3. Update documentation if needed
