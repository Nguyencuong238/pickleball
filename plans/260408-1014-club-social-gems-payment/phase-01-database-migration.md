# Phase 1: Database Migration

## Muc tieu
Them truong phi Gems vao bang `club_activities`.

## Migration

File: `database/migrations/2026_04_08_add_fee_gems_to_club_activities_table.php`

```php
Schema::table('club_activities', function (Blueprint $table) {
    $table->unsignedInteger('fee_gems')->nullable()->after('max_participants');
});
```

## Chi tiet
- `fee_gems` (unsigned int, nullable): so Gems can thanh toan. null/0 = mien phi
- Khong can truong `fee_amount_vnd` rieng - frontend tinh tu `fee_gems * exchange_rate`
- Khong can truong `refund_policy` - logic don gian: hoan neu chua bat dau

## Model update
File: `app/Models/ClubActivity.php`
- Them `fee_gems` vao `$fillable`
- Them `fee_gems` vao `$casts` (integer)
- Them helper: `hasFee(): bool` -> `return $this->fee_gems > 0;`

## Recurring instance
File: `app/Services/ClubActivityService.php` -> `createRecurringInstance()`
- Them `'fee_gems' => $template->fee_gems` vao array create

## Todo
- [ ] Tao migration file
- [ ] Update ClubActivity model ($fillable, $casts, hasFee())
- [ ] Update createRecurringInstance() copy fee_gems
- [ ] Chay `php artisan migrate`
