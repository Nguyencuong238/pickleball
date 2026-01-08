# Phase 02: Registration Form

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 01 - Database Schema](./phase-01-database-schema.md)
**Date**: 2025-12-18
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Overview

Modify tournament registration form to show partner fields when doubles category is selected. One submission creates 2 linked TournamentAthlete records.

## Key Insights

1. **Current form** (lines 579-666): Only collects athlete_name, email, phone, category_id
2. **Category select** has `category_type` data but not exposed to JS
3. **Backend** creates single TournamentAthlete record
4. **UX pattern**: Common for local tournaments - 1 person registers for the pair

## Requirements

### Functional
- When doubles category selected, show partner fields section
- Partner fields: name (required), email (optional), phone (optional)
- Form validation: partner name required for doubles
- Backend creates 2 TournamentAthlete records with bidirectional `partner_id`
- Both athletes get same status (pending/approved based on tournament settings)

### Non-Functional
- Smooth show/hide animation for partner section
- Clear visual indication that partner info is needed
- Mobile-responsive layout

## Architecture

### Registration Flow

```
User selects "Doi Nam" (double_men)
        │
        ▼
Partner section slides in
        │
        ▼
User fills: Own info + Partner info
        │
        ▼
Submit form
        │
        ▼
Backend creates:
├── Athlete A (user info, partner_id = B.id)
└── Athlete B (partner info, partner_id = A.id)
```

### Data Structure

```
Form Data:
{
  athlete_name: "Nguyen Van A",
  email: "a@email.com",
  phone: "0901234567",
  category_id: 5,
  // Partner fields (only for doubles)
  partner_name: "Tran Van B",
  partner_email: "b@email.com",
  partner_phone: "0907654321"
}

Created Records:
tournament_athletes:
| id | athlete_name   | email         | partner_id | category_id |
|----|----------------|---------------|------------|-------------|
| 10 | Nguyen Van A   | a@email.com   | 11         | 5           |
| 11 | Tran Van B     | b@email.com   | 10         | 5           |
```

## Related Code Files

### Files to Modify

1. **`resources/views/front/tournaments/tournaments_detail.blade.php`**
   - Add `data-category-type` to category options (line 653)
   - Add partner fields section after phone field (line 629)
   - Add JS to show/hide partner section on category change

2. **`app/Http/Controllers/TournamentRegistrationController.php`** (or equivalent)
   - Modify store method to handle partner data
   - Create 2 athletes for doubles categories

### Files to Read (for context)
- `app/Models/TournamentCategory.php` - Get category_type values

## Implementation Steps

### Step 1: Add Category Type Data to Options

Location: `tournaments_detail.blade.php:653`

```blade
<option value="{{ $category->id }}"
        data-category-type="{{ $category->category_type }}"
        @if (!$isAvailable) disabled @endif>
    {{ $category->category_name }}
    @if ($category->age_group && $category->age_group !== 'open')
        ({{ $category->age_group }})
    @endif
    - {{ $athleteCount }}/{{ $category->max_participants }}{{ $statusText }}
</option>
```

### Step 2: Add Partner Fields Section

Location: After phone field section (line 629), before category selection (line 631)

```html
<!-- Partner Info Section (for doubles) -->
<div id="partnerSection" style="display: none; margin-bottom: 25px; padding: 20px; background: #f8fafc; border-radius: 12px; border: 2px dashed #e5e7eb;">
    <h4 style="margin: 0 0 15px 0; color: #1f2937; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
        [PAIR] Thong tin Partner
    </h4>

    <!-- Partner Name -->
    <div style="margin-bottom: 15px;">
        <label for="partner_name" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 8px; font-size: 0.9rem;">
            Ten Partner <span style="color: #ef4444;">*</span>
        </label>
        <input type="text" id="partner_name" name="partner_name"
            placeholder="Nhap ten partner cua ban"
            style="width: 100%; padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 0.9rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
        <div id="partner_name_error" style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: none;"></div>
    </div>

    <!-- Partner Email -->
    <div style="margin-bottom: 15px;">
        <label for="partner_email" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 8px; font-size: 0.9rem;">
            Email Partner
        </label>
        <input type="email" id="partner_email" name="partner_email"
            placeholder="Nhap email partner (tuy chon)"
            style="width: 100%; padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 0.9rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
    </div>

    <!-- Partner Phone -->
    <div>
        <label for="partner_phone" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 8px; font-size: 0.9rem;">
            SDT Partner
        </label>
        <input type="tel" id="partner_phone" name="partner_phone"
            placeholder="Nhap SDT partner (tuy chon)"
            style="width: 100%; padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 0.9rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
    </div>
</div>
```

### Step 3: Add JavaScript for Category Change

Location: In `@section('js')` block

```javascript
// Doubles category types
const DOUBLES_TYPES = ['double_men', 'double_women', 'double_mixed'];

// Handle category selection change
document.getElementById('category_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const categoryType = selectedOption.dataset.categoryType || '';
    const partnerSection = document.getElementById('partnerSection');
    const partnerNameInput = document.getElementById('partner_name');

    if (DOUBLES_TYPES.includes(categoryType)) {
        // Show partner section with animation
        partnerSection.style.display = 'block';
        partnerSection.style.opacity = '0';
        partnerSection.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            partnerSection.style.transition = 'all 0.3s ease';
            partnerSection.style.opacity = '1';
            partnerSection.style.transform = 'translateY(0)';
        }, 10);

        // Make partner name required
        partnerNameInput.setAttribute('required', 'required');
    } else {
        // Hide partner section
        partnerSection.style.transition = 'all 0.3s ease';
        partnerSection.style.opacity = '0';
        partnerSection.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            partnerSection.style.display = 'none';
        }, 300);

        // Remove required
        partnerNameInput.removeAttribute('required');

        // Clear partner fields
        document.getElementById('partner_name').value = '';
        document.getElementById('partner_email').value = '';
        document.getElementById('partner_phone').value = '';
    }
});
```

### Step 4: Update Form Validation

Location: In `submitRegisterForm()` function

```javascript
function submitRegisterForm() {
    // ... existing validation ...

    // Get category type
    const categorySelect = document.getElementById('category_id');
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const categoryType = selectedOption.dataset.categoryType || '';
    const isDoubles = DOUBLES_TYPES.includes(categoryType);

    // Validate partner name for doubles
    if (isDoubles) {
        const partnerName = document.getElementById('partner_name').value.trim();
        if (!partnerName) {
            document.getElementById('partner_name_error').textContent = 'Vui long nhap ten partner';
            document.getElementById('partner_name_error').style.display = 'block';
            return;
        }
    }

    // ... continue with form submission ...
}
```

### Step 5: Update Backend Controller

Location: `TournamentRegistrationController.php` (or wherever registration is handled)

```php
public function store(Request $request, Tournament $tournament)
{
    // Get category to check if doubles
    $category = TournamentCategory::findOrFail($request->category_id);
    $isDoubles = $category->isDoubles();

    // Validation rules
    $rules = [
        'athlete_name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'required|string|max:20',
        'category_id' => 'required|exists:tournament_categories,id',
    ];

    // Add partner validation for doubles
    if ($isDoubles) {
        $rules['partner_name'] = 'required|string|max:255';
        $rules['partner_email'] = 'nullable|email';
        $rules['partner_phone'] = 'nullable|string|max:20';
    }

    $validated = $request->validate($rules);

    DB::transaction(function () use ($validated, $tournament, $category, $isDoubles, $request) {
        // Create main athlete
        $athlete1 = TournamentAthlete::create([
            'tournament_id' => $tournament->id,
            'category_id' => $validated['category_id'],
            'user_id' => auth()->id(),
            'athlete_name' => $validated['athlete_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => 'pending',
        ]);

        if ($isDoubles) {
            // Create partner athlete
            $athlete2 = TournamentAthlete::create([
                'tournament_id' => $tournament->id,
                'category_id' => $validated['category_id'],
                'user_id' => null, // Partner may not have account
                'athlete_name' => $validated['partner_name'],
                'email' => $validated['partner_email'] ?? null,
                'phone' => $validated['partner_phone'] ?? null,
                'status' => 'pending',
                'partner_id' => $athlete1->id,
            ]);

            // Link back
            $athlete1->update(['partner_id' => $athlete2->id]);
        }

        // Update category participant count
        $category->increment('current_participants', $isDoubles ? 2 : 1);
    });

    return response()->json([
        'success' => true,
        'message' => 'Dang ky thanh cong!'
    ]);
}
```

## Todo List

- [ ] Add `data-category-type` attribute to category options
- [ ] Add partner fields HTML section
- [ ] Add CSS for partner section animation
- [ ] Add JS to show/hide partner section on category change
- [ ] Update form validation for partner name
- [ ] Update backend to create 2 athletes for doubles
- [ ] Test registration flow for singles category
- [ ] Test registration flow for doubles category
- [ ] Test form reset clears partner fields

## Success Criteria

1. Selecting singles category shows standard form (no partner fields)
2. Selecting doubles category shows partner section with animation
3. Partner name is required for doubles categories
4. Form submission creates 2 linked TournamentAthlete records for doubles
5. Both athletes have correct `partner_id` referencing each other
6. Category participant count increases by 2 for doubles

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| User doesn't fill partner info | Medium | Clear validation message |
| Category type data missing | Low | Default to singles behavior |
| Transaction failure | Low | DB rollback handles cleanup |

## Security Considerations

- Validate category belongs to tournament
- Sanitize partner input fields
- Prevent duplicate registrations (existing logic)

## Next Steps

After completing this phase:
1. Proceed to Phase 03: Backend API for match creation
2. Test end-to-end registration flow
