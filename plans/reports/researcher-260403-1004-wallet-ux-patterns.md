# Wallet & Payment UX Research: Vietnamese Market Mobile-First Design

**Date:** 2026-04-03 | **Target:** Pickleball app wallet & booking payment feature

---

## Executive Summary

Vietnamese digital wallet ecosystem dominated by **MoMo (70% market share), ZaloPay (53%), VietQR**. Key findings:
- **80% of consumers prefer mobile wallets** for payments; 68% use tap-to-pay
- **2024 digital payment value: 295.2 quadrillion VND (~$11.3T)**
- **QR payment adoption: 62% of users scan codes ~16x/month** (VietQR standard)
- Grab's early cash integration was critical for user trust in Vietnam
- Gamification/loyalty gaining traction but still secondary to core payment UX

**Recommendation for MVP:** Focus on **clear balance display, simple payment method selector, wallet top-up via QR, and post-payment confirmation**. Defer cashback/gamification to Phase 2.

---

## 1. Wallet Dashboard UX Patterns

### Screen Components (Desktop & Mobile)

| Component | Priority | Details |
|-----------|----------|---------|
| **Balance Card** | P0 | Large, prominent VND amount (top of screen). Show "₫ 1,234,567" with VND symbol. Include "Available balance" label. |
| **Quick Actions** | P0 | 3-4 buttons below balance: "Top up", "Send Money", "Payment History", "Settings". Icons + text. |
| **Transaction History** | P0 | Scrollable list below actions. Show last 10 txns. Columns: date, description, amount, status icon. |
| **Top-up Banner** | P1 | Highlight if balance < 50k VND or after 2+ days of inactivity. Soft call-to-action. |
| **Balance Mini-Widget** | P1 | (Mobile only) Fixed header showing balance + "Top up" quick button. Sticky. |

### Transaction List Item

```
[Icon] Deposit Top-up          ₫ 500,000    14:32 | 2026-04-03
[Icon] Payment to Court #5     -₫ 250,000   10:15 | 2026-04-02
[Icon] Cashback Earned         +₫ 5,000     20:00 | 2026-04-01
```

- Show transaction direction (+ green, - gray)
- Category icon + description (max 30 chars)
- Amount + time + date
- Tap to expand: receipt, reference number, status

### Mobile Layout (320px-480px)

```
[Header: Safe Area 20px]
┌─────────────────────────────┐
│ ₫ 1,234,567 Available        │  ← Balance Card (h: 120px)
│ [Top up] [History] [Send]    │
└─────────────────────────────┘
┌─────────────────────────────┐
│ Recent Transactions          │
│ [Txn 1 item]                 │  ← List (scrollable)
│ [Txn 2 item]                 │
│ [Txn 3 item]                 │
└─────────────────────────────┘
```

### Desktop Layout (1024px+)

Two-column: Balance card + quick actions (left 25%), transaction history (right 75%). Side-by-side for easy scanning.

---

## 2. QR Payment Top-Up Flow (Bank Transfer via VietQR)

### Best Practice: 4-Step Flow

```
SCREEN 1: Top-Up Amount Entry
├─ Amount input field (numeric, defaults to 100k VND)
├─ Presets: [50k] [100k] [200k] [500k]
├─ Description: "Link your bank account to transfer funds securely"
└─ [Generate QR Code] button (primary)

SCREEN 2: QR Code Display (15-min validity)
├─ Large QR code (200x200px minimum)
├─ "Amount: ₫ 100,000" centered above QR
├─ "Scan from your bank app" instruction
├─ [Copy Code] button (text-based fallback: account #, amount)
├─ Timer: "QR expires in 14:32"
├─ [Refresh QR] button (disabled first 30 sec)
└─ Loading state during generation

SCREEN 3: Payment Processing
├─ Loading animation (rotating icon or progress bar)
├─ "Processing your transfer..."
├─ Allow user to close & check status later
├─ Timeout: if no confirmation after 15 min, show "Retry" button

SCREEN 4: Success/Failure Confirmation
├─ SUCCESS:
│   ├─ Green checkmark + "Transfer Successful"
│   ├─ "₫ 100,000 added to your wallet"
│   ├─ Reference #, timestamp, bank details
│   └─ [Continue] button → Wallet dashboard
└─ FAILURE:
    ├─ Red error icon
    ├─ "Transfer Failed. [Reason]"
    ├─ Specific error: "Bank timeout", "Invalid amount", etc.
    └─ [Retry] / [Back] buttons
```

### Mobile UX Details
- **Amount input**: Large input field (44px height), keyboard stays open for presets
- **QR display**: Full-screen, finger-friendly close button (top-right, 48px)
- **Copy fallback**: Show manual transfer details (account, amount, reference) if QR fails to generate
- **Status polling**: Check payment status every 5 sec for first 60 sec, then every 30 sec until 15 min timeout

### Error Handling
| Error | Message | Recovery |
|-------|---------|----------|
| Network timeout | "Unable to generate QR code. Check internet connection." | [Retry] button |
| Invalid amount | "Amount must be between ₫ 10,000 and ₫ 10,000,000" | Clear input, show bounds |
| Bank decline | "Your bank declined this transfer. Try a different bank app." | [Change Amount] / [Back] |
| Duplicate payment | "Payment already detected. Visit wallet dashboard to confirm." | [Check Status] |

---

## 3. Booking Payment Flow (Wallet vs Cash vs Transfer)

### Screen: Payment Method Selector

**Context:** User completes court booking (e.g., Saturday 14:00, Court 5, 2 hours = ₫ 500,000)

```
ORDER SUMMARY
┌──────────────────────────────────────┐
│ Court 5, Court - 2 hours             │
│ 2026-04-05 14:00 - 16:00             │
│ Total: ₫ 500,000                     │
└──────────────────────────────────────┘

PAYMENT METHOD
┌──────────────────────────────────────┐
│ ○ Wallet (Available: ₫ 1,234,567)    │  ← Recommended (green badge)
│   [Select] → Shows balance check      │
├──────────────────────────────────────┤
│ ○ Bank Transfer (VietQR)             │
│   [Select] → Shows bank app list     │
├──────────────────────────────────────┤
│ ○ Cash at Court                      │
│   [Select] → Shows "Pay on arrival"  │
└──────────────────────────────────────┘

[Cancel] [Confirm & Pay]
```

### Wallet Selection (Insufficient Funds Handling)

**CASE 1: Sufficient Balance**
```
┌──────────────────────────────────────┐
│ ✓ Wallet Payment                     │
│ Current balance: ₫ 1,234,567         │
│ After payment: ₫ 734,567             │
│ [Confirm Payment] (primary)          │
└──────────────────────────────────────┘
```

**CASE 2: Insufficient Balance**
```
┌──────────────────────────────────────┐
│ ✗ Insufficient wallet balance        │
│ Required: ₫ 500,000                  │
│ Available: ₫ 45,000                  │
│ Needed: ₫ 455,000 more               │
│                                      │
│ [Top up wallet] [Try another method] │
└──────────────────────────────────────┘
```

- Show shortfall amount in red
- Offer quick top-up button (pre-fill remaining amount)
- Allow fallback to other payment methods

### Booking Confirmation Screen

**After payment selection confirmed:**
```
┌──────────────────────────────────────┐
│ Processing payment...                │
│ [Loading animation]                  │
│ "Do not close this page"             │
└──────────────────────────────────────┘

[Success]
┌──────────────────────────────────────┐
│ ✓ Booking Confirmed                  │
│ Booking code: BK0050260403001        │
│ Court 5, 2026-04-05, 14:00-16:00     │
│ Amount: ₫ 500,000 (Wallet)           │
│                                      │
│ [View Booking] [Back to Dashboard]   │
└──────────────────────────────────────┘

[Failed - Wallet]
┌──────────────────────────────────────┐
│ ✗ Payment Failed                     │
│ Wallet transaction declined.          │
│ Reason: Network error                │
│                                      │
│ [Retry] [Change Payment Method]      │
└──────────────────────────────────────┘
```

### Mobile-Specific Behaviors
- Sticky order summary header (show total at top during method selection)
- Radio buttons instead of cards on mobile
- Confirmation modal after method selection (prevent accidental payment)
- Back button always available (returns to booking review)

---

## 4. Cashback & Rewards Display

### Post-Payment Cashback Toast

**Timing:** Show 2-3 seconds after successful payment

```
┌──────────────────────────────────────┐
│ 🎉 Earned ₫ 5,000 Cashback!          │
│ +5 Loyalty Points                    │
│ [View Rewards] [Dismiss]             │
└──────────────────────────────────────┘
```

- Green background, white text
- Icon: gift/star/check
- Include points if applicable
- Dismiss auto-hides after 3 sec

### Rewards Dashboard (MVP Phase 2)

```
EARNINGS SUMMARY
┌──────────────────────────────────────┐
│ Total Cashback This Month             │
│ ₫ 45,000                              │
│ 120 Loyalty Points                    │
└──────────────────────────────────────┘

RECENT TRANSACTIONS
[Booking - Court 5 → +₫ 5,000 (1%)]
[Booking - Court 3 → +₫ 2,500 (0.5%)]
[Top-up → +₫ 10,000 (bonus)]

NEXT MILESTONE
[████████░░░░] 1,280 / 1,500 points
Unlock: Free court hour (₫ 250k value)
```

- Simple bar chart of earnings over time (optional)
- Filter by period (week/month/all-time)
- Link to redeem rewards

### Best Practices
- Display earned rewards **after** payment completes (not during processing)
- Show points + VND equivalent for clarity
- Link rewards to specific transactions for transparency
- Avoid gambling mechanics (1:1 redemption, no randomness)

---

## 5. Gamification Elements (Phase 2+)

### Loyalty Points System

**Visual Treatment:**
- Star icon for points
- Counter display: "⭐ 120 points"
- Progress bar toward next tier/milestone
- Color: Gold (#FFB81C) or brand color

**Milestone Badges (Optional):**
```
[BRONZE] [SILVER] [GOLD] [PLATINUM]
 25pts    100pts   250pts   500pts
```

Unlock with XP/transactions, show in profile + profile card.

### Gems/Coins (Gaming-Style Apps)

If adopting Grab-style "Grab Rewards" or WeChat-style points:
- **Icon:** Diamond/gem shape
- **Color:** Purple (#9C27B0) or custom brand color
- **Earned from:** Bookings, referrals, challenges
- **Redeemable for:** Court discounts, free hours, merchandise
- **Display:** Balance in header (sticky), transaction log in rewards history

**Example**: "💎 50 gems earned" → "Redeem for 10% off next booking"

### Anti-Patterns to Avoid
- ❌ Randomized loot boxes (builds gambling perception in Vietnam)
- ❌ Points that expire (frustrates users, builds distrust)
- ❌ Hidden tier requirements (be transparent)
- ❌ Excessive notifications (max 1 per day for rewards)

---

## 6. Vietnamese Market Specifics

### VND Formatting Rules
- **Display:** "₫ 1,234,567" (currency symbol first, comma-separated thousands)
- **Input:** Accept "1234567" (auto-format on blur)
- **Zero:** Display "₫ 0" not "₫ 0.00" (VND has no decimals)
- **Transaction limits:** Inform users of State Bank caps:
  - Daily limit: ₫ 20 million (cumulative)
  - Per-transaction: ₫ 10 million soft limit
  - Biometric required above ₫ 10M

### Payment Method Preferences (Market Data)
| Method | % Users | Notes |
|--------|---------|-------|
| Mobile Wallet (MoMo/ZaloPay) | 80% | Primary preference, fastest checkout |
| Tap-to-pay (NFC) | 68% | Growing among urban youth |
| VietQR Bank Transfer | 62% | Preferred for large transactions, direct to account |
| Cash on Delivery | 45% | Still trusted for unfamiliar merchants |

### Design Recommendations
1. **Default to wallet** if user has positive balance
2. **Show VietQR prominently** for large bookings (> ₫ 5M)
3. **Always offer cash payment** as fallback (builds trust for new users)
4. **QR code generation**: Must work offline and in low-bandwidth areas
5. **Timeout handling**: 15-min QR validity is standard; refresh gracefully

---

## 7. MVP Wallet Feature - Recommended Screen List

### Core Screens (Phase 1)

| # | Screen | Purpose | Priority |
|---|--------|---------|----------|
| 1 | Wallet Dashboard | Show balance, recent txns, quick actions | P0 |
| 2 | Top-Up Entry | Amount input + presets | P0 |
| 3 | QR Display | Large QR code + timer + manual fallback | P0 |
| 4 | Top-Up Status | Loading/success/error confirmation | P0 |
| 5 | Payment Method Selector | Wallet/Cash/Transfer choice for booking | P0 |
| 6 | Booking Confirmation | Post-payment receipt + booking details | P0 |
| 7 | Transaction Detail | Expand txn item → full receipt view | P1 |
| 8 | Wallet Settings | Withdraw, notification prefs, security | P1 |

### Phase 2 Additions
- Rewards/cashback dashboard
- Referral code generation
- Bulk transaction export (CSV/PDF)
- Spending analytics by court
- Recurring booking auto-payment

---

## 8. Key UI Component Library

### Balance Card
```
┌────────────────────────────────────┐
│ Balance                            │
│ ₫ 1,234,567                        │
│                                    │
│ [Top Up] [Send Money]              │
└────────────────────────────────────┘
```
- Height: 140px (mobile), 160px (desktop)
- Background: White/light gray, subtle shadow
- Buttons: 44px height, rounded corners (8px)

### Payment Method Card
```
┌────────────────────────────────────┐
│ ○ Wallet                           │
│   Available: ₫ 1,234,567           │
│   New balance: ₫ 734,567           │
└────────────────────────────────────┘
```
- Radio button left, details right
- Disabled state (gray) if insufficient balance
- Show new balance estimate in smaller text

### Error Alert
```
┌────────────────────────────────────┐
│ ⚠️  Insufficient balance            │
│ You need ₫ 455,000 more            │
│                                    │
│ [Top Up] [Try Another Method]      │
└────────────────────────────────────┘
```
- Red/orange background (alert color)
- Icon + title + description
- 2 action buttons (primary + secondary)

### Loading State
- Spinner icon (spinning circle, 32px)
- Text: "Processing payment..." or "Generating QR code..."
- Duration: Show timer after 3 sec ("This may take up to 30 seconds")
- Allow dismiss with warning: "Payment may still process in background"

---

## 9. Best Practices & Anti-Patterns

### ✅ DO
- **Show balance prominently** on every payment screen (reduce cognitive load)
- **Provide manual transfer details** as QR fallback (accessibility)
- **Display new balance after payment** (confirms transaction impact)
- **Use consistent currency symbol** (₫) everywhere
- **Lock payment button** until method selected (prevent double-pay)
- **Show transaction reference** on all receipts (accountability)
- **Allow users to review before confirming** (reduce payment errors)
- **Implement retry logic** with exponential backoff (network resilience)

### ❌ DON'T
- ❌ Auto-submit payment (must require explicit confirmation)
- ❌ Hide currency symbol in input (1,234,567 without ₫ confuses users)
- ❌ Use "success" animation longer than 1-2 sec (delays UX, annoying)
- ❌ Force top-up on failed payment (allow payment method swap first)
- ❌ Show detailed error codes (e.g., "ERR_TIMEOUT_002") to users
- ❌ Expire QR without user-facing warning
- ❌ Require multiple taps to reach payment method selector (buried 3+ levels deep)
- ❌ Auto-save payment method for security-sensitive flows (TOTP/PIN required)

---

## 10. UX Flow Diagrams (Text-Based)

### Top-Up QR Flow

```
Start
  ↓
[Amount Entry] → Validation (min 10k, max 10M)
  ↓
[QR Generation] ← Network request (timeout: 10s)
  ↓
[QR Display] ← Timer: 15 min
  ├─ User scans from bank app
  ├─ User manual copy
  └─ User refreshes QR
  ↓
[Status Polling] ← Every 5s × 12, then 30s × 20
  ↓
[Success/Failure] ← Final confirmation
  ↓
End
```

### Booking Payment Flow

```
[Booking Review] (shows total amount)
  ↓
[Payment Method Selection]
  ├─ Wallet (if balance sufficient)
  ├─ Bank Transfer (VietQR)
  └─ Cash at Court
  ↓
[Confirmation Modal] (show selected method + amount)
  ↓
[Payment Processing] ← Backend call (3-5s typical)
  ↓
[Result Screen]
  ├─ Success → [Booking Details] → Dashboard
  ├─ Failure → [Retry] or [Change Method]
  └─ Pending → [Check Status Later]
```

### Insufficient Funds Handling

```
[Wallet Selected] → Balance check
  ↓
Balance < Required?
  ├─ YES → [Insufficient Alert]
  │         ├─ [Top Up] → Amount pre-filled → Top-up flow
  │         └─ [Change Method] → Back to selector
  └─ NO → Proceed to payment
```

---

## 11. Responsive Breakpoints

### Mobile-First (320px - 640px)
- Stack all payment methods vertically
- Full-width buttons (48px minimum height)
- Balance card: simplified (just amount + top-up button)
- QR code: full-screen modal or bottom sheet
- Transaction list: single column, icons + amount only

### Tablet (641px - 1024px)
- Two-column layout for wallet dashboard
- Balance card + quick actions (left), transactions (right)
- Payment method cards: side-by-side if screen > 800px
- QR display: modal with white background

### Desktop (1025px+)
- Three-column: Summary (20%) | Payment selector (35%) | Preview (45%)
- Balance card: larger, rounded corners (12px)
- Transaction table: multi-column (date, description, amount, status, actions)
- QR code: 300x300px minimum (scannable from arm's length)

---

## 12. Unresolved Questions

1. **Cashback rate:** What % cashback for court bookings? (e.g., 0.5%, 1%, tiered?)
2. **Wallet minimum balance:** Should we enforce minimum balance? (e.g., ₫ 10k threshold)
3. **Withdrawal flow:** Do users need withdrawal feature for Phase 1, or deferred to Phase 2?
4. **Biometric unlock:** Required for payments > ₫ 1M? (Follows State Bank guidance but adds friction)
5. **Referral incentive:** Should wallet top-ups include referral bonuses? (Drives viral growth)
6. **Subscription bundles:** Auto-debit for monthly passes? (Requires saved payment method + extra auth)
7. **Internationalization:** Support for expat users (USD/JPY/etc.), or VND-only MVP?
8. **Analytics:** Track wallet funnel metrics (top-up rate, payment success rate, abandonment)?

---

## 13. Sources

- [Mobile Wallet Design - Mobbin](https://mobbin.com/explore/mobile/screens/wallet-balance)
- [Transaction History UX - Dribbble](https://dribbble.com/tags/transaction-history)
- [UX Design of Challenger Banks - UXDA](https://theuxda.com/blog/ui-ux-design-of-challenger-bank)
- [MoMo Wallet Documentation - Adyen](https://docs.adyen.com/payment-methods/momo-wallet/)
- [ZaloPay Documentation - Nuvei](https://docs.nuvei.com/documentation/asia-pacific-guides/zalopay/)
- [ZaloPay Payment Flow - 2C2P](https://developer.2c2p.com/docs/sdk-method-zalopay)
- [Vietnam's Top Payment Methods - Transfi](https://www.transfi.com/blog/vietnams-top-payment-methods-momo-zalopay-vietqr-explained)
- [How to Accept Payments in Vietnam - Transfi](https://www.transfi.com/blog/how-to-accept-payments-in-vietnam-bank-transfer-wallets-and-vietqr)
- [GrabPay Wallet Overview - Antom](https://knowledge.antom.com/understanding-grabpay-wallet)
- [GrabPay Top-up Guide - Grab Singapore](https://www.grab.com/sg/pay/guide/top-up/)
- [Payment Method UX Best Practices - Baymard](https://baymard.com/blog/payment-method-selection)
- [Mobile Payment Design Examples - Mobbin](https://mobbin.com/explore/mobile/screens/payment-method)
- [Error Handling Best Practices - Maestro](https://maestro.dev/insights/error-handling-mobile-apps-best-practices)
- [Payment Confirmation UX - Stitch](https://stitch.money/blog/ui-ux-best-practices-for-linkpay)
- [Success Message Best Practices - Pencil & Paper](https://www.pencilandpaper.io/articles/success-ux)
- [Payment Gateway Design Best Practices - EnKash](https://www.enkash.com/resources/blog/best-practices-for-payment-gateway-ui-ux-design)
- [Vietnam Loyalty Programs Market 2025 - ResearchAndMarkets](https://www.businesswire.com/news/home/20250317287668/en/Vietnam-Loyalty-Programs-Intelligence-Report-2025-Market-to-Reach-$971.2-Million-by-2029-Fueled-by-Gamification-Digital-Payments-Integration-and-Data-Driven-Personalization---ResearchAndMarkets.com)
- [Loyalty Programs UX Checklist - Voucherify](https://www.voucherify.io/blog/loyalty-programs-ux-and-ui-best-practices)
- [Gamified Loyalty Programs - CleverTap](https://clevertap.com/blog/gamified-loyalty-programs/)
- [Digital Payments in Vietnam - Statista](https://www.statista.com/topics/9797/digital-payments-in-vietnam/)
- [Vietnam Mobile Payments Market - Mordor Intelligence](https://www.mordorintelligence.com/industry-reports/vietnam-mobile-payments-market)
- [Vietnam Digital Payment Market - IMARC](https://www.imarcgroup.com/vietnam-digital-payment-market)
- [Vietnam E-Payments & Mobile Wallets - Cimigo](https://www.cimigo.com/en/trends/vietnam-e-payments-and-mobile-wallets/)
- [Vietnam's Cashless Future - Antom](https://knowledge.antom.com/vietnams-cashless-future-urban-and-rural-opportunities)
- [Popular Payment Methods in Vietnam - 2C2P](https://2c2p.com/blog/vietnam-payment-methods)
- [Vietnam Leads Contactless Payments - Visa](https://www.visa.com.vn/en_VN/about-visa/newsroom/press-releases/vietnam-leads-surge-in-contactless-payments-across-asia-pacific-visa-survey-data.html)
- [Grab in Vietnam - Travel Vietnam](https://www.travelvietnam.com/traffic-transportation/grab-in-vietnam-uber-alternative-of-vietnam.html)
- [Digital Wallet Design Blueprint - Debut Infotech](https://www.debutinfotech.com/blog/digital-wallet-design-guide)
- [Digital Wallet Design Trends - RNDpoint](https://rndpoint.com/blog/digital-wallet-design-trends/)
- [UI/UX Best Practices for Stitch Pay - Stitch](https://stitch.money/blog/ui-ux-best-practices-for-stitch-pay-by-bank-manual-eft-and-cash)
- [Digital Wallets in Travel - AltexSoft](https://www.altexsoft.com/blog/digital-wallets-travel/)

