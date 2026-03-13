{{-- Alpine.js category inline editor -- included inside an x-data="tournamentForm(...)" scope --}}
<div class="td-form-section">
    <div class="td-section-title">Hạng mục thi đấu</div>
    <template x-for="(cat, index) in categories" :key="index">
        <div class="td-category-row">
            <input type="hidden" :name="'categories['+index+'][id]'" :value="cat.id ?? ''">
            <div class="td-category-fields">
                <div class="td-form-group" style="margin-bottom:0;">
                    <label class="td-label">Tên hạng mục</label>
                    <input type="text" :name="'categories['+index+'][category_name]'"
                           class="td-input" x-model="cat.category_name" required>
                </div>
                <div class="td-form-group" style="margin-bottom:0;">
                    <label class="td-label">Loại</label>
                    <select :name="'categories['+index+'][category_type]'" class="td-select"
                            x-model="cat.category_type">
                        <option value="single_men">Nam đơn</option>
                        <option value="single_women">Nữ đơn</option>
                        <option value="double_men">Nam đôi</option>
                        <option value="double_women">Nữ đôi</option>
                        <option value="double_mixed">Đôi hỗn hợp</option>
                    </select>
                </div>
                <div class="td-form-group" style="margin-bottom:0;">
                    <label class="td-label">Nhóm tuổi</label>
                    <input type="text" :name="'categories['+index+'][age_group]'"
                           class="td-input" x-model="cat.age_group" placeholder="VD: U30">
                </div>
                <div class="td-form-group" style="margin-bottom:0;">
                    <label class="td-label">Tối đa VĐV</label>
                    <input type="number" :name="'categories['+index+'][max_participants]'"
                           class="td-input" x-model="cat.max_participants" min="2">
                </div>
            </div>
            <button type="button" class="td-remove-btn" @click="removeCategory(index)"
                    title="Xóa hạng mục">&times;</button>
        </div>
    </template>
    <button type="button" class="td-add-category-btn" @click="addCategory()">
        + Thêm hạng mục
    </button>
</div>
