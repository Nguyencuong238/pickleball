<div class="card fade-in">
    <div class="card-header">
        <h3 class="card-title">{{ isset($stadium->id) ? '✏️ Chỉnh Sửa Sân' : '➕ Tạo Sân Mới' }}</h3>
    </div>
    <div class="card-body">
        <form method="POST"
            action="{{ isset($stadium->id) ? route('homeyard.stadiums.update', $stadium) : route('homeyard.stadiums.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($stadium->id)
                @method('PUT')
            @endif

            <!-- Hàng 1: Tên Sân và Trạng Thái -->
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Tên Sân *</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $stadium->name) }}"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label">Giờ Hoạt Động</label>
                    <input type="text" name="opening_hours" class="form-input"
                        value="{{ old('opening_hours', $stadium->opening_hours) }}" placeholder="VD: 06:00 - 22:00">
                </div>
            </div>


            <!-- Contact Info -->
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Địa Chỉ *</label>
                    <input type="text" name="address" class="form-input"
                        value="{{ old('address', $stadium->address) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Điện Thoại</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', $stadium->phone) }}">
                </div>
            </div>

            <div class="grid grid-2">

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $stadium->email) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="text" name="website" class="form-input"
                        value="{{ old('website', $stadium->website) }}">
                </div>
            </div>


            <!-- Mô Tả -->
            <div class="form-group">
                <label class="form-label">Mô Tả</label>
                <textarea name="description" class="form-textarea" rows="4">{{ old('description', $stadium->description) }}</textarea>
            </div>

            <!-- Court Surface, Hours -->
            <div class="form-group">
                <label class="form-label">Mặt Sân</label>
                <input type="text" name="court_surface" class="form-input"
                    value="{{ old('court_surface', $stadium->court_surface) }}" placeholder="VD: Acrylic chuyên dụng">
            </div>

            <!-- Regulations -->
            <div class="form-group">
                <label class="form-label">Quy Định Sân</label>
                <textarea name="regulations" class="form-textarea" rows="6">{{ old('regulations', $stadium->regulations) }}</textarea>
                <small style="color: #64748b; margin-top: 8px; display: block;">Nhập các quy định chi tiết của
                    sân</small>
            </div>

            <!-- Utilities (JSON array) -->
            <div class="form-group">
                <label class="form-label">Tiện Ích (Nhập mỗi cái trên một dòng)</label>
                <div class="filter-options" style="display: flex; flex-direction: column;gap:10px;">
                    <label class="filter-checkbox">
                        <input type="checkbox" name="amenities[1]" @if (@$stadium->amenities[1]) checked @endif
                            value="🅿️ Bãi đỗ xe">
                        <span class="checkbox-custom"></span>
                        <span>🅿️ Bãi đỗ xe</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="amenities[2]" @if (@$stadium->amenities[2]) checked @endif
                            value="🚿 Phòng tắm">
                        <span class="checkbox-custom"></span>
                        <span>🚿 Phòng tắm</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="amenities[3]" @if (@$stadium->amenities[3]) checked @endif
                            value="☕ Canteen">
                        <span class="checkbox-custom"></span>
                        <span>☕ Canteen</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="amenities[]4" @if (@$stadium->amenities[4]) checked @endif
                            value="🏪 Cửa hàng">
                        <span class="checkbox-custom"></span>
                        <span>🏪 Cửa hàng</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="amenities[5]" @if (@$stadium->amenities[5]) checked @endif
                            value="❄️ Điều hòa">
                        <span class="checkbox-custom"></span>
                        <span>❄️ Điều hòa</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="amenities[6]" @if (@$stadium->amenities[6]) checked @endif
                            value="🎾 Cho thuê vợt">
                        <span class="checkbox-custom"></span>
                        <span>🎾 Cho thuê vợt</span>
                    </label>
                </div>
            </div>

            <!-- Banner Image -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">
                    Banner Image
                </label>
                @include('components.media-uploader', [
                    'model' => $stadium,
                    'collection' => 'banner',
                    'name' => 'banner',
                    'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                    'maxItems' => 1,
                ])
            </div>

            <!-- Gallery Images Component -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">
                    Gallery Images
                </label>
                @include('components.media-uploader', [
                    'model' => $stadium,
                    'collection' => 'gallery',
                    'name' => 'gallery',
                    'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                ])
            </div>

            <!-- Buttons -->
            <div
                style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('homeyard.stadiums.index') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($stadium) ? '💾 Cập Nhật Sân' : '➕ Tạo Sân Mới' }}
                </button>
            </div>
        </form>
    </div>
</div>
