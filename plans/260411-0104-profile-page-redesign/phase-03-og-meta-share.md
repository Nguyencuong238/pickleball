# Phase 3: Add OG Meta Tags + Share Button

## Context
- [Research Report](../reports/researcher-260411-0104-profile-page-redesign.md)
- Layout: `resources/views/layouts/front.blade.php` supports `@section('seo')`
- Controller: `app/Http/Controllers/Front/OcrController.php` (profile method, line 352)

## Overview
- **Priority:** P1
- **Status:** Complete
- **Effort:** 1.5h

Add Open Graph meta tags so shared links show a branded preview card on Zalo/iMessage/Facebook. Add share button with copy-to-clipboard functionality.

## Key Insights
- Layout already has `@hasSection('seo')` → `@yield('seo')` pattern
- Other pages (news, tournaments, clubs) already use `@section('seo')` - follow same pattern
- Web Share API available on mobile browsers for native share sheet
- No server-side OG image generation in this phase (use static template with text overlay)

## Requirements

### Functional
- OG tags: title, description, image, url, type=profile
- Twitter card: summary_large_image
- Share button: copy link to clipboard + Web Share API fallback
- Toast notification on copy success

### Non-functional
- OG preview must be readable at thumbnail size
- Share button must work without JS (fallback: link visible)

## Architecture

### OG Meta Tags
```html
@section('seo')
<title>{{ $user->name }} | OPRS {{ $user->total_oprs }} - OnePickleball</title>
<meta property="og:title" content="{{ $user->name }} | OPRS {{ $user->total_oprs }}">
<meta property="og:description" content="{{ $user->elo_rank }} | #{{ $globalRank }} Toan Quoc | {{ $user->ocr_wins }}W-{{ $user->ocr_losses }}L">
<meta property="og:image" content="{{ asset('assets/images/og-profile-card.png') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="profile">
<meta name="twitter:card" content="summary_large_image">
@endsection
```

### Share Button (in hero)
```javascript
// Web Share API (mobile) with clipboard fallback (desktop)
function shareProfile() {
    if (navigator.share) {
        navigator.share({ title, url });
    } else {
        navigator.clipboard.writeText(url);
        // show toast
    }
}
```

## Related Code Files

| File | Action | Description |
|------|--------|-------------|
| `resources/views/front/ocr/profile.blade.php` | Modify | Add @section('seo') + share button |
| `public/assets/images/og-profile-card.png` | Create | Static OG image template (1200x630) |

## Implementation Steps

1. **Add `@section('seo')` to profile.blade.php**
   - Place before `@section('css')`
   - Include: og:title, og:description, og:image, og:url, og:type
   - Include: twitter:card, twitter:title, twitter:description, twitter:image
   - Include: canonical URL

2. **Create static OG image**
   - 1200x630px PNG with OnePickleball branding
   - Green gradient background matching brand
   - Logo + "OnePickleball Player Profile" text
   - Generic template (not per-player in this phase)

3. **Add share button to hero section**
   - Button style matching action buttons
   - Icon: share/link icon (CSS, no emoji)
   - onClick: try Web Share API first, fallback to clipboard
   - Toast notification via existing toastr library

4. **Add minimal JS for share**
   - Add to `@section('js')` or inline script
   - < 20 lines of vanilla JS
   - No new dependencies

## Todo List

- [ ] Add @section('seo') with OG meta tags
- [ ] Create static OG image template (1200x630)
- [ ] Add share button to hero action buttons
- [ ] Implement share JS (Web Share API + clipboard fallback)
- [ ] Test OG preview with opengraph.xyz

## Success Criteria

- Sharing link on Zalo/Facebook shows branded preview
- Share button copies link and shows confirmation
- OG title includes player name + OPRS score
- No broken meta tags

## Risk Assessment

- **Low risk:** Additive changes only
- OG image is static in this phase (dynamic per-player generation is future enhancement)

## Next Steps (Future)

- Phase 2 enhancement: Dynamic OG image generation with Intervention Image
- Per-player card with avatar, name, OPRS score rendered as image
