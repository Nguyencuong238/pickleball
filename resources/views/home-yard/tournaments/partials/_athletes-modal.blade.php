{{-- Add / Edit Athlete Modal --}}
<div class="at-modal-overlay" x-show="showModal" x-cloak @click.self="closeModal">
    <div class="at-modal">
        <div class="at-modal-header">
            <span class="at-modal-title" x-text="editMode ? 'Chỉnh sửa vận động viên' : 'Thêm vận động viên'"></span>
            <button class="at-modal-close" @click="closeModal">&#215;</button>
        </div>

        <form class="at-modal-body" @submit.prevent="submitForm">
            {{-- Name --}}
            <div class="td-form-group">
                <label class="td-label">Họ và tên <span style="color:#ef4444">*</span></label>
                <input type="text"
                       class="td-input"
                       :class="{ 'is-invalid': formErrors.athlete_name }"
                       x-model="form.athlete_name"
                       placeholder="Nguyễn Văn A"
                       required>
                <span class="td-error" x-show="formErrors.athlete_name" x-text="formErrors.athlete_name && formErrors.athlete_name[0]"></span>
            </div>

            {{-- Email --}}
            <div class="td-form-group">
                <label class="td-label">Email <span style="color:#ef4444">*</span></label>
                <input type="email"
                       class="td-input"
                       :class="{ 'is-invalid': formErrors.email }"
                       x-model="form.email"
                       placeholder="email@example.com"
                       required>
                <span class="td-error" x-show="formErrors.email" x-text="formErrors.email && formErrors.email[0]"></span>
            </div>

            {{-- Phone --}}
            <div class="td-form-group">
                <label class="td-label">Số điện thoại <span style="color:#ef4444">*</span></label>
                <input type="text"
                       class="td-input"
                       :class="{ 'is-invalid': formErrors.phone }"
                       x-model="form.phone"
                       placeholder="0901234567"
                       required>
                <span class="td-error" x-show="formErrors.phone" x-text="formErrors.phone && formErrors.phone[0]"></span>
            </div>

            {{-- Category --}}
            <div class="td-form-group" x-show="!editMode">
                <label class="td-label">Nội dung thi đấu <span style="color:#ef4444">*</span></label>
                <select class="td-input"
                        :class="{ 'is-invalid': formErrors.category_id }"
                        x-model="form.category_id">
                    <option value="">-- Chọn nội dung --</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.category_name"></option>
                    </template>
                </select>
                <span class="td-error" x-show="formErrors.category_id" x-text="formErrors.category_id && formErrors.category_id[0]"></span>
            </div>

            {{-- Partner (doubles only) --}}
            <div class="td-form-group" x-show="selectedCategoryIsDoubles">
                <label class="td-label">Đối tác (đôi)</label>
                <select class="td-input" x-model="form.partner_id">
                    <option value="">-- Chọn đối tác (tùy chọn) --</option>
                    <template x-for="p in availablePartners" :key="p.id">
                        <option :value="p.id" x-text="p.athlete_name + ' (' + p.email + ')'"></option>
                    </template>
                </select>
                <div style="font-size:0.78rem;color:#64748b;margin-top:4px;">
                    Chỉ hiển thị vận động viên chưa có đối tác trong cùng nội dung.
                </div>
            </div>
        </form>

        <div class="at-modal-footer">
            <button type="button" class="td-btn td-btn-ghost" @click="closeModal">Hủy</button>
            <button type="button"
                    class="td-btn td-btn-primary"
                    :disabled="submitting"
                    @click="submitForm">
                <span x-show="submitting">Đang xử lý...</span>
                <span x-show="!submitting" x-text="editMode ? 'Lưu thay đổi' : 'Thêm vào giải'"></span>
            </button>
        </div>
    </div>
</div>
