# SePay Integration Research Report

**Date:** 2026-04-06  
**Researcher:** Claude Code  
**Project:** Pickleball Gems Wallet

---

## Executive Summary

SePay is a Vietnamese payment gateway with official SDKs for Laravel/PHP. The integration focuses on:
1. QR code generation for bank transfers (via `qr.sepay.vn`)
2. Webhook handling for payment confirmation
3. API authentication via Bearer token
4. Official Laravel package available for simplified integration

All parameter names and authentication formats are confirmed via official SePay documentation.

---

## 1. QR Code Generation (`qr.sepay.vn`)

### Correct Parameter Names

**Endpoint:** `https://qr.sepay.vn/img`

**Required Parameters:**
- `acc` - Bank account number (NOT `accountNumber`)
- `bank` - Bank name (NOT `bankCode`)

**Optional Parameters:**
- `amount` - Transfer amount
- `des` - Transfer description/content (NOT `description`)

### Example URL

```
https://qr.sepay.vn/img?acc=0010000000355&bank=Vietcombank&amount=100000&des=ung ho quy bao tro tre em
```

### Bank Code Reference

Valid bank names/codes are available at: `https://qr.sepay.vn/banks.json`

### Usage in HTML

```html
<img src="https://qr.sepay.vn/img?acc={{account}}&bank={{bankName}}&amount={{amount}}&des={{description}}" alt="QR Code" />
```

---

## 2. Webhook Implementation

### Webhook Response Format

**Success response:**
```json
{ "success": true }
```

**Error response:**
```json
{ "success": false, "message": "error description" }
```

Response must be JSON format.

### Authentication Format

SePay webhook uses **API Key authentication** via header:

```
Authorization: Apikey YOUR_API_KEY
```

**Format:** `Apikey` (not `Bearer`) followed by space and the API key.

### Webhook Payload Structure

SePay sends POST request with JSON body containing:

```json
{
  "id": 92704,
  "gateway": "Vietcombank",
  "transactionDate": "2023-03-25 14:02:37",
  "accountNumber": "0123499999",
  "subAccount": null,
  "transferType": "in",
  "transferAmount": 100000,
  "accumulated": 500000,
  "code": null,
  "content": "ung ho quy bao tro tre em",
  "referenceCode": "BK12345",
  "description": "optional description"
}
```

**Key fields:**
- `id` - Unique transaction ID (validate for duplicates on retries)
- `gateway` - Bank name
- `transactionDate` - Timestamp
- `accountNumber` - Receiving account
- `transferType` - "in" (incoming) or "out" (outgoing)
- `transferAmount` - Amount in VND
- `content` - Transaction description/reference

### IP Whitelist (If Using IP Authentication)

If you use IP-based authentication, whitelist these SePay IP addresses:

- `172.236.138.20`
- `172.233.83.68`
- `171.244.35.2`
- `151.158.108.68`
- `151.158.109.79`
- `103.255.238.139`

**Note:** SePay recommends API Key authentication over IP-only whitelisting for better security.

### Duplicate Transaction Prevention

Validate uniqueness using combination of:
- `id` field (primary)
- `referenceCode` + `transferType` + `transferAmount` (fallback)

---

## 3. API Authentication

### Request Format

```
Authorization: Bearer YOUR_API_TOKEN
```

**Format:** `Bearer` (with capital B) followed by space and API token.

### API Limits

- **Rate Limit:** 3 calls per second
- **Exceeding limit:** Returns HTTP 429 Too Many Requests
- **Retry header:** `x-sepay-userapi-retry-after: {seconds}` indicates wait time before retry

### Token Creation

Create API token via SePay dashboard at:
https://docs.sepay.vn/tao-api-token.html

---

## 4. Official Laravel Package

### Package Name & Installation

```bash
composer require "sepayvn/laravel-sepay:dev-lite"
```

**GitHub:** https://github.com/sepayvn/laravel-sepay

### Configuration

Create `.env` variables:
```env
SEPAY_WEBHOOK_TOKEN=your_random_webhook_token
SEPAY_MATCH_PATTERN=SE
```

Publish config:
```bash
php artisan vendor:publish --provider="SePayVN\LaravelSePay\SePayServiceProvider"
```

### Webhook Listener

Generate listener:
```bash
php artisan make:listener SePayWebhookListener
```

Example handler:
```php
<?php

namespace App\Listeners;

use SePayVN\LaravelSePay\Events\SePayWebhookEvent;

class SePayWebhookListener
{
    public function handle(SePayWebhookEvent $event)
    {
        $data = $event->sePayWebhookData;
        
        if ($data->transferType === 'in') {
            // Process incoming payment
            // $data->transferAmount, $data->accountNumber, etc.
        }
    }
}
```

### Event Registration (Laravel < 11)

In `EventServiceProvider`:
```php
protected $listen = [
    SePayWebhookEvent::class => [
        SePayWebhookListener::class,
    ],
];
```

**Laravel 11+:** Automatically discovered if listener is in `app/Listeners/`

### Webhook Route

Package registers route:
```
POST /api/sepay/webhook
```

No need to manually define webhook route.

---

## 5. Additional SePay Packages

### SePay BankHub (Laravel)

```bash
composer require sepayvn/laravel-sepay-bankhub
```

**Configuration:**
```env
SEPAY_API_KEY=your_api_key
SEPAY_API_SECRET=your_secret
SEPAY_API_URL=https://partner-api.sepay.vn/merchant/v1
```

### SePay PG SDK (PHP)

For payment gateway checkout:
```bash
composer require sepay/sepay-pg
```

**Timeout config:** 60 seconds default
**Retry config:** 5 attempts, 2000ms delay
**Exceptions:** AuthenticationException, ValidationException, NotFoundException, RateLimitException, ServerException

---

## 6. Official Documentation Links

| Resource | URL |
|----------|-----|
| API Introduction | https://docs.sepay.vn/gioi-thieu-api.html |
| API Transactions | https://docs.sepay.vn/api-giao-dich.html |
| Virtual Account API | https://docs.sepay.vn/api-va-theo-don-hang.html |
| API Token Creation | https://docs.sepay.vn/tao-api-token.html |
| QR Code Generation | https://docs.sepay.vn/tao-qr-code-vietqr-dong.html |
| Webhook Programming | https://docs.sepay.vn/lap-trinh-webhooks.html |
| Webhook Integration | https://docs.sepay.vn/tich-trong-webhooks.html |
| OAuth2 Webhooks | https://docs.sepay.vn/oauth2/api-webhooks.html |

---

## 7. Key Implementation Decisions

### Authentication Choice

**Recommendation:** Use API Key (`Authorization: Apikey YOUR_KEY`) for webhooks.

**Rationale:**
- More secure than IP-only whitelisting
- Easier to rotate credentials
- SePay official recommendation

### Duplicate Prevention

Implement idempotency by checking `id` field + timestamp before processing webhook.

### Error Handling

Always return JSON response (success/error) before closing webhook endpoint. Do NOT rely on HTTP status codes alone.

### Rate Limiting Strategy

Implement exponential backoff when receiving 429 status, using `x-sepay-userapi-retry-after` header.

---

## 8. Answered Key Questions

| Question | Answer |
|----------|--------|
| QR param names | `acc`, `bank`, `amount` (NOT `accountNumber`/`bankCode`/`description`) |
| Webhook auth format | `Authorization: Apikey KEY_VALUE` (capital A, NOT Bearer) |
| Webhook payload | JSON with `id`, `gateway`, `transactionDate`, `accountNumber`, `transferType`, `transferAmount`, `content` fields |
| Laravel package | Yes: `sepayvn/laravel-sepay` via GitHub |
| Webhook IPs | 6 IP addresses provided for whitelist (but API Key auth preferred) |

---

## 9. Summary for Implementation

1. **QR Code Generation:** Use `qr.sepay.vn/img?acc=X&bank=Y&amount=Z&des=D` with correct parameter names
2. **Webhook Receiver:** Implement JSON response with success/error, validate `id` for duplicates
3. **Authentication:** Use `Authorization: Apikey YOUR_KEY` header for webhooks
4. **Laravel Integration:** Use official `sepayvn/laravel-sepay` package for automatic webhook handling
5. **Rate Limits:** Handle 429 responses with exponential backoff using retry header
6. **Validation:** Combine `id` + `transactionDate` + `transferAmount` for idempotency checks

---

## Sources

- [SePay API Introduction](https://docs.sepay.vn/gioi-thieu-api.html)
- [SePay QR Code Generation](https://docs.sepay.vn/tao-qr-code-vietqr-dong.html)
- [SePay Webhook Programming](https://docs.sepay.vn/lap-trinh-webhooks.html)
- [SePay Webhook Integration](https://docs.sepay.vn/tich-trong-webhooks.html)
- [SePay Laravel Package](https://github.com/sepayvn/laravel-sepay)
- [SePay Official GitHub](https://github.com/sepayvn)
- [SePay Developer Portal](https://developer.sepay.vn/)
