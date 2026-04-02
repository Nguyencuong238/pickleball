@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi Xác Thực:</strong>
        <ul style="margin-bottom: 0; margin-top: 10px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <form method="POST"
        action="{{ isset($stadium) ? route('admin.stadiums.update', $stadium) : route('admin.stadiums.store') }}"
        enctype="multipart/form-data">
        @csrf
        @if (isset($stadium))
            @method('PUT')
        @endif

        <!-- Row 1: Name and Status -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Name -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tên Sân *</label>
                <input type="text" name="name" class="form-control" value="{{ $stadium->name ?? old('name') }}"
                    required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Giờ Hoạt
                    Động</label>
                <input type="text" name="opening_hours" class="form-control"
                    value="{{ old('opening_hours', $stadium->opening_hours) }}" placeholder="VD: 06:00 - 22:00"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Row 2: Address -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Địa Chỉ *</label>
                <input type="text" name="address" class="form-control"
                    value="{{ $stadium->address ?? old('address') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tỉnh/Thành
                    Phố</label>
                <select name="province_id" class="form-control"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="">-- Chọn Tỉnh/Thành Phố --</option>
                    @forelse ($provinces as $province)
                        <option value="{{ $province->id }}"
                            {{ old('province_id', $stadium->province_id) == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
                        </option>
                    @empty
                        <option value="">Không có tỉnh/thành phố</option>
                    @endforelse
                </select>
            </div>
        </div>

        <!-- Row 3: Contact Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Điện Thoại</label>
                <input type="tel" name="phone" class="form-control" value="{{ $stadium->phone ?? old('phone') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Email</label>
                <input type="email" name="email" class="form-control" value="{{ $stadium->email ?? old('email') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mô Tả</label>
            <textarea name="description" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $stadium->description ?? old('description') }}</textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mặt Sân</label>
            <input type="text" name="court_surface" class="form-control"
                value="{{ $stadium->court_surface ?? old('court_surface') }}" placeholder="VD: Acrylic chuyên dụng"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Regulations -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Quy Định Sân</label>
            <textarea name="regulations" class="form-control" rows="6"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $stadium->regulations ?? old('regulations') }}</textarea>
            <small style="color: #64748b; margin-top: 4px; display: block;">Nhập các quy định chi tiết của sân</small>
        </div>

        <!-- Maps Link -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Google Maps Link</label>
            <input type="url" name="maps_link" class="form-control"
                value="{{ $stadium->maps_link ?? old('maps_link') }}" placeholder="https://maps.app.goo.gl/..."
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            <small style="color: #64748b; margin-top: 4px; display: block;">Nhập đường dẫn Google Maps của sân</small>
        </div>

        <!-- Utilities -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tiện Ích </label>

            <div class="filter-options d-flex flex-column flex-wrap gap-2">
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
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Banner Image</label>
            @include('components.media-uploader', [
                'model' => $stadium,
                'collection' => 'banner',
                'name' => 'banner',
                'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                'maxItems' => 1,
            ])
        </div>

        <!-- Gallery Images -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Gallery Images</label>
            @include('components.media-uploader', [
                'model' => $stadium,
                'collection' => 'gallery',
                'name' => 'gallery',
                'rules' => 'JPG, JPEG, SVG, PNG, WebP',
            ])
        </div>


        <!-- Rating, Featured, Verified -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">

            <div style="display: flex; align-items: flex-end;">
                <label
                    style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" name="status" value="1"
                        {{ (isset($stadium) && $stadium->status) || old('status') ? 'checked' : '' }}
                        style="cursor: pointer;">
                    Hoạt Động
                </label>
            </div>

            <div style="display: flex; align-items: flex-end;">
                <label
                    style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1"
                        {{ (isset($stadium) && $stadium->is_featured) || old('is_featured') ? 'checked' : '' }}
                        style="cursor: pointer;">
                    Nổi Bật
                </label>
            </div>

            <div style="display: flex; align-items: flex-end;">
                <label
                    style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" name="is_verified" value="1"
                        {{ (isset($stadium) && $stadium->is_verified) || old('is_verified') ? 'checked' : '' }}
                        style="cursor: pointer;">
                    Đã Xác Minh
                </label>
            </div>
        </div>

        <!-- Buttons -->
        <div
            style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('admin.stadiums.index') }}" class="btn"
                style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">Hủy</a>
            <button type="submit" class="btn"
                style="background-color: #006646; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                {{ isset($stadium) ? 'Cập Nhật Sân' : 'Tạo Sân Mới' }}
            </button>
        </div>
    </form>
</div>

<script>
    // Track marked for deletion
    const deletedMediaIds = new Set();

    document.querySelectorAll('.btn-mark-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const mediaId = this.dataset.id;
            const mediaElement = document.getElementById(`media-${mediaId}`);

            if (deletedMediaIds.has(mediaId)) {
                // Undo deletion
                deletedMediaIds.delete(mediaId);
                mediaElement.style.opacity = '1';
                mediaElement.style.pointerEvents = 'auto';
                this.textContent = '✕ Xóa';
                this.style.backgroundColor = 'rgba(220, 38, 38, 0.9)';
            } else {
                // Mark for deletion
                deletedMediaIds.add(mediaId);
                mediaElement.style.display = 'none';
            }

            // Update hidden input
            document.getElementById('deleted_media_ids').value = Array.from(deletedMediaIds).join(',');
        });
    });
</script>
