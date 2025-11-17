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
    <form method="POST" action="{{ isset($tournament) ? route('homeyard.tournaments.update', $tournament) : route('homeyard.tournaments.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($tournament))
            @method('PUT')
        @endif

        <!-- Tên Giải và Trạng Thái -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tên Giải Đấu *</label>
                <input type="text" name="name" class="form-control" value="{{ $tournament->name ?? old('name') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Trạng Thái *</label>
                <select name="status" class="form-control" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="upcoming" {{ (isset($tournament) && $tournament->status === 'upcoming') || old('status') === 'upcoming' ? 'selected' : '' }}>Sắp Diễn Ra</option>
                    <option value="ongoing" {{ (isset($tournament) && $tournament->status === 'ongoing') || old('status') === 'ongoing' ? 'selected' : '' }}>Đang Diễn Ra</option>
                    <option value="completed" {{ (isset($tournament) && $tournament->status === 'completed') || old('status') === 'completed' ? 'selected' : '' }}>Hoàn Thành</option>
                    <option value="cancelled" {{ (isset($tournament) && $tournament->status === 'cancelled') || old('status') === 'cancelled' ? 'selected' : '' }}>Huỷ</option>
                </select>
            </div>
        </div>

        <!-- Mô Tả -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mô Tả</label>
            <textarea name="description" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->description ?? old('description') }}</textarea>
        </div>

        <!-- Ngày Bắt Đầu, Kết Thúc, Địa Điểm -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ngày Bắt Đầu *</label>
                <input type="date" name="start_date" class="form-control" value="{{ isset($tournament) ? $tournament->start_date->format('Y-m-d') : old('start_date') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ngày Kết Thúc</label>
                <input type="date" name="end_date" class="form-control" value="{{ isset($tournament) && $tournament->end_date ? $tournament->end_date->format('Y-m-d') : old('end_date') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Địa Điểm</label>
                <input type="text" name="location" class="form-control" value="{{ $tournament->location ?? old('location') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Số Vận Động Viên Tối Đa, Giá -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Số Vận Động Viên Tối Đa *</label>
                <input type="number" name="max_participants" class="form-control" value="{{ $tournament->max_participants ?? old('max_participants', 32) }}" min="1" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Giá (VNĐ) *</label>
                <input type="number" name="price" class="form-control" value="{{ $tournament->price ?? old('price', 0) }}" step="0.01" min="0" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Luật Thi Đấu -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Luật Thi Đấu</label>
            <textarea name="rules" class="form-control" rows="5"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->rules ?? old('rules') }}</textarea>
        </div>

        <!-- Giải Thưởng -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Giải Thưởng</label>
            <textarea name="prizes" class="form-control" rows="5"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->prizes ?? old('prizes') }}</textarea>
        </div>

        <!-- Ảnh -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ảnh Giải Đấu</label>
            @if(isset($tournament) && $tournament->image)
                <div style="margin-bottom: 10px;">
                    <img src="{{ asset('storage/' . $tournament->image) }}" alt="{{ $tournament->name }}" style="max-width: 200px; max-height: 150px; border-radius: 6px;">
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
            <a href="{{ route('homeyard.tournaments.index') }}" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; white-space: nowrap;">Hủy</a>
            <button type="submit" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; white-space: nowrap;">
                {{ isset($tournament) ? 'Cập Nhật Giải Đấu' : 'Tạo Giải Đấu' }}
            </button>
        </div>
    </form>
</div>
