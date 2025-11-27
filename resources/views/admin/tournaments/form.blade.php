@if ($errors->any())
    <div
        style="background: #fee2e2; color: #991b1b; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #991b1b;">
        <strong>Lỗi Xác Thực:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <form method="POST"
        action="{{ isset($tournament) ? route('admin.tournaments.update', $tournament) : route('admin.tournaments.store') }}"
        enctype="multipart/form-data">
        @csrf
        @if (isset($tournament))
            @method('PUT')
        @endif

        <!-- Tên Giải và Trạng Thái -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tên Giải Đấu *</label>
            <input type="text" name="name" class="form-control" value="{{ $tournament->name ?? old('name') }}"
                required
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ngày Bắt Đầu
                    *</label>
                <input type="date" name="start_date" class="form-control"
                    value="{{ isset($tournament) ? $tournament->start_date->format('Y-m-d') : old('start_date') }}"
                    required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ngày Kết
                    Thúc</label>
                <input type="date" name="end_date" class="form-control"
                    value="{{ isset($tournament) && $tournament->end_date ? $tournament->end_date->format('Y-m-d') : old('end_date') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Ngày Bắt Đầu, Kết Thúc, Hạn Đăng Ký, Địa Điểm -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hạn Đăng Ký</label>
                <input type="datetime-local" name="registration_deadline" class="form-control"
                    value="{{ isset($tournament) && $tournament->registration_deadline ? $tournament->registration_deadline->format('Y-m-d\TH:i') : old('registration_deadline') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Địa Điểm *</label>
                <input type="text" name="location" class="form-control" required
                    value="{{ $tournament->location ?? old('location') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

        </div>

        <!-- Số Vận Động Viên Tối Đa, Giá -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Loại giải *</label>
                <select name="competition_format" class="form-control" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="">Chọn loại giải</option>
                    <option value="single"
                        {{ old('competition_format', $tournament->competition_format) == 'single' ? 'selected' : '' }}>
                        Đơn
                    </option>
                    <option value="double"
                        {{ old('competition_format', $tournament->competition_format) == 'double' ? 'selected' : '' }}>
                        Đôi
                    </option>
                    <option value="mixed"
                        {{ old('competition_format', $tournament->competition_format) == 'mixed' ? 'selected' : '' }}>
                        Đôi
                        nam nữ</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Số VĐV
                    Tối Đa</label>
                <input type="number" name="max_participants" class="form-control"
                    value="{{ old('max_participants', $tournament->max_participants) }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Lệ phí, Giải Thưởng -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Lệ phí
                    (VNĐ)</label>
                <input type="number" name="price" class="form-control"
                    value="{{ old('price', $tournament->price) }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Giải Thưởng</label>
                <input type="text" name="prizes" value="{{ old('prizes', $tournament->prizes) }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Mô Tả -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mô Tả</label>
            <textarea name="description" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ old('description', $tournament->description) }}</textarea>
        </div>

        <!-- Quy định -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Quy định</label>
            <textarea name="competition_rules" class="form-control" rows="5" placeholder="Nhập quy định của giải đấu..."
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ old('competition_rules', $tournament->competition_rules) }}</textarea>
        </div>

        <!-- Quy định -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">
                Quyền lợi khi tham gia
            </label>
            <textarea name="registration_benefits" class="form-control" rows="5" placeholder="Quyền lợi khi tham gia..."
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ old('registration_benefits', $tournament->registration_benefits) }}</textarea>
        </div>


            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Banner</label>
                @include('components.media-uploader', [
                    'model' => $tournament,
                    'collection' => 'banner',
                    'name' => 'banner',
                    'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                    'maxItems' => 1,
                ])
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hình ảnh</label>
                @include('components.media-uploader', [
                    'model' => $tournament,
                    'collection' => 'gallery',
                    'name' => 'gallery',
                    'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                ])
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
                const event = new Event('change', {
                    bubbles: true
                });
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
        <div
            style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('admin.tournaments.index') }}"
                style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; white-space: nowrap;">Hủy</a>
            <button type="submit"
                style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; white-space: nowrap;">
                {{ isset($tournament) ? 'Cập Nhật Giải Đấu' : 'Tạo Giải Đấu' }}
            </button>
        </div>
    </form>
</div>
