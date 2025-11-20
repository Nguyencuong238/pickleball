@if($errors->any())
    <div class="alert alert-danger fade-in" style="margin-bottom: 20px;">
        <strong>❌ Lỗi Xác Thực:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card fade-in">
    <div class="card-header">
        <h3 class="card-title">{{ isset($stadium) ? '✏️ Chỉnh Sửa Sân' : '➕ Tạo Sân Mới' }}</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($stadium) ? route('homeyard.stadiums.update', $stadium) : route('homeyard.stadiums.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($stadium))
                @method('PUT')
            @endif

            <!-- Hàng 1: Tên Sân và Trạng Thái -->
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Tên Sân *</label>
                    <input type="text" name="name" class="form-input" value="{{ $stadium->name ?? old('name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng Thái *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ (isset($stadium) && $stadium->status === 'active') || old('status') === 'active' ? 'selected' : '' }}>Hoạt Động</option>
                        <option value="inactive" {{ (isset($stadium) && $stadium->status === 'inactive') || old('status') === 'inactive' ? 'selected' : '' }}>Không Hoạt Động</option>
                    </select>
                </div>
            </div>

            <!-- Mô Tả -->
            <div class="form-group">
                <label class="form-label">Mô Tả</label>
                <textarea name="description" class="form-textarea" rows="4">{{ $stadium->description ?? old('description') }}</textarea>
            </div>

            <!-- Địa Chỉ -->
            <div class="form-group">
                <label class="form-label">Địa Chỉ *</label>
                <input type="text" name="address" class="form-input" value="{{ $stadium->address ?? old('address') }}" required>
            </div>

            <!-- Contact Info -->
            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label">Điện Thoại</label>
                    <input type="tel" name="phone" class="form-input" value="{{ $stadium->phone ?? old('phone') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ $stadium->email ?? old('email') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-input" value="{{ $stadium->website ?? old('website') }}">
                </div>
            </div>

            <!-- Court Surface, Hours -->
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Mặt Sân</label>
                    <input type="text" name="court_surface" class="form-input" value="{{ $stadium->court_surface ?? old('court_surface') }}" placeholder="VD: Acrylic chuyên dụng">
                </div>

                <div class="form-group">
                    <label class="form-label">Giờ Hoạt Động</label>
                    <input type="text" name="opening_hours" class="form-input" value="{{ $stadium->opening_hours ?? old('opening_hours') }}" placeholder="VD: 06:00 - 22:00">
                </div>
            </div>

            <!-- Rating, Featured, Verified -->
            <div class="grid grid-4">
                <div class="form-group">
                    <label class="form-label">Đánh Giá (Sao)</label>
                    <input type="number" name="rating" class="form-input" value="{{ $stadium->rating ?? old('rating', 0) }}" min="0" max="5" step="0.1" placeholder="0-5">
                </div>

                <div class="form-group">
                    <label class="form-label">Số Lượng Đánh Giá</label>
                    <input type="number" name="rating_count" class="form-input" value="{{ $stadium->rating_count ?? old('rating_count', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng Thái Nổi Bật</label>
                    <select name="featured_status" class="form-select">
                        <option value="normal" {{ (isset($stadium) && $stadium->featured_status === 'normal') || old('featured_status') === 'normal' ? 'selected' : '' }}>Bình Thường</option>
                        <option value="featured" {{ (isset($stadium) && $stadium->featured_status === 'featured') || old('featured_status') === 'featured' ? 'selected' : '' }}>Nổi Bật</option>
                    </select>
                </div>

                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer;">
                        <input type="checkbox" name="verified" value="1" {{ (isset($stadium) && $stadium->verified) || old('verified') ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer;">
                        Đã Xác Minh
                    </label>
                </div>
            </div>

            <!-- Utilities (JSON array) -->
            <div class="form-group">
                <label class="form-label">Tiện Ích (Nhập mỗi cái trên một dòng)</label>
                <textarea name="utilities" class="form-textarea" rows="4" placeholder="VD:&#10;🏟️ 8 Sân thi đấu&#10;🚿 Phòng tắm&#10;🅿️ Bãi đỗ xe">@if(isset($stadium) && $stadium->utilities){{ implode("\n", $stadium->utilities) }}@else{{ old('utilities') }}@endif</textarea>
                <small style="color: #64748b; margin-top: 8px; display: block;">Mỗi dòng sẽ được lưu như một mục tiện ích</small>
            </div>

            <!-- Regulations -->
            <div class="form-group">
                <label class="form-label">Quy Định Sân</label>
                <textarea name="regulations" class="form-textarea" rows="6">{{ $stadium->regulations ?? old('regulations') }}</textarea>
                <small style="color: #64748b; margin-top: 8px; display: block;">Nhập các quy định chi tiết của sân</small>
            </div>

            <!-- Image -->
            <div class="form-group">
                <label class="form-label">Ảnh Sân</label>
                @if(isset($stadium) && $stadium->image)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('storage/' . $stadium->image) }}" alt="{{ $stadium->name }}" style="max-width: 200px; max-height: 150px; border-radius: 6px;">
                    </div>
                @endif
                <input type="file" id="imageInput" name="image" class="form-input" accept="image/*">
                <small style="color: #64748b; margin-top: 8px; display: block;">Khuyến nghị: JPG, PNG, WEBP (tối đa 2MB)</small>
                <div id="imagePreview" style="margin-top: 10px;"></div>
            </div>

            <script>
                document.getElementById('imageInput').addEventListener('change', function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = '';
                    
                    if (e.target.files.length > 0) {
                        const file = e.target.files[0];
                        const reader = new FileReader();
                        
                        reader.onload = function(event) {
                            const img = document.createElement('img');
                            img.src = event.target.result;
                            img.style.maxWidth = '200px';
                            img.style.maxHeight = '150px';
                            img.style.borderRadius = '6px';
                            img.style.marginTop = '10px';
                            preview.appendChild(img);
                        };
                        
                        reader.readAsDataURL(file);
                    }
                });
            </script>

            <!-- Buttons -->
            <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('homeyard.stadiums.index') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($stadium) ? '💾 Cập Nhật Sân' : '➕ Tạo Sân Mới' }}
                </button>
            </div>
        </form>
    </div>
</div>
