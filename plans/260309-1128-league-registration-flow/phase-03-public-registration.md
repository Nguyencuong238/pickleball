# Phase 3: Frontend - Public Registration Page

## Context Links
- [Phase 2: Backend Logic](./phase-02-backend-logic.md)
- [Existing front layout](../../resources/views/layouts/front.blade.php)
- [League create form](../../resources/views/home-yard/leagues/create.blade.php)

## Overview
- **Priority**: P1
- **Status**: pending
- **Effort**: 2.5h

## Key Insights
- Page is PUBLIC (no auth) -- VDV access via shared link
- Form renders N player sub-forms based on `league.required_players_per_registration`
- Must show league info (name, fee, deadline) before form
- Single payment proof upload for entire group
- Extend `layouts.front` (consistent with other public pages)

## Requirements

### Functional
- Display league name, description, registration fee, deadline
- Show closed state if past deadline or league not in registration status
- Render N player forms dynamically (phone, name, skill_level, province, gender, birthday, photo, message)
- Upload 1 payment proof image (preview before submit)
- Success page/message after submission
- Phone input: auto-format, required. Normalized to 0xxx on server.
- Skill level + province: free text (no dropdowns)
<!-- Updated: Validation Session 1 - Free text fields, phone normalization -->

### Non-functional
- Mobile-responsive (most VDV access on phone)
- Client-side validation before submit
- Image preview for payment proof

## Architecture

### View File
`resources/views/front/leagues/register.blade.php`

### Layout
```
@extends('layouts.front')

League Info Header (name, fee, deadline)
  |
  v
Registration Form
  ├── Player 1 (phone*, name*, gender*, skill_level, province, birthday, photo, message)
  ├── Player 2 (if required_players >= 2)
  ├── ...
  ├── Player N
  └── Payment Proof upload*
  └── Submit button
```

## Related Code Files

### Files to CREATE
- `resources/views/front/leagues/register.blade.php`

### Files to MODIFY
- None (routes added in Phase 2)

## Implementation Steps

### 1. Create Registration View

Structure:
```html
@extends('layouts.front')
@section('title', 'Dang ky - ' . $league->name)

@section('content')
<!-- League Info Banner -->
<div> League name, season, fee, deadline countdown </div>

<!-- Closed State -->
@if($closed)
  <div> Registration da dong </div>
@else

<!-- Registration Form -->
<form method="POST" action="{{ route('leagues.register.store', $league) }}" enctype="multipart/form-data">
  @csrf

  @for($i = 0; $i < $league->required_players_per_registration; $i++)
    <!-- Player Card #{{ $i+1 }} -->
    <div class="player-card">
      <h4>VDV {{ $i + 1 }} {{ $i === 0 ? '(Doi truong)' : '' }}</h4>
      <input name="players[{{ $i }}][phone]" type="tel" required>
      <input name="players[{{ $i }}][name]" required>
      <select name="players[{{ $i }}][gender]" required>
        <option value="male">Nam</option>
        <option value="female">Nu</option>
      </select>
      <input name="players[{{ $i }}][skill_level]">
      <input name="players[{{ $i }}][province]">
      <input name="players[{{ $i }}][birthday]" type="date">
      <input name="players[{{ $i }}][photo]" type="file" accept="image/*">
      <textarea name="players[{{ $i }}][message]" maxlength="500"></textarea>
    </div>
  @endfor

  <!-- Payment Proof -->
  <div>
    <label>Anh chuyen khoan *</label>
    <input name="payment_proof" type="file" accept="image/*" required>
    <img id="payment-preview" style="display:none; max-width:300px;">
  </div>

  <button type="submit">Gui dang ky</button>
</form>
@endif
@endsection
```

### 2. Styling Approach
- Use inline styles consistent with existing league views (home-yard pattern)
- Responsive: max-width container, stack on mobile
- Card-style player forms with numbered headers
- Green success banner for captain indicator on player 1

### 3. Client-side JS
- Payment proof image preview on file select
- Phone input: strip non-digits, show formatted
- Basic client validation: required fields highlighted
- Disable submit button on form submission (prevent double-submit)

### 4. Controller `showForm` Logic
```php
public function showForm(League $league)
{
    $closed = false;
    if ($league->status !== 'registration') $closed = true;
    if ($league->registration_deadline && $league->registration_deadline->isPast()) $closed = true;

    return view('front.leagues.register', compact('league', 'closed'));
}
```

### 5. Success Handling
After successful POST, redirect back with flash success message. Show "Dang ky thanh cong! Vui long cho admin duyet." banner.

## Todo List
- [ ] Create register.blade.php view
- [ ] League info header section
- [ ] Dynamic N-player form rendering
- [ ] Payment proof upload with preview
- [ ] Client-side validation JS
- [ ] Closed/deadline check display
- [ ] Success message display
- [ ] Mobile responsive styling
- [ ] Test with required_players = 1, 2, 4

## Success Criteria
- Public URL `/leagues/{slug}/register` loads without auth
- Shows closed state when deadline passed or wrong league status
- Renders correct number of player forms
- Payment proof preview works
- Form submits successfully, shows success message
- Works on mobile viewport

## Risk Assessment
- **Large photos**: max 2MB per player photo, 5MB payment proof
- **Slow connection**: disable submit on click, show loading indicator

## Security
- CSRF token on form
- No auth required but rate-limited (Phase 2 throttle)
- File type validation (image only)
- All output escaped via Blade `{{ }}`
