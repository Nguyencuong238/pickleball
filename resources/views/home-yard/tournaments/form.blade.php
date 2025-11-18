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
                    <option value="1" {{ (isset($tournament) && $tournament->status == 1) || old('status') == 1 ? 'selected' : '' }}>Hoạt Động</option>
                    <option value="0" {{ (isset($tournament) && $tournament->status == 0) || old('status') == 0 ? 'selected' : '' }}>Không Hoạt Động</option>
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

        <!-- Hình Thức Thi Đấu và Hạng Giải -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hình Thức Thi Đấu</label>
                <select name="competition_format" class="form-control"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="">-- Chọn --</option>
                    <option value="single" {{ (isset($tournament) && $tournament->competition_format === 'single') || old('competition_format') === 'single' ? 'selected' : '' }}>Đơn</option>
                    <option value="double" {{ (isset($tournament) && $tournament->competition_format === 'double') || old('competition_format') === 'double' ? 'selected' : '' }}>Đôi</option>
                    <option value="mixed" {{ (isset($tournament) && $tournament->competition_format === 'mixed') || old('competition_format') === 'mixed' ? 'selected' : '' }}>Hỗn Hợp</option>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hạng Giải</label>
                <select name="tournament_rank" class="form-control"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="">-- Chọn --</option>
                    <option value="beginner" {{ (isset($tournament) && $tournament->tournament_rank === 'beginner') || old('tournament_rank') === 'beginner' ? 'selected' : '' }}>Sơ Cấp</option>
                    <option value="intermediate" {{ (isset($tournament) && $tournament->tournament_rank === 'intermediate') || old('tournament_rank') === 'intermediate' ? 'selected' : '' }}>Trung Cấp</option>
                    <option value="advanced" {{ (isset($tournament) && $tournament->tournament_rank === 'advanced') || old('tournament_rank') === 'advanced' ? 'selected' : '' }}>Cao Cấp</option>
                    <option value="professional" {{ (isset($tournament) && $tournament->tournament_rank === 'professional') || old('tournament_rank') === 'professional' ? 'selected' : '' }}>Chuyên Nghiệp</option>
                </select>
            </div>
        </div>

        <!-- Lợi Ích Đăng Ký -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Lợi Ích Đăng Ký</label>
            <textarea name="registration_benefits" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->registration_benefits ?? old('registration_benefits') }}</textarea>
        </div>

        <!-- Luật Thi Đấu Chi Tiết -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Luật Thi Đấu Chi Tiết</label>
            <textarea name="competition_rules" class="form-control" rows="5"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->competition_rules ?? old('competition_rules') }}</textarea>
        </div>

        <!-- Timeline Sự Kiện -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Timeline Sự Kiện</label>
            <textarea name="event_timeline" class="form-control" rows="5"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->event_timeline ?? old('event_timeline') }}</textarea>
        </div>

        <!-- Thông Tin Xã Hội -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Thông Tin Xã Hội (Link, Mạng Xã Hội...)</label>
            <textarea name="social_information" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->social_information ?? old('social_information') }}</textarea>
        </div>

        <!-- Thông Tin Tổ Chức -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Email Tổ Chức</label>
                <input type="email" name="organizer_email" class="form-control" value="{{ $tournament->organizer_email ?? old('organizer_email') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hotline Tổ Chức</label>
                <input type="tel" name="organizer_hotline" class="form-control" value="{{ $tournament->organizer_hotline ?? old('organizer_hotline') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Lịch Thi Đấu -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Lịch Thi Đấu Chi Tiết</label>
            <textarea name="competition_schedule" class="form-control" rows="5"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->competition_schedule ?? old('competition_schedule') }}</textarea>
        </div>

        <!-- Kết Quả -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Kết Quả Giải Đấu</label>
            <textarea name="results" class="form-control" rows="5"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->results ?? old('results') }}</textarea>
        </div>

        <!-- Gallery -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Thư Viện Ảnh (Chọn nhiều ảnh)</label>
            @if(isset($tournament) && $tournament->gallery)
                <div style="margin-bottom: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;">
                    @foreach($tournament->gallery as $index => $image)
                        <div style="position: relative;">
                            <img src="{{ asset('storage/' . $image) }}" alt="Gallery" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px;">
                            <button type="button" onclick="removeGalleryImage({{ $index }})" style="position: absolute; top: 5px; right: 5px; background: red; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 16px;">×</button>
                        </div>
                    @endforeach
                </div>
            @endif
            <div style="border: 2px dashed #e2e8f0; border-radius: 6px; padding: 20px; text-align: center; background: #f8fafc; cursor: pointer;" id="dropZone">
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">Kéo thả ảnh vào đây hoặc <span style="color: #ec4899; font-weight: 600;">bấm chọn file</span></p>
                <input type="file" id="galleryInput" name="gallery[]" class="form-control" accept="image/*" multiple
                    style="display: none; width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
            <div id="galleryPreview" style="margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;"></div>
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

            // Gallery upload handler
            const dropZone = document.getElementById('dropZone');
            const galleryInput = document.getElementById('galleryInput');

            dropZone.addEventListener('click', function() {
                galleryInput.click();
            });

            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZone.style.background = '#f1f5f9';
                dropZone.style.borderColor = '#ec4899';
            });

            dropZone.addEventListener('dragleave', function() {
                dropZone.style.background = '#f8fafc';
                dropZone.style.borderColor = '#e2e8f0';
            });

            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZone.style.background = '#f8fafc';
                dropZone.style.borderColor = '#e2e8f0';
                
                // Transfer dropped files to input
                galleryInput.files = e.dataTransfer.files;
                
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                galleryInput.dispatchEvent(event);
            });

            galleryInput.addEventListener('change', function(e) {
                const preview = document.getElementById('galleryPreview');
                preview.innerHTML = '';
                
                console.log('Files selected: ' + e.target.files.length);
                
                Array.from(e.target.files).forEach((file, index) => {
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        const div = document.createElement('div');
                        div.style.position = 'relative';
                        
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.style.width = '100%';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '6px';
                        
                        div.appendChild(img);
                        preview.appendChild(div);
                    };
                    
                    reader.readAsDataURL(file);
                });
            });

            function removeGalleryImage(index) {
                // This would be handled by the controller
                console.log('Remove image at index: ' + index);
            }
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
