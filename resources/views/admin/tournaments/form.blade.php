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
    <form method="POST" action="{{ isset($tournament) ? route('admin.tournaments.update', $tournament) : route('admin.tournaments.store') }}" enctype="multipart/form-data">
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

        <!-- Ngày Bắt Đầu, Kết Thúc, Hạn Đăng Ký, Địa Điểm -->
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
                 <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hạn Đăng Ký</label>
                 <input type="datetime-local" name="registration_deadline" class="form-control" value="{{ isset($tournament) && $tournament->registration_deadline ? $tournament->registration_deadline->format('Y-m-d\TH:i') : old('registration_deadline') }}"
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
             <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Thư Viện Ảnh (JSON URLs)</label>
             @php
                 $galleryItems = [];
                 if(isset($tournament) && $tournament->gallery) {
                     $galleryItems = is_array($tournament->gallery) ? $tournament->gallery : json_decode($tournament->gallery, true) ?? [];
                 }
             @endphp
             @if(!empty($galleryItems))
                 <div style="margin-bottom: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;">
                     @foreach($galleryItems as $index => $item)
                         <div style="position: relative; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px;">
                             @if(is_array($item) && isset($item['url']))
                                 <img src="{{ $item['url'] }}" alt="Gallery {{ $index }}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px;">
                                 @if(isset($item['title']))
                                     <p style="margin: 5px 0 0 0; font-size: 0.75rem; color: #64748b; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">{{ $item['title'] }}</p>
                                 @endif
                             @else
                                 <img src="{{ $item }}" alt="Gallery {{ $index }}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px;">
                             @endif
                             <button type="button" onclick="removeGalleryImage({{ $index }})" style="position: absolute; top: 5px; right: 5px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;">×</button>
                         </div>
                     @endforeach
                 </div>
             @endif
            <div style="background: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; margin-bottom: 12px; font-size: 0.85rem; color: #1e40af;">
                <strong>ℹ️ Hướng dẫn:</strong> Thêm ảnh bằng cách paste URL (nên dùng Unsplash, Pexels, hoặc upload file)
            </div>
            
            <!-- Gallery URLs Input -->
            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #1e293b; font-size: 0.9rem;">Thêm ảnh từ URL:</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="galleryUrlInput" placeholder="https://example.com/image.jpg" 
                        style="flex: 1; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem;">
                    <input type="text" id="galleryTitleInput" placeholder="Tiêu đề (không bắt buộc)" 
                        style="flex: 0.5; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem;">
                    <button type="button" onclick="addGalleryUrl()" 
                        style="background: #8b5cf6; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; white-space: nowrap;">Thêm</button>
                </div>
            </div>
            
            <!-- File Upload -->
            <div style="border: 2px dashed #e2e8f0; border-radius: 6px; padding: 20px; text-align: center; background: #f8fafc; cursor: pointer; margin-bottom: 12px;" id="dropZone">
                 <p style="margin: 0; color: #64748b; font-size: 0.9rem;">Kéo thả ảnh vào đây hoặc <span style="color: #8b5cf6; font-weight: 600;">bấm chọn file</span></p>
                 <input type="file" id="galleryInput" name="gallery[]" class="form-control" accept="image/*" multiple
                     style="display: none; width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
             </div>
             
             <!-- Hidden field to store gallery JSON -->
             <textarea id="galleryJsonField" name="gallery_json" style="display: none;"></textarea>
             
             <div id="galleryPreview" style="margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;"></div>
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
             // Gallery items storage
             let galleryItems = @json($galleryItems ?? []);
             
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

             // Gallery JSON management
             function addGalleryUrl() {
                 const urlInput = document.getElementById('galleryUrlInput');
                 const titleInput = document.getElementById('galleryTitleInput');
                 const url = urlInput.value.trim();
                 const title = titleInput.value.trim();
                 
                 if (!url) {
                     alert('Vui lòng nhập URL ảnh');
                     return;
                 }
                 
                 // Add to gallery items
                 galleryItems.push({
                     url: url,
                     title: title || 'Gallery Image'
                 });
                 
                 // Clear inputs
                 urlInput.value = '';
                 titleInput.value = '';
                 
                 // Update preview
                 renderGalleryPreview();
                 updateGalleryJson();
             }
             
             function removeGalleryImage(index) {
                 galleryItems.splice(index, 1);
                 renderGalleryPreview();
                 updateGalleryJson();
             }
             
             function renderGalleryPreview() {
                 const preview = document.getElementById('galleryPreview');
                 preview.innerHTML = '';
                 
                 galleryItems.forEach((item, index) => {
                     const div = document.createElement('div');
                     div.style.position = 'relative';
                     div.style.border = '1px solid #e2e8f0';
                     div.style.borderRadius = '6px';
                     div.style.padding = '5px';
                     
                     const img = document.createElement('img');
                     img.src = item.url;
                     img.style.width = '100%';
                     img.style.height = '100px';
                     img.style.objectFit = 'cover';
                     img.style.borderRadius = '4px';
                     img.alt = 'Gallery';
                     
                     const title = document.createElement('p');
                     title.textContent = item.title;
                     title.style.margin = '5px 0 0 0';
                     title.style.fontSize = '0.75rem';
                     title.style.color = '#64748b';
                     title.style.textOverflow = 'ellipsis';
                     title.style.whiteSpace = 'nowrap';
                     title.style.overflow = 'hidden';
                     
                     const btn = document.createElement('button');
                     btn.type = 'button';
                     btn.textContent = '×';
                     btn.style.position = 'absolute';
                     btn.style.top = '5px';
                     btn.style.right = '5px';
                     btn.style.background = '#ef4444';
                     btn.style.color = 'white';
                     btn.style.border = 'none';
                     btn.style.borderRadius = '50%';
                     btn.style.width = '24px';
                     btn.style.height = '24px';
                     btn.style.cursor = 'pointer';
                     btn.style.fontSize = '16px';
                     btn.onclick = () => removeGalleryImage(index);
                     
                     div.appendChild(img);
                     div.appendChild(title);
                     div.appendChild(btn);
                     preview.appendChild(div);
                 });
             }
             
             function updateGalleryJson() {
                 document.getElementById('galleryJsonField').value = JSON.stringify(galleryItems);
             }
             
             // Initialize preview on page load
             if (galleryItems.length > 0) {
                 renderGalleryPreview();
             }

             // Gallery upload handler
             const dropZone = document.getElementById('dropZone');
             const galleryInput = document.getElementById('galleryInput');

             dropZone.addEventListener('click', function() {
                 galleryInput.click();
             });

             dropZone.addEventListener('dragover', function(e) {
                 e.preventDefault();
                 dropZone.style.background = '#f1f5f9';
                 dropZone.style.borderColor = '#8b5cf6';
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
                 console.log('Files selected: ' + e.target.files.length);
                 
                 Array.from(e.target.files).forEach((file, index) => {
                     const reader = new FileReader();
                     
                     reader.onload = function(event) {
                         // Add file as data URL to gallery
                         galleryItems.push({
                             url: event.target.result,
                             title: file.name.split('.')[0]
                         });
                         renderGalleryPreview();
                         updateGalleryJson();
                     };
                     
                     reader.readAsDataURL(file);
                 });
             });
         </script>

        <!-- Buttons -->
        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('admin.tournaments.index') }}" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; white-space: nowrap;">Hủy</a>
            <button type="submit" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; white-space: nowrap;">
                {{ isset($tournament) ? 'Cập Nhật Giải Đấu' : 'Tạo Giải Đấu' }}
            </button>
        </div>
    </form>
</div>
