# Phase 3: API & Controllers

## Context
- Depends on: Phase 2 (services)
- [System Architecture](../../docs/system-architecture.md)

## Overview
- **Priority**: P1
- **Status**: Pending
- **Effort**: 4h
- Create API endpoints for wallet operations + SePay webhook route

## Key Insights
- API controllers in `app/Http/Controllers/Api/`
- Auth via JWT (php-open-source-saver/jwt-auth) + Sanctum
- Existing pattern: Api/PointController for points endpoints
- Webhook route must be public (no auth), but IP-restricted via middleware

## Requirements

### Functional
- GET wallet balance + recent transactions
- POST top-up request -> return QR URL
- POST webhook (SePay callback)
- GET transaction history (paginated)
- GET single transaction detail

### Non-Functional
- Webhook responds < 8 seconds
- Pagination: 15 items per page default
- JSON responses follow existing API conventions

## Architecture

### Routes

```php
// routes/api.php
Route::middleware('auth:api')->prefix('gems')->group(function () {
    Route::get('/wallet', [GemController::class, 'wallet']);
    Route::post('/topup', [GemController::class, 'topUp']);
    Route::get('/transactions', [GemController::class, 'transactions']);
    Route::get('/transactions/{transaction}', [GemController::class, 'transactionDetail']);
});

// routes/web.php (or api.php without auth)
Route::post('/webhook/sepay', [SepayWebhookController::class, 'handle'])
    ->middleware('verify.sepay.webhook');
```

## Related Code Files

### Create
- `app/Http/Controllers/Api/GemController.php`
- `app/Http/Controllers/Api/SepayWebhookController.php`

### Modify
- `routes/api.php` - add gems routes
- `routes/web.php` - add webhook route (exempt from CSRF)
- `app/Http/Kernel.php` - register verify.sepay.webhook middleware
- `app/Http/Middleware/VerifyCsrfToken.php` - exclude webhook URL

## Implementation Steps

### GemController (~100 lines)

1. `wallet()` - GET /api/gems/wallet
   ```php
   // Return balance, formatted balance, exchange rate info
   $wallet = $this->gemWalletService->getOrCreateWallet(auth()->user());
   $recentTx = auth()->user()->gemTransactions()
       ->completed()->latest()->take(5)->get();
   return response()->json([
       'balance' => $wallet->balance,
       'balance_vnd' => $wallet->balance * config('gems.exchange_rate'),
       'exchange_rate' => config('gems.exchange_rate'),
       'recent_transactions' => $recentTx,
   ]);
   ```

2. `topUp(Request $request)` - POST /api/gems/topup
   ```php
   // Validate amount_vnd (integer, min/max from config)
   // Call GemWalletService::createTopUpRequest
   // Return QR URL + transaction info + expiry (15 min)
   ```

3. `transactions(Request $request)` - GET /api/gems/transactions
   ```php
   // Paginated list, optional filter by type/status
   // Return with balance summary
   ```

4. `transactionDetail(GemTransaction $transaction)` - GET /api/gems/transactions/{id}
   ```php
   // Verify ownership (user_id check)
   // Return full transaction detail with reference info
   ```

### SepayWebhookController (~30 lines)

1. `handle(Request $request)` - POST /webhook/sepay
   ```php
   // Middleware already verified IP + API key
   // Validate payload structure
   // Call SepayService::handleWebhook
   // Return 200 OK (always, even on error - log instead)
   // SePay retries on non-200
   ```

### Route Registration

1. Add gem routes to `routes/api.php` under auth:api middleware
2. Add webhook route to `routes/web.php`
3. Register `verify.sepay.webhook` alias in Kernel.php routeMiddleware
4. Add `/webhook/sepay` to VerifyCsrfToken $except array

## Todo List
- [ ] Create GemController with wallet/topUp/transactions/transactionDetail
- [ ] Create SepayWebhookController with handle
- [ ] Add routes to api.php and web.php
- [ ] Register middleware alias in Kernel.php
- [ ] Add CSRF exception for webhook
- [ ] Test endpoints with Postman/curl

## Success Criteria
- All endpoints return correct JSON structure
- Auth-protected endpoints require valid JWT
- Webhook accepts SePay payload and credits Gems
- Webhook returns 200 even on processing errors (logged)
- Pagination works on transaction list

## Risk Assessment
- Webhook must always return 200 quickly - use try/catch, log errors
- Transaction detail must verify ownership to prevent data leak

## Security Considerations
- JWT auth on all gem endpoints
- IP + API key on webhook
- CSRF exemption only for webhook URL
- User can only see own transactions (scope by auth user)
