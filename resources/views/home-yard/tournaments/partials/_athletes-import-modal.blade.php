{{-- Import Excel Athletes Modal --}}
<div class="at-modal-overlay" x-show="showImportModal" x-cloak @click.self="closeImportModal">
    <div class="at-modal">
        <div class="at-modal-header">
            <span class="at-modal-title">Import vận động viên từ Excel</span>
            <button class="at-modal-close" @click="closeImportModal" type="button">&times;</button>
        </div>
        <div class="at-modal-body">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">1. Tải file mẫu</label>
                <a :href="importTemplateUrl" class="td-btn td-btn-ghost">Tải template .xlsx</a>
                <p style="font-size:0.85rem;color:#64748b;margin:6px 0 0 0;">
                    File mẫu có sẵn danh sách hạng mục của giải đấu.
                </p>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">2. Chọn file Excel (.xlsx, .xls — tối đa 2MB, 500 dòng)</label>
                <input type="file"
                       accept=".xlsx,.xls"
                       x-ref="importFileInput"
                       @change="importFile = $event.target.files[0] || null">
                <p x-show="importFile" x-cloak style="font-size:0.85rem;color:#16a34a;margin:6px 0 0 0;">
                    Đã chọn: <span x-text="importFile?.name"></span>
                </p>
            </div>
            <div x-show="importErrors.length > 0" x-cloak
                 style="max-height:240px;overflow-y:auto;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px;">
                <p style="font-weight:600;color:#b91c1c;margin:0 0 8px 0;">
                    Phát hiện <span x-text="importErrors.length"></span> lỗi — file không được import:
                </p>
                <ul style="margin:0;padding-left:20px;color:#991b1b;font-size:0.9rem;">
                    <template x-for="(err, i) in importErrors" :key="i">
                        <li>
                            Dòng <strong x-text="err.row"></strong>:
                            <span x-text="err.message"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
        <div class="at-modal-footer">
            <button class="td-btn td-btn-ghost" @click="closeImportModal" type="button">Hủy</button>
            <button class="td-btn td-btn-primary"
                    type="button"
                    :disabled="!importFile || importLoading"
                    @click="submitImport">
                <span x-show="!importLoading">Import</span>
                <span x-show="importLoading" x-cloak>Đang import...</span>
            </button>
        </div>
    </div>
</div>
