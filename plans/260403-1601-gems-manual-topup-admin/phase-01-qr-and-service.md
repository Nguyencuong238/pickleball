# Phase 1: VietQR + Config + Service Updates

## Overview
- **Priority**: P1
- **Status**: Complete
- **Effort**: 1.5h
- Doi QR tu SePay sang VietQR, them bank config, update service de ho tro ca 2 mode (auto SePay + manual admin)

## Key Insights
- VietQR URL format: `https://img.vietqr.io/image/{bankBin}-{accountNumber}-compact.png?amount={amount}&addInfo={description}&accountName={name}`
- Bank BIN codes: VCB=970436, TCB=970407, MB=970422, ACB=970416, TPB=970423, VPB=970432
- Khong can API key, hoan toan mien phi
- SepayService::buildQrUrl() can duoc thay the/fallback

## Related Code Files

### Modify
- `config/gems.php` - them bank_account_number, bank_bin, bank_name, account_name
- `.env.example` - them GEMS_BANK_* vars
- `app/Services/SepayService.php` - doi buildQrUrl() sang VietQR, giu webhook handler
- `app/Services/GemWalletService.php` - them cancelTopUp() method

## Implementation Steps

### 1. Update config/gems.php
Them bank config:
```php
'bank' => [
    'account_number' => env('GEMS_BANK_ACCOUNT_NUMBER'),
    'bin' => env('GEMS_BANK_BIN'),
    'name' => env('GEMS_BANK_NAME', ''),
    'account_name' => env('GEMS_BANK_ACCOUNT_NAME', ''),
],
```

### 2. Update .env.example
```
GEMS_BANK_ACCOUNT_NUMBER=
GEMS_BANK_BIN=
GEMS_BANK_NAME=
GEMS_BANK_ACCOUNT_NAME=
```

### 3. Update SepayService::buildQrUrl()
Doi sang VietQR:
```php
public function buildQrUrl(int $amountVnd, string $description): string
{
    $bin = config('gems.bank.bin');
    $accountNumber = config('gems.bank.account_number');
    $accountName = config('gems.bank.account_name');

    // Fallback to SePay if sepay config exists
    if (config('gems.sepay.account_number')) {
        $params = http_build_query([...]);
        return "https://qr.sepay.vn/img?{$params}";
    }

    // VietQR (free, no API key)
    $params = http_build_query([
        'amount' => $amountVnd,
        'addInfo' => $description,
        'accountName' => $accountName,
    ]);
    return "https://img.vietqr.io/image/{$bin}-{$accountNumber}-compact.png?{$params}";
}
```

### 4. Add cancelTopUp() to GemWalletService
```php
public function cancelTopUp(GemTransaction $transaction): void
{
    if ($transaction->status !== 'pending' || $transaction->type !== 'top_up') {
        return;
    }
    $transaction->update(['status' => 'cancelled']);
}
```

### 5. Update user-facing topup modal
Them hien thi thong tin ngan hang (ten NH, STK, ten TK) de user co the chuyen thu cong neu QR khong scan duoc.

## Todo List
- [x] Update config/gems.php with bank config
- [x] Update .env.example with GEMS_BANK_* vars
- [x] Update SepayService::buildQrUrl() -> VietQR with SePay fallback
- [x] Add cancelTopUp() to GemWalletService
- [x] Update topup modal to show bank details

## Success Criteria
- QR image loads from VietQR khi khong co SePay config
- QR image loads from SePay khi co SePay config
- Bank info hien thi duoi QR code
- cancelTopUp() hoat dong dung
