# Phase 5: Frontend - Booking Integration

## Context
- [Wallet UX Research](../reports/researcher-260403-1004-wallet-ux-patterns.md)
- Existing booking: `resources/views/front/booking.blade.php` (65KB, transfer-only)
- 3 booking creation paths: Api/BookingController, Front/HomeController, Front/HomeYardTournamentController
- Depends on: Phase 3 (API), can run parallel with Phase 4

## Overview
- **Priority**: P1
- **Status**: Pending
- **Effort**: 3h
- Add Gems payment option to booking flow, show balance, handle insufficient funds

## Key Insights
- Booking view currently hardcodes `payment_method=transfer` (hidden input)
- Payment flow: submit -> QR modal -> upload proof -> confirm
- Need to add payment method selector: "Chuyen khoan" vs "Gems"
- Gems path: check balance -> deduct -> instant confirm (no QR/proof needed)
- Must handle insufficient balance with "Nap them Gems" link
- 3 booking controllers need the wallet payment path

## Requirements

### Functional
- Payment method selector (radio buttons): Chuyen khoan ngan hang | Thanh toan bang Gems
- Show Gems balance when Gems selected
- Show equivalent Gems amount for booking price
- Insufficient balance: show shortfall + "Nap them Gems" link
- Gems payment: instant booking confirmation (no transfer proof)
- Cashback display after successful Gems payment

### Non-Functional
- No breaking changes to existing transfer flow
- Gems option only visible to authenticated users with wallet
- Graceful degradation if wallet service unavailable

## Architecture

### Modified Booking Flow
```
Existing:
  Submit -> QR Modal -> Upload Proof -> Confirm

With Gems:
  Select Payment Method
  ├── Chuyen khoan -> existing QR flow (unchanged)
  └── Gems
      ├── Balance >= price -> Deduct -> Instant Confirm + Cashback toast
      └── Balance < price -> Show shortfall + "Nap them" link
```

## Related Code Files

### Modify
- `resources/views/front/booking.blade.php` - Add payment method selector + Gems UI
- `app/Http/Controllers/Api/BookingController.php` - Handle wallet payment in store/bookingCourt
- `app/Http/Controllers/Front/HomeController.php` - Handle wallet payment in bookingCourt
- `app/Http/Controllers/Front/HomeYardTournamentController.php` - Handle wallet payment in bookingCourt

### Reference
- `app/Models/Booking.php` - payment_method enum already includes 'wallet'
- `app/Services/GemWalletService.php` (from Phase 2)

## Implementation Steps

### 1. Payment Method Selector in booking.blade.php (~30 lines added)

Insert before payment section:
```html
<!-- Payment Method Selection -->
<div class="payment-method-selector">
    <label class="payment-method-label">Phuong thuc thanh toan</label>
    <div class="payment-method-options">
        <label class="payment-option">
            <input type="radio" name="payment_method" value="transfer" checked>
            <span>Chuyen khoan ngan hang</span>
        </label>
        <label class="payment-option" id="gems-payment-option">
            <input type="radio" name="payment_method" value="wallet">
            <span>Thanh toan bang Gems</span>
            <span class="gems-balance">So du: {{ $gemsBalance ?? 0 }} Gems</span>
        </label>
    </div>
</div>

<!-- Gems Payment Info (shown when Gems selected) -->
<div id="gems-payment-info" style="display:none;">
    <div class="gems-amount-needed">
        Can thanh toan: <strong id="gems-needed">0</strong> Gems
    </div>
    <div id="gems-insufficient" style="display:none;" class="alert-warning">
        Thieu <strong id="gems-shortfall">0</strong> Gems.
        <a href="/gems">Nap them Gems</a>
    </div>
</div>
```

### 2. JavaScript additions in booking view (~40 lines)

- Toggle payment sections based on radio selection
- Calculate gems needed: Math.ceil(totalPrice / exchangeRate)
- Check balance vs needed, show/hide insufficient warning
- On Gems submit: POST to booking endpoint with payment_method=wallet
- On success: show confirmation + cashback toast

### 3. Backend: BookingController modifications

For each of the 3 booking controllers, add wallet payment handling:

```php
// In store/bookingCourt method, after validation:
if ($request->payment_method === 'wallet') {
    $gemsNeeded = (int) ceil($totalPrice / config('gems.exchange_rate'));
    $gemTx = app(GemWalletService::class)->deduct(
        auth()->user(), $gemsNeeded, Booking::class, $booking->id,
        "Dat san {$court->name} - {$booking->booking_code}"
    );
    $booking->update([
        'payment_method' => 'wallet',
        'confirmed_at' => now(),
    ]);
    // Award cashback
    app(GemCashbackService::class)->award($gemTx);
    // Return success with cashback info
}
```

### 4. Pass wallet data to booking views

In each controller's booking page method, pass:
```php
$gemsBalance = auth()->check()
    ? app(GemWalletService::class)->getBalance(auth()->user())
    : 0;
$exchangeRate = config('gems.exchange_rate');
```

### 5. CSS additions (~20 lines in booking.css)
- Payment method radio buttons styled as cards
- Gems balance badge (green pill)
- Insufficient funds warning (orange)
- Active payment method highlight

### 6. Booking confirmation update
- For Gems payment: show "Da thanh toan bang Gems" instead of "Cho xac nhan"
- Show cashback earned: "+X Points"

## Todo List
- [ ] Add payment method selector HTML to booking.blade.php
- [ ] Add Gems payment JS logic (toggle, calculate, validate)
- [ ] Update Api/BookingController for wallet payment
- [ ] Update Front/HomeController for wallet payment
- [ ] Update Front/HomeYardTournamentController for wallet payment
- [ ] Pass wallet data (balance, exchange rate) to views
- [ ] Add CSS for payment selector
- [ ] Update booking confirmation for Gems payment
- [ ] Test: successful Gems payment
- [ ] Test: insufficient balance flow
- [ ] Test: existing transfer flow unchanged

## Success Criteria
- Payment method selector shows both options
- Gems balance displays correctly
- Insufficient balance shows warning + top-up link
- Successful Gems payment instantly confirms booking
- Cashback points awarded to UserWallet
- Existing transfer flow works exactly as before
- All 3 booking controllers handle wallet payment

## Risk Assessment
- **65KB booking.blade.php**: Large file, careful with edits. Use targeted insertions.
- **3 controllers**: DRY concern - extract shared wallet payment logic to trait or service method
- **Race condition**: User opens 2 tabs, both try to pay - lockForUpdate handles this

## Security Considerations
- Verify user owns the booking before Gems deduction
- Server-side balance check (never trust client-side)
- Gems deduction inside same DB transaction as booking confirmation
