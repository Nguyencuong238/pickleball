# Phase 4: Frontend - Wallet UI

## Context
- [Wallet UX Research](../reports/researcher-260403-1004-wallet-ux-patterns.md)
- Existing wallet views: `resources/views/wallet/index.blade.php`, `show.blade.php`
- CSS: Custom CSS with CSS variables, Bootstrap-like utilities, green theme (#006646/#52c98c)
- Depends on: Phase 3 (API endpoints)

## Overview
- **Priority**: P1
- **Status**: Pending
- **Effort**: 4h
- Create Gems wallet dashboard with balance, top-up flow, transaction history

## Key Insights
- Existing wallet views use gradient header (#006646 -> #52c98c), card-based transactions
- No JS framework - vanilla JS + axios + toastr for notifications
- Existing points wallet has similar layout - reuse patterns
- Vietnamese users expect: large balance display, preset amounts, QR + copy bank info
- Mobile-first, responsive breakpoints: 480px, 768px, 1024px

## Requirements

### Functional
- Balance card showing Gems + VND equivalent
- Top-up form: amount input + presets (50k, 100k, 200k, 500k VND)
- QR code display modal with bank info + copy buttons
- Transaction history list with pagination
- Top-up status polling (check pending tx every 5s for 15 min)

### Non-Functional
- Mobile-first responsive design
- Match existing green theme
- Vietnamese text with diacritics (per feedback memory)
- Loading states for all async operations

## Architecture

### Screen Flow
```
Gems Wallet Page (single page with sections)
├── Balance Card (top) - Gems balance + VND equivalent
├── Quick Actions: [Nap Gems] [Lich su]
├── Top-up Section (expandable/modal)
│   ├── Amount input + presets
│   ├── QR code display + bank info
│   └── Status polling -> success toast
└── Transaction History (below, paginated)
```

## Related Code Files

### Create
- `resources/views/front/gems/index.blade.php` - Wallet dashboard
- `resources/views/front/gems/partials/balance-card.blade.php` - Balance component
- `resources/views/front/gems/partials/topup-modal.blade.php` - Top-up QR modal
- `resources/views/front/gems/partials/transaction-list.blade.php` - History list
- `public/assets/css/gems-wallet.css` - Wallet-specific styles
- `public/assets/js/gems-wallet.js` - Top-up + polling logic

### Create (Controller for web views)
- `app/Http/Controllers/Front/GemController.php` - Web view controller

### Modify
- `routes/web.php` - add /gems routes
- Navigation: add "Vi Gems" link to user menu in layout

## Implementation Steps

### 1. Front/GemController (web views, ~40 lines)
- `index()`: Load wallet + recent transactions, return Blade view
- Uses auth middleware

### 2. Wallet Dashboard View (`gems/index.blade.php`, ~120 lines)
- Extends `layouts.front`
- Include balance-card, topup-modal, transaction-list partials
- Vietnamese text: "Vi Gems", "Nap Gems", "Lich su giao dich", "So du"

### 3. Balance Card Partial (~40 lines)
```
┌─────────────────────────────────────┐
│ (gradient header #006646 -> #52c98c)│
│   So du Gems                        │
│   ★ 1,500 Gems                     │
│   ~ 1,500,000 VND                   │
│                                     │
│   [Nap Gems]    [Lich su]          │
└─────────────────────────────────────┘
```

### 4. Top-up Modal (~80 lines)
```
┌─ Nap Gems ──────────────────────────┐
│                                      │
│ Nhap so tien (VND):                 │
│ ┌──────────────────────────────────┐│
│ │ 100,000                          ││
│ └──────────────────────────────────┘│
│ [50k] [100k] [200k] [500k]         │
│                                      │
│ Ban se nhan: 100 Gems               │
│                                      │
│ [Tao ma QR]                         │
│                                      │
│ ── After QR generated ──            │
│                                      │
│ [QR CODE IMAGE 200x200]            │
│                                      │
│ Ngan hang: [bank name]             │
│ So TK: [account] [Copy]            │
│ So tien: 100,000 VND [Copy]        │
│ Noi dung CK: GEMS001T123 [Copy]    │
│                                      │
│ Dang cho thanh toan... (polling)    │
│ Het han sau: 14:32                  │
└──────────────────────────────────────┘
```

### 5. Transaction List Partial (~50 lines)
- Reuse existing wallet transaction card pattern
- Icon + type label + description + amount (green +, red -)
- Date + status badge
- Pagination links

### 6. CSS (`gems-wallet.css`, ~100 lines)
- Reuse CSS variable system from existing styles
- Balance card with gradient header
- Preset amount buttons (pill-shaped)
- QR code display area
- Transaction list items
- Responsive: stack on mobile, sidebar on desktop

### 7. JavaScript (`gems-wallet.js`, ~80 lines)
- Preset button click -> fill amount input
- Calculate Gems from VND input (using exchange rate from data attribute)
- Submit top-up -> show QR modal
- Poll GET /api/gems/transactions/{id} every 5s for pending tx
- On completed: close modal, show success toast (toastr), refresh balance
- Countdown timer for 15-min expiry
- Copy to clipboard for bank info

### 8. Routes + Navigation
- `Route::get('/gems', [Front\GemController::class, 'index'])->name('gems.index')`
- Add "Vi Gems" to user dropdown/navigation menu

## Todo List
- [ ] Create Front/GemController
- [ ] Create gems/index.blade.php with layout
- [ ] Create balance-card partial
- [ ] Create topup-modal partial
- [ ] Create transaction-list partial
- [ ] Create gems-wallet.css
- [ ] Create gems-wallet.js (topup + polling)
- [ ] Add web routes
- [ ] Add navigation link
- [ ] Test responsive on mobile/desktop

## Success Criteria
- Balance displays correctly with VND equivalent
- Top-up generates QR and shows bank info
- Polling detects completed payment and updates UI
- Transaction history loads with pagination
- Responsive on all breakpoints
- Vietnamese text with proper diacritics

## Risk Assessment
- QR image from SePay external URL - fallback to manual bank info if image fails
- Polling timeout after 15 min - show "Het han" message with retry option
- Large transaction history - pagination prevents slow loads

## Security Considerations
- Auth middleware on all web routes
- API calls include CSRF token (axios default)
- No sensitive data in client-side JS
