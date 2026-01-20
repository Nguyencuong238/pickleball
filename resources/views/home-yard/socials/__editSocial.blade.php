<div class="modal-content">
    <form id="editSocialForm" method="POST" enctype="multipart/form-data"
        action="{{ route('homeyard.socials.update', $social->id) }}">
        @csrf
        @method('PUT')

        <div class="modal-header"
            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-bottom: none;">
            <h3 class="modal-title" style="color: white; margin: 0;">Chỉnh Sửa Giải Đấu</h3>
            <button type="button" class="modal-close" style="color: white;" onclick="closeEditModal()">×</button>
        </div>
        <div class="modal-body" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <div class="form-group">
                        <label class="form-label">Tên *</label>
                        <input type="text" class="form-input" name="name" value="{{ $social->name }}"
                            placeholder="VD: Giải Pickleball Mở Rộng" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sân *</label>
                        <select class="form-select" name="stadium_id" required>
                            <option value="">Chọn sân</option>
                            @if (isset($stadiums))
                                @foreach ($stadiums as $stadium)
                                    <option value="{{ $stadium->id }}" {{ $stadium->id == $social->stadium_id ? 'selected' : '' }}>{{ $stadium->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ngày thi đấu *</label>
                        <input type="date" class="form-input" name="date" value="{{ $social->date }}" required>
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Giờ bắt đầu *</label>
                            <input type="time" class="form-input" name="start_time" value="{{ $social->start_time }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Giờ kết thúc *</label>
                            <input type="time" class="form-input" name="end_time" value="{{ $social->end_time }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ngày trong tuần</label>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" class="day-checkbox selectAllDays" value="all"
                                    style="cursor: pointer;">
                                <span>Chọn tất cả</span>
                            </label>
                            <span style="width: 100%; height: 1px; background: var(--border-color);"></span>
                            @php
                                $days = [
                                    '2' => 'Thứ 2',
                                    '3' => 'Thứ 3',
                                    '4' => 'Thứ 4',
                                    '5' => 'Thứ 5',
                                    '6' => 'Thứ 6',
                                    '7' => 'Thứ 7',
                                    '1' => 'Chủ nhật',
                                ];
                            @endphp
                            @foreach ($days as $dayNum => $dayName)
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input type="checkbox" class="day-checkbox" name="days_of_week[]"
                                        value="{{ $dayNum }}" style="cursor: pointer;" {{ in_array($dayNum, $social->days_of_week) ? 'checked' : '' }}>
                                    <span>{{ $dayName }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Đối tượng *</label>
                             @php
                                $levels = [
                                    'beginner' => 'Người mới',
                                    'intermediate' => 'Trung cấp',
                                    'advanced' => 'Nâng cao',
                                ];
                            @endphp
                            <select class="form-select" name="object">
                                <option value="">Chọn</option>
                                @foreach ($levels as $key => $value)
                                    <option value="{{ $key }}" {{ $social->object == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số người tối đa</label>
                            <input type="number" class="form-input" name="max_participants" placeholder="64"
                                min="1" value="{{ $social->max_participants }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phí tham gia (VNĐ)</label>
                        <input type="number" class="form-input" name="fee" placeholder="0" min="0"
                            step="0.01" value="{{ $social->fee }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-input" name="description" placeholder="Nhập mô tả sự kiện..." rows="3">{{ $social->description }}</textarea>
                    </div>
                </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
            <button type="submit" class="btn btn-primary">💾 Lưu Thay Đổi</button>
        </div>

    </form>
</div>
