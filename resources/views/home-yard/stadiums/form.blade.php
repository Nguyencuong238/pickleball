@if($errors->any())
    <div style="background: #fee2e2; color: #991b1b; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #991b1b;">
        <strong>Lỗi Xác Thực:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<div style="background: white; border-radius: 15px; padding: clamp(20px, 5vw, 30px); box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
    <form method="POST" action="{{ isset($stadium) ? route('homeyard.stadiums.update', $stadium) : route('homeyard.stadiums.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($stadium))
            @method('PUT')
        @endif

        <!-- Hàng 1: Tên Sân và Trạng Thái -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tên Sân *</label>
                <input type="text" name="name" class="form-control" value="{{ $stadium->name ?? old('name') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Trạng Thái *</label>
                <select name="status" class="form-control" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="active" {{ (isset($stadium) && $stadium->status === 'active') || old('status') === 'active' ? 'selected' : '' }}>Hoạt Động</option>
                    <option value="inactive" {{ (isset($stadium) && $stadium->status === 'inactive') || old('status') === 'inactive' ? 'selected' : '' }}>Không Hoạt Động</option>
                </select>
            </div>
        </div>

        <!-- Mô Tả -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mô Tả</label>
            <textarea name="description" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $stadium->description ?? old('description') }}</textarea>
        </div>

        <!-- Địa Chỉ -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Địa Chỉ *</label>
            <input type="text" name="address" class="form-control" value="{{ $stadium->address ?? old('address') }}" required
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Contact Info -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
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

        <!-- Courts Count, Hours -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Số Sân *</label>
                <input type="number" name="courts_count" class="form-control" value="{{ $stadium->courts_count ?? old('courts_count', 1) }}" min="1" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Giờ Hoạt Động</label>
                <input type="text" name="opening_hours" class="form-control" value="{{ $stadium->opening_hours ?? old('opening_hours') }}" placeholder="VD: 06:00 - 22:00"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Image -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ảnh Sân</label>
            @if(isset($stadium) && $stadium->image)
                <div style="margin-bottom: 10px;">
                    <img src="{{ asset('storage/' . $stadium->image) }}" alt="{{ $stadium->name }}" style="max-width: 200px; max-height: 150px; border-radius: 6px;">
                </div>
            @endif
            <input type="file" id="imageInput" name="image" class="form-control" accept="image/*"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
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
        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('homeyard.stadiums.index') }}" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; white-space: nowrap;">Hủy</a>
            <button type="submit" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; white-space: nowrap;">
                {{ isset($stadium) ? 'Cập Nhật Sân' : 'Tạo Sân Mới' }}
            </button>
        </div>
    </form>
</div>
