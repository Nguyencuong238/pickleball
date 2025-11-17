@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi Xác Thực:</strong>
        <ul style="margin-bottom: 0; margin-top: 10px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ isset($stadium) ? route('admin.stadiums.update', $stadium) : route('admin.stadiums.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($stadium))
            @method('PUT')
        @endif

        <!-- Row 1: Name and Status -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Name -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tên Sân *</label>
                <input type="text" name="name" class="form-control" value="{{ $stadium->name ?? old('name') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <!-- Status -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Trạng Thái *</label>
                <select name="status" class="form-control" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="active" {{ (isset($stadium) && $stadium->status === 'active') || old('status') === 'active' ? 'selected' : '' }}>Hoạt Động</option>
                    <option value="inactive" {{ (isset($stadium) && $stadium->status === 'inactive') || old('status') === 'inactive' ? 'selected' : '' }}>Không Hoạt Động</option>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mô Tả</label>
            <textarea name="description" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $stadium->description ?? old('description') }}</textarea>
        </div>

        <!-- Row 2: Address -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Địa Chỉ *</label>
            <input type="text" name="address" class="form-control" value="{{ $stadium->address ?? old('address') }}" required
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Row 3: Contact Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
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

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Website</label>
                <input type="url" name="website" class="form-control" value="{{ $stadium->website ?? old('website') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Row 4: Courts Count, Court Surface, Hours -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1.5fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Số Sân *</label>
                <input type="number" name="courts_count" class="form-control" value="{{ $stadium->courts_count ?? old('courts_count', 1) }}" min="1" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mặt Sân</label>
                <input type="text" name="court_surface" class="form-control" value="{{ $stadium->court_surface ?? old('court_surface') }}" placeholder="VD: Acrylic chuyên dụng"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Giờ Hoạt Động</label>
                <input type="text" name="opening_hours" class="form-control" value="{{ $stadium->opening_hours ?? old('opening_hours') }}" placeholder="VD: 06:00 - 22:00"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Rating, Featured, Verified -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Đánh Giá (Số Sao)</label>
                <input type="number" name="rating" class="form-control" value="{{ $stadium->rating ?? old('rating', 0) }}" min="0" max="5" step="0.1"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;" placeholder="0-5">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Số Lượng Đánh Giá</label>
                <input type="number" name="rating_count" class="form-control" value="{{ $stadium->rating_count ?? old('rating_count', 0) }}" min="0"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Trạng Thái Nổi Bật</label>
                <select name="featured_status" class="form-control"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="normal" {{ (isset($stadium) && $stadium->featured_status === 'normal') || old('featured_status') === 'normal' ? 'selected' : '' }}>Bình Thường</option>
                    <option value="featured" {{ (isset($stadium) && $stadium->featured_status === 'featured') || old('featured_status') === 'featured' ? 'selected' : '' }}>Nổi Bật</option>
                </select>
            </div>

            <div style="display: flex; align-items: flex-end;">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" name="verified" value="1" {{ (isset($stadium) && $stadium->verified) || old('verified') ? 'checked' : '' }}
                        style="width: 18px; height: 18px; cursor: pointer;">
                    Đã Xác Minh
                </label>
            </div>
        </div>

        <!-- Utilities -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tiện Ích (Nhập mỗi cái trên một dòng)</label>
            <textarea name="utilities" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;" placeholder="VD:&#10;🏟️ 8 Sân thi đấu&#10;🚿 Phòng tắm&#10;🅿️ Bãi đỗ xe">@if(isset($stadium) && $stadium->utilities){{ implode("\n", $stadium->utilities) }}@else{{ old('utilities') }}@endif</textarea>
            <small style="color: #64748b; margin-top: 4px; display: block;">Mỗi dòng sẽ được lưu như một mục tiện ích</small>
        </div>

        <!-- Regulations -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Quy Định Sân</label>
            <textarea name="regulations" class="form-control" rows="6"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $stadium->regulations ?? old('regulations') }}</textarea>
            <small style="color: #64748b; margin-top: 4px; display: block;">Nhập các quy định chi tiết của sân</small>
        </div>

        <!-- Image -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ảnh Sân</label>
            @if(isset($stadium) && $stadium->image)
                <div style="margin-bottom: 10px;">
                    <img src="{{ asset('storage/' . $stadium->image) }}" alt="{{ $stadium->name }}" style="max-width: 200px; max-height: 150px; border-radius: 6px;">
                </div>
            @endif
            <input type="file" name="image" class="form-control" accept="image/*"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Buttons -->
        <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('admin.stadiums.index') }}" class="btn" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">Hủy</a>
            <button type="submit" class="btn" style="background-color: #00D9B5; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                {{ isset($stadium) ? 'Cập Nhật Sân' : 'Tạo Sân Mới' }}
            </button>
        </div>
    </form>
</div>
