# Phase 2: Admin Top-up Management

## Overview
- **Priority**: P1
- **Status**: Complete
- **Effort**: 2.5h
- Trang admin de xem danh sach, duyet/tu choi cac yeu cau nap Gems

## Key Insights
- Follow pattern cua PointSubmissionController (index, show, approve, reject)
- Admin layout: `@extends('admin.layouts.app')` (extends `layouts.app`)
- Sidebar nav: them link trong `resources/views/layouts/app.blade.php` line ~494
- Routes: under `Route::middleware(['auth', 'role:admin'])->prefix('admin')` group

## Related Code Files

### Create
- `app/Http/Controllers/Admin/GemTopupController.php`
- `resources/views/admin/gem-topups/index.blade.php`

### Modify
- `routes/web.php` - them admin gem-topup routes
- `resources/views/layouts/app.blade.php` - them sidebar nav link

## Architecture

### Routes
```php
// Inside admin middleware group
Route::prefix('gem-topups')->name('gem-topups.')->group(function () {
    Route::get('/', [GemTopupController::class, 'index'])->name('index');
    Route::post('/{transaction}/approve', [GemTopupController::class, 'approve'])->name('approve');
    Route::post('/{transaction}/reject', [GemTopupController::class, 'reject'])->name('reject');
});
```

### Admin Controller (~60 lines)

```php
class GemTopupController extends Controller
{
    // index() - list pending + recent completed/cancelled
    //   - filter by status (pending/completed/cancelled)
    //   - paginate 20
    //   - show: user name, email, amount VND, gems, transfer content, created_at, status

    // approve(GemTransaction $transaction) - call confirmTopUp()
    //   - redirect back with success

    // reject(GemTransaction $transaction) - call cancelTopUp()
    //   - redirect back with success
}
```

### Admin View (~120 lines)
Table-based view with:
- Stats cards: pending count, total approved today, total gems issued
- Table columns: ID | User | So tien (VND) | Gems | Noi dung CK | Thoi gian | Trang thai | Hanh dong
- Status filter tabs: Tat ca | Cho duyet | Da duyet | Tu choi
- Approve/Reject buttons (only for pending)
- Pending count badge in sidebar

## Implementation Steps

### 1. Create GemTopupController
- Constructor: inject GemWalletService
- `index(Request $request)`: query GemTransaction where type=top_up, filter by status, paginate, load user relation
- `approve(GemTransaction $transaction)`: verify pending+top_up, call confirmTopUp(), redirect with flash
- `reject(GemTransaction $transaction)`: verify pending+top_up, call cancelTopUp(), redirect with flash

### 2. Create admin/gem-topups/index.blade.php
- Extends `admin.layouts.app`
- Stats row: 3 cards (pending, approved today, total gems)
- Filter tabs: tat ca / cho duyet / da duyet / tu choi
- Table with columns: #, User, So tien VND, Gems, Noi dung CK (GEMS{userId}T{txId}), Thoi gian, Trang thai badge, Actions
- Approve button (green) + Reject button (red) cho pending rows
- Pagination

### 3. Add routes to web.php
Them vao admin middleware group, sau point-tasks routes

### 4. Add sidebar nav link
Them link "Nap Gems" voi pending count badge (giong Submissions) vao sau Point Tasks link

## Todo List
- [x] Create GemTopupController with index/approve/reject
- [x] Create admin/gem-topups/index.blade.php
- [x] Add routes to admin group in web.php
- [x] Add sidebar nav link with pending badge

## Success Criteria
- Admin thay duoc danh sach tat ca yeu cau nap
- Filter theo trang thai hoat dong
- Duyet -> Gems duoc cong vao vi user
- Tu choi -> Transaction chuyen sang cancelled
- Badge hien thi so luong pending trong sidebar
- Khong anh huong SePay webhook flow hien tai
