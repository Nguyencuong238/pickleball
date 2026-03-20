{{-- Add / Edit Athlete Modal --}}
<div class="at-modal-overlay" x-show="showModal" x-cloak @click.self="closeModal">
    <div class="at-modal">
        <div class="at-modal-header">
            <span class="at-modal-title" x-text="editMode ? 'Chỉnh sửa vận động viên' : 'Thêm vận động viên'"></span>
            <button class="at-modal-close" @click="closeModal">&#215;</button>
        </div>

        <form class="at-modal-body" @submit.prevent="submitForm">
            {{-- Tìm kiếm người dùng --}}
            <div class="td-form-group" x-show="!editMode" style="margin-bottom:16px;">
                <label class="td-label">Tìm người dùng</label>
                <div style="position:relative;">
                    <input type="text"
                           class="td-input"
                           placeholder="Nhập email hoặc số điện thoại để tìm..."
                           x-model="userSearchQuery"
                           @input.debounce.400ms="searchUser"
                           @keydown.escape="clearUserSearch">
                    <div x-show="userSearchLoading" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:0.8rem;color:#94a3b8;">
                        Đang tìm...
                    </div>
                </div>
                <div x-show="userSearchResults.length > 0"
                     style="border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;max-height:200px;overflow-y:auto;background:#fff;">
                    <template x-for="user in userSearchResults" :key="user.id">
                        <div @click="selectUser(user)"
                             style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9;"
                             class="at-search-result-item">
                            <div style="font-weight:500;" x-text="user.name"></div>
                            <div style="font-size:0.8rem;color:#64748b;">
                                <span x-text="user.email"></span>
                                <span x-show="user.phone"> - <span x-text="user.phone"></span></span>
                            </div>
                        </div>
                    </template>
                </div>
                <div x-show="userSearchQuery.length >= 3 && !userSearchLoading && userSearchResults.length === 0 && userSearchDone"
                     style="font-size:0.8rem;color:#94a3b8;margin-top:4px;">
                    Không tìm thấy người dùng nào.
                </div>
            </div>

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
