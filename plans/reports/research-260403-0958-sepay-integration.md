---
name: SePay VietQR Integration Research
description: Technical research on SePay API, VietQR, webhook implementation, and Laravel integration patterns for Gems wallet top-up feature
type: research
date: 2026-04-03
---

# SePay VietQR Integration - Technical Research Report

## Executive Summary

SePay is a Vietnamese payment gateway providing direct bank-to-bank transfers via VietQR standard. Integration flow: User requests top-up → SePay generates VietQR → user pays via bank app → SePay webhook confirms → Gems credited. Key consideration: SePay sends webhooks directly (no signature verification required by default) but supports multiple auth methods. Transaction matching uses transfer content description field + optional payment code detection.

## 1. VietQR Standard Overview

**What is VietQR?**
- National QR payment standard developed by NAPAS (National Payment Corporation Vietnam) and State Bank of Vietnam
- Unified format allowing all Vietnamese banks & wallets to accept QR payments via same standard
- Instant bank-to-bank transfers: free, real-time settlement, no intermediaries

**How it works:**
1. QR code contains merchant bank account (account number, bank code, amount)
2. User scans with Vietnamese bank app (any bank)
3. User confirms payment details
4. Transaction processes as direct bank transfer
5. Merchant receives payment instantly

**VietQR Compatibility:**
- Works across all major Vietnamese banks (Vietcombank, Agribank, BIDV, Techcombank, etc.)
- Supported by digital wallets (MoMo, ZaloPay integration possible)
- Mobile-first design (most users pay via bank mobile app)
- No transaction fees for merchant

**Reference:** [VietQR Overview](https://www.transfi.com/blog/vietnams-top-payment-methods-momo-zalopay-vietqr-explained)

---

## 2. SePay API Endpoints

### QR Code Generation
**Endpoint:** `https://qr.sepay.vn/img`

**Parameters:**
- `accountNumber` - Merchant bank account
- `bankCode` - Bank identifier (e.g., "vietcombank")
- `amount` - Transfer amount (VND, optional - user can enter)
- `description` - Transfer content/note (e.g., "GEMS_TOP_UP_USER123")
- `template` - QR template style (optional)

**Response:** Direct image file (PNG/SVG QR code)

**Example URL:**
```
https://qr.sepay.vn/img?accountNumber=1234567890
&bankCode=vietcombank
&amount=100000
&description=GEMS_TOP_UP_USR001
```

### Transaction Detail API (Optional - Direct Query)
**Endpoint:** `https://api.sepay.vn/transactions/{id}`

**Use Case:** Verify transaction status without waiting for webhook

**Authentication:** API Key in header `Authorization: Apikey YOUR_API_KEY`

**Reference:** [SePay Dev Documentation](https://developer.sepay.vn/en/bankhub/api/api-giao-dich/chi-tiet-giao-dich)

---

## 3. Webhook Implementation

### Webhook Payload Structure

SePay sends **POST request with JSON body** when bank transfer occurs:

```json
{
  "id": "507f1f77bcf86cd799439011",
  "gateway": "Vietcombank",
  "transactionDate": "2026-04-03T14:30:00+07:00",
  "accountNumber": "0123456789",
  "code": "GEMS_TOP_UP_USR001",
  "content": "GEMS_TOP_UP_USR001",
  "transferType": "in",
  "transferAmount": 100000,
  "accumulated": 5000000,
  "subAccount": null,
  "referenceCode": "REF123456789",
  "description": "GEMS_TOP_UP_USR001 - Bank SMS detail"
}
```

**Key Fields:**
- `id` - Unique SePay transaction ID (use for deduplication)
- `content` - Transfer description from bank (for payment code matching)
- `transferType` - "in" (deposit) or "out" (withdrawal)
- `transferAmount` - Amount in VND
- `transactionDate` - When bank recorded transaction
- `referenceCode` - Bank reference (from SMS)
- `gateway` - Sender's bank name

### Webhook Response Requirements

**OAuth 2.0 Auth:** Response HTTP 201 with body `{"success": true}`

**API Key or No Auth:** Response HTTP 200 or 201 with body `{"success": true}`

**Critical:** SePay retries failed webhooks 7 times using Fibonacci intervals over ~5 hours
- Connection timeout: 5 seconds
- Response timeout: 8 seconds

### Authentication Methods (Select One)

1. **No Authentication** - Webhook sent without auth headers (rely on IP whitelist)
2. **API Key** - SePay sends header `Authorization: Apikey {YOUR_API_KEY}`
3. **OAuth 2.0** - Bearer token in header

**SePay IP Whitelist for Incoming Webhooks:**
- 172.236.138.20
- 172.233.83.68
- 171.244.35.2
- 151.158.108.68
- 151.158.109.79
- 103.255.238.139

**Reference:** [SePay Webhook Documentation](https://developer.sepay.vn/en/sepay-webhooks/tich-hop-webhook)

---

## 4. Transaction Matching Strategy

### Payment Code Detection (Recommended)

SePay supports automatic payment code extraction from transfer content using regex patterns.

**Configure in SePay Dashboard:**
1. Company → General Settings → Payment Code Structure
2. Define regex pattern: `GEMS_TOP_UP_(\w+)` (matches "GEMS_TOP_UP_USR001")
3. Enable "Ignore if no payment code found" to reject transfers without code

**In Webhook:**
- SePay extracts payment code into `code` field
- Use `code` to match against pending top-up requests

### Content Field Matching (Fallback)

If regex not available, parse `content` field directly:

```php
// Example
preg_match('/GEMS_TOP_UP_(\w+)/', $webhook['content'], $matches);
$userId = $matches[1]; // Extract user ID
```

### Deduplication (CRITICAL)

Combine multiple fields for uniqueness:

```php
$uniqueKey = $webhook['id'] . '_' . 
             $webhook['referenceCode'] . '_' . 
             $webhook['transferAmount'];
// Check if already processed in database
```

**Why multiple fields?** SePay retries webhooks; same `id` within 5-hour window = duplicate.

### Matching Workflow

```
Webhook arrives with content: "GEMS_TOP_UP_USR001"
                ↓
1. Extract user_id from content/code field
2. Find pending top-up request for user_id with matching amount
3. Check deduplication (id + referenceCode + amount)
4. If dedup exists, return success (idempotent)
5. If new, credit gems, mark top-up complete
6. Respond {"success": true}
```

---

## 5. Webhook Signature Verification

**Important Note:** SePay's default webhook setup does NOT use HMAC-SHA256 signatures like Stripe/Paddle. Instead, it relies on:

1. **IP Whitelisting** (primary) - Only whitelist SePay's 6 IP addresses
2. **API Key Header** - If enabled, SePay sends `Authorization: Apikey` header
3. **OAuth 2.0 Token** - Stored in Authorization header

**Recommendation for Laravel:**

Use **API Key authentication** + IP whitelist:

1. **Configure API Key in SePay Dashboard:**
   - Generate webhook API key
   - Store in `.env`: `SEPAY_WEBHOOK_TOKEN=your_api_key`

2. **Middleware to verify:**

```php
// app/Http/Middleware/VerifySePayWebhook.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerifySePayWebhook
{
    public function handle(Request $request, Closure $next)
    {
        // Check IP whitelist
        $allowedIps = [
            '172.236.138.20', '172.233.83.68', '171.244.35.2',
            '151.158.108.68', '151.158.109.79', '103.255.238.139'
        ];
        
        if (!in_array($request->ip(), $allowedIps)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        // Check API Key if enabled
        $apiKey = $request->header('Authorization');
        if ($apiKey !== 'Apikey ' . config('sepay.webhook_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return $next($request);
    }
}
```

---

## 6. Laravel Integration Architecture

### Recommended Stack

- **Package:** `sepayvn/laravel-sepay` (official Laravel package)
- **Queue:** Laravel Job for async processing
- **Storage:** Database table for webhook logs (deduplication)
- **Events:** Laravel Event/Listener for domain logic separation

### Installation & Setup

```bash
composer require sepayvn/laravel-sepay

# Publish config & migrations
php artisan vendor:publish --tag="sepay-migrations"
php artisan vendor:publish --tag="sepay-config"
php artisan migrate
```

**Configuration (.env):**
```env
SEPAY_WEBHOOK_TOKEN=your_api_key
SEPAY_MATCH_PATTERN=GEMS_TOP_UP_
```

**Configuration (config/sepay.php):**
```php
return [
    'webhook_token' => env('SEPAY_WEBHOOK_TOKEN'),
    'match_pattern' => env('SEPAY_MATCH_PATTERN', 'SE'),
    'webhook_url' => env('SEPAY_WEBHOOK_URL', '/api/webhook/sepay'),
];
```

### Webhook Controller

```php
// app/Http/Controllers/SePay/WebhookController.php
namespace App\Http\Controllers\SePay;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Events\GemTopUpCompleted;
use App\Jobs\ProcessSePayWebhook;
use Illuminate\Support\Facades\Log;

class WebhookController
{
    public function handle(Request $request): Response
    {
        $payload = $request->json()->all();
        
        // Log for debugging
        Log::info('SePay webhook received', [
            'id' => $payload['id'],
            'amount' => $payload['transferAmount'],
            'content' => $payload['content'],
        ]);
        
        // Respond immediately (don't block bank timeout)
        dispatch(new ProcessSePayWebhook($payload));
        
        return response()->json(['success' => true], 200);
    }
}
```

### Job for Async Processing

```php
// app/Jobs/ProcessSePayWebhook.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\GemTopUp;
use App\Events\GemTopUpCompleted;
use Illuminate\Support\Facades\DB;

class ProcessSePayWebhook implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    protected array $payload;
    
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
    
    public function handle(): void
    {
        // 1. Deduplication check
        $exists = DB::table('sepay_webhooks')->where([
            'transaction_id' => $this->payload['id'],
            'reference_code' => $this->payload['referenceCode'],
            'amount' => $this->payload['transferAmount'],
        ])->exists();
        
        if ($exists) {
            return; // Already processed
        }
        
        // 2. Extract user ID from content/code
        $pattern = config('sepay.match_pattern');
        preg_match("/{$pattern}(\w+)/i", $this->payload['content'], $matches);
        
        if (empty($matches[1])) {
            \Log::warning('SePay: No payment code found', $this->payload);
            return;
        }
        
        $userId = $matches[1];
        
        // 3. Find pending top-up request
        $topUp = GemTopUp::where([
            ['user_id', '=', $userId],
            ['amount', '=', $this->payload['transferAmount']],
            ['status', '=', 'pending'],
        ])->orderBy('created_at', 'desc')->first();
        
        if (!$topUp) {
            \Log::warning('SePay: No pending top-up found', [
                'user_id' => $userId,
                'amount' => $this->payload['transferAmount'],
            ]);
            return;
        }
        
        // 4. Credit gems (within transaction)
        DB::transaction(function () use ($topUp) {
            $topUp->update([
                'status' => 'completed',
                'sepay_transaction_id' => $this->payload['id'],
                'completed_at' => now(),
            ]);
            
            $topUp->user->increment('gems', $topUp->gem_amount);
            
            // Log webhook processing
            DB::table('sepay_webhooks')->insert([
                'transaction_id' => $this->payload['id'],
                'reference_code' => $this->payload['referenceCode'],
                'amount' => $this->payload['transferAmount'],
                'top_up_id' => $topUp->id,
                'processed_at' => now(),
            ]);
            
            event(new GemTopUpCompleted($topUp));
        });
    }
}
```

### Routes Setup

```php
// routes/api.php
Route::post('/webhook/sepay', 
    App\Http\Controllers\SePay\WebhookController::class . '@handle'
)->middleware('sepay.webhook')->name('sepay.webhook');
```

### Database Schema

```php
// Migration: create_gem_top_ups_table
Schema::create('gem_top_ups', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->integer('amount'); // VND
    $table->integer('gem_amount'); // Gems credited
    $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
    $table->string('sepay_transaction_id')->nullable()->unique();
    $table->string('qr_code_url')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});

// Migration: create_sepay_webhooks_table (deduplication log)
Schema::create('sepay_webhooks', function (Blueprint $table) {
    $table->id();
    $table->string('transaction_id')->unique();
    $table->string('reference_code');
    $table->integer('amount');
    $table->foreignId('top_up_id')->nullable()->constrained('gem_top_ups')->onDelete('set null');
    $table->timestamp('processed_at');
    $table->timestamps();
    
    $table->index(['transaction_id', 'reference_code']);
});
```

---

## 7. Security Considerations

### 1. IP Whitelisting (Essential)
- Always validate webhook source IP against SePay's 6 whitelist IPs
- Reject any webhook from unknown IPs
- Update whitelist if SePay publishes new IPs

### 2. Webhook Signature Verification
- If using API Key auth: verify `Authorization` header matches `SEPAY_WEBHOOK_TOKEN`
- Never hardcode credentials in code
- Rotate API key periodically

### 3. Idempotency (Critical for Retries)
- Store webhook deduplication key in database with unique constraint
- Use composite key: `(transaction_id, reference_code, transfer_amount)`
- Always check before crediting gems

### 4. Amount Validation
- Verify webhook amount matches expected top-up amount ±0 VND (exact match)
- Log mismatches for investigation
- Don't auto-correct amounts

### 5. Rate Limiting
- Limit webhook endpoint to burst rate (e.g., 100 requests/minute)
- SePay retries over 5 hours, so short bursts expected
- Prevent DoS via webhook endpoint

```php
// Middleware rate limiting
Route::post('/webhook/sepay', ...)->middleware(['throttle:100,1']);
```

### 6. Async Processing
- Always respond immediately to webhook (< 8 seconds)
- Use queued job for heavy logic (database updates, user notification)
- Don't block webhook handler with external API calls

### 7. Logging & Monitoring
- Log all webhook arrivals (for debugging failed transactions)
- Alert on repeated failures for same user
- Monitor queue job failures

### 8. User Input Validation
- Never trust `content` field format (user might typo description)
- Validate extracted user ID exists
- Implement fallback: if no code found, require manual verification

---

## 8. Recommended Implementation Approach

### Phase 1: Setup (1-2 days)
1. Create SePay merchant account + configure webhook
2. Install `sepayvn/laravel-sepay` package
3. Create migrations for `gem_top_ups` and `sepay_webhooks` tables
4. Setup webhook middleware (IP whitelist + API key check)

### Phase 2: API Integration (2-3 days)
1. Build `GemTopUpController` with QR generation endpoint:
   - Generate payment code: `GEMS_TOP_UP_{USER_ID}`
   - Request QR from SePay API
   - Store pending top-up request in DB
   - Return QR URL to frontend

2. Create webhook handler:
   - Receive SePay webhook
   - Respond immediately
   - Queue async processing job

3. Build async job:
   - Deduplication check
   - Extract user ID from content
   - Match to pending request
   - Credit gems atomically

### Phase 3: Frontend (2 days)
1. Top-up request form (amount input)
2. QR display modal with countdown timer
3. Success/failure notifications
4. Manual verification fallback (if webhook fails after timeout)

### Phase 4: Testing & Monitoring (2-3 days)
1. Test with SePay sandbox
2. Simulate webhook failures & retries
3. Monitor queue job failures
4. Set up alerts for payment mismatches

---

## 9. Implementation Checklist

- [ ] SePay merchant account created
- [ ] Webhook URL configured in SePay dashboard
- [ ] API credentials stored in `.env`
- [ ] `sepayvn/laravel-sepay` package installed
- [ ] Migrations created & run
- [ ] Webhook middleware implemented (IP + API Key)
- [ ] Webhook controller responding with 200 + `{"success": true}`
- [ ] Async job queued for processing
- [ ] Deduplication logic tested
- [ ] Amount validation in place
- [ ] User notification on completion
- [ ] Error handling for mismatched payments
- [ ] Queue worker running in production
- [ ] Monitoring/alerting setup
- [ ] SePay sandbox testing completed
- [ ] Rate limiting on webhook endpoint

---

## 10. Unresolved Questions / Further Investigation

1. **OAuth 2.0 Flow** - If using OAuth instead of API Key, need SePay's token endpoint details (not documented in search results)
2. **Exact HMAC Verification** - Confirm SePay doesn't support HMAC-SHA256 signature header (unlike Stripe/Paddle)
3. **VietQR ISO Format** - Detailed ISO 20022 QR code structure if manual QR generation needed (vs. using SePay's endpoint)
4. **Webhook Retry Logic** - Exact Fibonacci intervals SePay uses for retries (documentation shows "up to 7 attempts over 5 hours" but exact schedule unclear)
5. **Currency Conversion** - If supporting multi-currency top-ups, need exchange rate API integration details
6. **Refund Handling** - How to process refunds if user requests (callback to SePay needed?)
7. **Reconciliation** - Best practice for daily reconciliation with bank statements
8. **PCI Compliance** - If storing bank account details locally, need PCI DSS assessment

---

## References

- [SePay Create Webhooks Documentation](https://developer.sepay.vn/en/sepay-webhooks/tich-hop-webhook)
- [SePay Payment Gateway Integration](https://sepay.vn/lap-trinh-cong-thanh-toan.html)
- [Laravel SePay Package](https://github.com/sepayvn/laravel-sepay)
- [VietQR Standard Overview](https://www.transfi.com/blog/vietnams-top-payment-methods-momo-zalopay-vietqr-explained)
- [VietQR API Documentation](https://api.vietqr.vn/en)
- [SePay Webhook Implementation Guide (Vietnamese)](https://docs.sepay.vn/lap-trinh-webhooks.html)
- [Laravel Webhook Security Patterns](https://github.com/spatie/laravel-webhook-client)
- [HMAC-SHA256 Webhook Verification Guide](https://hookdeck.com/webhooks/guides/how-to-implement-sha256-webhook-signature-verification)
