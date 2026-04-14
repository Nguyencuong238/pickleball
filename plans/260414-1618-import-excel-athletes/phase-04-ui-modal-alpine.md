# Phase 04: UI Modal + Alpine Methods

## Context Links
- Brainstorm: `../reports/brainstorm-260414-1618-import-excel-athletes.md` (Section 5)
- Partial: `resources/views/home-yard/tournaments/partials/_athletes.blade.php`
- Alpine: `public/assets/js/tournament-athletes.js`

## Overview
- **Priority:** High
- **Status:** complete
- **Description:** Thêm nút "Import Excel" cạnh nút "Thêm VĐV", modal upload, Alpine methods call 2 endpoints mới.

## Key Insights (REVISED)
- Alpine pattern: config destructured vào state fields trực tiếp (`storeUrl: config.storeUrl`) → import cũng phải `importUrl: config.importUrl`, dùng `this.importUrl` NOT `this.config.importUrl`
- Existing modal partial: `resources/views/home-yard/tournaments/partials/_athletes-modal.blade.php` → tạo partial riêng `_athletes-import-modal.blade.php` để consistent
- CSS classes có sẵn trong `public/assets/css/tournament-dashboard/components-athletes.css`: `at-modal-overlay`, `at-modal`, `at-modal-header`, `at-modal-title`, `at-modal-close`, `at-modal-body`, `at-modal-footer` (KHÔNG phải `at-modal-backdrop`)
- Existing state pattern: `showModal: false` cho add modal → dùng `showImportModal: false` cho import

## Requirements
- Nút "Import Excel" trong toolbar
- Modal với: download template button, file picker, submit button
- Loading state khi submit
- Error list display (scrollable) nếu server trả 422
- Success toast + reload athletes list
- Reset modal state sau success/close

## Architecture
```
_athletes.blade.php
├── Toolbar: thêm button "Import Excel"
└── Modal: clone structure add-athlete modal
    ├── Section 1: Download template (link GET)
    ├── Section 2: File input <input type="file" accept=".xlsx,.xls">
    ├── Section 3: Error list (x-show="importErrors.length > 0")
    └── Footer: Cancel + Submit

tournament-athletes.js (Alpine component)
├── state: importOpen, importFile, importLoading, importErrors, importSummary
├── openImportModal()
├── closeImportModal()
├── submitImport() — FormData POST with _token
└── refreshAthletes() — GET index wantsJson to reload list
```

## Related Code Files
**Create:**
- `resources/views/home-yard/tournaments/partials/_athletes-import-modal.blade.php`

**Modify:**
- `resources/views/home-yard/tournaments/partials/_athletes.blade.php` (config URLs, toolbar button, include import modal partial)
- `public/assets/js/tournament-athletes.js` (config fields + 4 methods + state)

## Implementation Steps

### 1. Pass new URLs vào Alpine config
Trong `_athletes.blade.php` line 24-32, thêm:
```blade
importUrl: '{{ route('tournament-manage.athletes.import', $tournament) }}',
importTemplateUrl: '{{ route('tournament-manage.athletes.import-template', $tournament) }}',
indexUrl: '{{ route('tournament-manage.athletes.index', $tournament) }}',
```

### 2. Thêm nút "Import Excel" trong toolbar
Sau line 49 (nút "Thêm vận động viên"):
```blade
<button class="td-btn td-btn-secondary" @click="openImportModal">
    Import Excel
</button>
```

### 3. Tạo partial riêng `_athletes-import-modal.blade.php`
File: `resources/views/home-yard/tournaments/partials/_athletes-import-modal.blade.php`
```blade
{{-- Import Excel Athletes Modal --}}
<div class="at-modal-overlay" x-show="showImportModal" x-cloak @click.self="closeImportModal">
    <div class="at-modal">
        <div class="at-modal-header">
            <span class="at-modal-title">Import VĐV từ Excel</span>
            <button class="at-modal-close" @click="closeImportModal">&times;</button>
        </div>
        <div class="at-modal-body">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">1. Tải file mẫu</label>
                <a :href="importTemplateUrl" class="td-btn td-btn-ghost" download>Tải template .xlsx</a>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">2. Chọn file Excel</label>
                <input type="file" accept=".xlsx,.xls"
                       @change="importFile = $event.target.files[0]">
            </div>
            <div x-show="importErrors.length > 0" x-cloak
                 style="max-height:240px;overflow-y:auto;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px;">
                <p style="font-weight:600;color:#b91c1c;margin:0 0 8px 0;">
                    Có <span x-text="importErrors.length"></span> lỗi:
                </p>
                <ul style="margin:0;padding-left:20px;color:#991b1b;font-size:0.9rem;">
                    <template x-for="(err, i) in importErrors" :key="i">
                        <li>
                            Row <span x-text="err.row"></span>:
                            <span x-text="err.message"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
        <div class="at-modal-footer">
            <button class="td-btn td-btn-ghost" @click="closeImportModal">Hủy</button>
            <button class="td-btn td-btn-primary"
                    :disabled="!importFile || importLoading"
                    @click="submitImport">
                <span x-show="!importLoading">Import</span>
                <span x-show="importLoading">Đang import...</span>
            </button>
        </div>
    </div>
</div>
```

Trong `_athletes.blade.php` include partial (ngoài x-data hoặc trong — cần nằm trong component scope để truy cập state):
```blade
@include('home-yard.tournaments.partials._athletes-import-modal')
```

### 4. Alpine state + methods trong `tournament-athletes.js`
Thêm config URLs vào state (match existing pattern — destructure config → fields):
```javascript
// Add after csrfToken line:
importUrl: config.importUrl,
importTemplateUrl: config.importTemplateUrl,
indexUrl: config.indexUrl,

// Add after existing "Add/edit modal" block:
// Import modal
showImportModal: false,
importFile: null,
importLoading: false,
importErrors: [],
```

Methods (dùng `this.importUrl` TRỰC TIẾP, không `this.config.xxx`):
```javascript
openImportModal() {
    this.showImportModal = true;
    this.importFile = null;
    this.importErrors = [];
},

closeImportModal() {
    this.showImportModal = false;
    this.importFile = null;
    this.importErrors = [];
    this.importLoading = false;
},

async submitImport() {
    if (!this.importFile) return;
    this.importLoading = true;
    this.importErrors = [];

    const formData = new FormData();
    formData.append('file', this.importFile);
    formData.append('_token', this.csrfToken);

    try {
        const res = await fetch(this.importUrl, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
        });
        const data = await res.json();

        if (res.status === 422 && data.errors) {
            this.importErrors = data.errors;
            return;
        }
        if (!res.ok) {
            alert(data.message || 'Import thất bại');
            return;
        }

        alert(data.message);
        await this.refreshAthletes();
        this.closeImportModal();
    } catch (e) {
        console.error(e);
        alert('Lỗi mạng, thử lại.');
    } finally {
        this.importLoading = false;
    }
},

async refreshAthletes() {
    const res = await fetch(this.indexUrl, {
        headers: { 'Accept': 'application/json' },
    });
    const data = await res.json();
    this.athletes = data.athletes || [];
    this.categories = data.categories || this.categories;
},
```

### 5. CSS
Reuse existing classes trong `public/assets/css/tournament-dashboard/components-athletes.css`:
`at-modal-overlay`, `at-modal`, `at-modal-header`, `at-modal-title`, `at-modal-close`, `at-modal-body`, `at-modal-footer`, `td-btn`, `td-btn-primary`, `td-btn-ghost`, `td-btn-secondary`.
Custom styles cho error list và step labels đã inline trong markup (Phase 04 step 3) → không cần add CSS mới.

## Todo List
- [x] Update `_athletes.blade.php` config pass 3 URLs mới
- [x] Add "Import Excel" button trong toolbar
- [x] Add modal markup
- [x] Add Alpine state fields
- [x] Implement `openImportModal()` + `closeImportModal()`
- [x] Implement `submitImport()` với FormData + error display
- [x] Implement `refreshAthletes()` reload list sau success
- [x] Add CSS classes cần thiết (nếu chưa có)
- [x] Browser test: mở modal, upload file, xem error/success

## Success Criteria
- Click "Import Excel" → modal mở
- Download template → browser download file .xlsx
- Upload file valid → toast success, list reload với VĐV mới
- Upload file invalid → error list hiển thị, modal không đóng
- Cancel / click outside → modal đóng, state reset
- Loading state khi submit (button disabled)

## Risk Assessment
- **Risk:** CSS class không có sẵn → modal xấu. **Mitigation:** Reuse classes từ add-athlete modal có sẵn
- **Risk:** `refreshAthletes()` race condition. **Mitigation:** Await trước khi close modal
- **Risk:** File input không reset sau success → user re-upload nhầm. **Mitigation:** `closeImportModal()` set `importFile = null` và reset DOM input

## Security Considerations
- CSRF token từ `config.csrfToken` (passed from blade meta tag)
- Accept header JSON để Laravel trả JSON thay vì HTML redirect

## Next Steps
- Depends on: Phase 03 (routes tồn tại)
- Blocks: Phase 06 (test)
