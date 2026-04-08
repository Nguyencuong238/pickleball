# Phase 2: Backend Service Logic

## Muc tieu
Sua `ClubActivityService` de xu ly thanh toan Gems khi RSVP va hoan tien khi huy.

## Thay doi chinh

### ClubActivityService::rsvp()
File: `app/Services/ClubActivityService.php`

Logic moi (trong DB::transaction):
1. Check skill level, check existing (giu nguyen)
2. Xac dinh status: confirmed / waitlisted (giu nguyen)
3. **NEW**: Neu `confirmed` VA `activity->hasFee()`:
   - Goi `GemWalletService::deduct($user, $activity->fee_gems, ClubActivity::class, $activity->id, "Phi tham gia: {$activity->title}")`
   - Neu insufficient balance -> throw RuntimeException (se catch o controller)
   - Goi `GemCashbackService::award($gemTx)` de cong Point
4. Tao/update participant (giu nguyen)
5. **Luu gem_transaction_id vao participant** (de truy vet khi hoan)

### ClubActivityService::cancelRsvp()
Logic moi:
1. Tim participant (giu nguyen)
2. **NEW**: Neu `wasConfirmed` VA `activity->hasFee()` VA `activity->activity_date > now()`:
   - Tim GemTransaction theo reference (ClubActivity, activity_id) + user_id
   - Goi `GemWalletService::refund($gemTx)` de hoan Gems
3. Update status cancelled (giu nguyen)
4. promoteFromWaitlist (giu nguyen)

### ClubActivityService::promoteFromWaitlist()
Logic moi:
1. Tim next waitlisted user (giu nguyen)
2. **NEW**: Neu `activity->hasFee()`:
   - Thu Gems tu waitlisted user
   - Neu insufficient balance -> skip user nay, thu promote nguoi tiep theo
   - Goi cashback
3. Update status confirmed (giu nguyen)

### ClubActivityService::checkinByPhone()
Logic moi:
1. Auto-add to club (giu nguyen)
2. Check existing participant (giu nguyen)
3. **NEW**: Neu user CHUA co participant (chua RSVP truoc) VA `activity->hasFee()`:
   - Thu Gems truoc khi tao participant
   - Neu insufficient balance -> throw RuntimeException
   - Goi cashback
4. **NEW**: Neu user DA co participant confirmed (da RSVP + da thu phi) -> chi check-in (giu nguyen)
5. Tao/update participant voi checked_in_at (giu nguyen)

**Luu y**: Check-in la PUBLIC (khong auth). User moi (register qua phone) se khong co GemWallet.
-> Xu ly: Neu activity co fee va user chua co wallet/khong du Gems -> throw RuntimeException.
   Controller se hien thi "Can nap Gems truoc khi check-in" + link dang nhap/nap Gems.
   User moi tao qua register flow se khong the check-in activity co phi cho den khi nap Gems.

### ClubActivityService::cancelRsvp() - bo sung
**Return type**: doi tu `void` -> `array` tra ve `['gems_refunded' => int]`
- Controller can biet so Gems hoan de tra ve frontend
- Neu user status = `waitlisted` -> chi update cancelled, KHONG hoan (vi chua thu phi) -> return `['gems_refunded' => 0]`
- Neu user status = `confirmed` -> hoan neu chua bat dau -> return `['gems_refunded' => $amount]`

### ClubActivityService::promoteFromWaitlist() - bo sung loop
Logic moi (thay vi chi lay 1 nguoi):
```
while (con cho trong && con nguoi waitlist) {
    $next = lay nguoi dau waitlist
    if (activity khong co fee) {
        confirm ngay -> break
    }
    try {
        thu Gems -> confirm -> cashback -> break
    } catch (insufficient) {
        cancel nguoi nay (khong du Gems)
        // TODO: thong bao user "Ban mat cho vi khong du Gems"
        continue // thu nguoi tiep
    }
}
```

### ClubActivityParticipant model
- Them `gem_transaction_id` nullable vao fillable (optional, de trace)

### Khoa fee khi da co nguoi dang ky
File: `app/Models/ClubActivity.php`
- Them method `isFeeEditable(): bool` -> `return $this->confirmedParticipants()->count() === 0;`
- Dung trong controller validation: neu `!isFeeEditable()` va fee_gems thay doi -> reject

## Migration bo sung (gop vao Phase 1 neu chua chay)
```php
$table->unsignedBigInteger('gem_transaction_id')->nullable()->after('waitlist_position');
$table->foreign('gem_transaction_id')->references('id')->on('gem_transactions')->nullOnDelete();
```

## Todo
- [ ] Update rsvp() - thu Gems khi confirmed + activity co fee
- [ ] Update cancelRsvp() - hoan Gems neu confirmed + chua bat dau, skip neu waitlisted
- [ ] Update promoteFromWaitlist() - loop thu Gems, skip + cancel neu khong du
- [ ] Update checkinByPhone() - thu Gems khi check-in truc tiep (chua RSVP truoc)
- [ ] Them isFeeEditable() vao ClubActivity model
- [ ] Them gem_transaction_id vao ClubActivityParticipant
