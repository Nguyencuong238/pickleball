@extends('layouts.homeyard')

@section('content')
<main class="main-content" id="mainContent">
    <div class="container">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <h1>Chỉnh sửa Nhóm/CLB</h1>
                <div class="breadcrumb">
                    <span class="breadcrumb-item">
                        <a href="{{ route('homeyard.overview') }}" class="breadcrumb-link">🏠 Dashboard</a>
                    </span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item">
                        <a href="{{ route('homeyard.clubs.index') }}" class="breadcrumb-link">Nhóm/CLB</a>
                    </span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item">{{ $club->name }}</span>
                </div>
            </div>
        </header>

        <!-- Form Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h3 class="card-title">✏️ Chỉnh Sửa Nhóm/CLB</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('homeyard.clubs.update', $club) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Tên Nhóm/CLB *</label>
                            <input 
                                type="text" 
                                name="name" 
                                class="form-input @error('name') is-invalid @enderror" 
                                value="{{ old('name', $club->name) }}"
                                placeholder="Nhập tên nhóm hoặc câu lạc bộ"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Loại *</label>
                            <select name="type" class="form-input @error('type') is-invalid @enderror" required>
                                <option value="">-- Chọn loại --</option>
                                <option value="club" {{ old('type', $club->type) === 'club' ? 'selected' : '' }}>Câu lạc bộ</option>
                                <option value="group" {{ old('type', $club->type) === 'group' ? 'selected' : '' }}>Nhóm</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Founded Date & Province -->
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Ngày Thành lập *</label>
                            <input 
                                type="date" 
                                name="founded_date" 
                                class="form-input @error('founded_date') is-invalid @enderror" 
                                value="{{ old('founded_date', $club->founded_date->format('Y-m-d')) }}"
                                required
                            >
                            @error('founded_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Khu Vực Hoạt Động *</label>
                            <select name="provinces[]" class="form-input select2-multiple @error('provinces') is-invalid @enderror" multiple required>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->id }}" 
                                        {{ in_array($province->id, old('provinces', $selectedProvinces)) ? 'selected' : '' }}>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('provinces')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">Mô tả</label>
                        <textarea 
                            name="description" 
                            class="form-textarea @error('description') is-invalid @enderror" 
                            rows="4"
                            placeholder="Mô tả chi tiết về nhóm hoặc câu lạc bộ"
                        >{{ old('description', $club->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Objectives -->
                    <div class="form-group">
                        <label class="form-label">Mục tiêu</label>
                        <textarea 
                            name="objectives" 
                            class="form-textarea @error('objectives') is-invalid @enderror" 
                            rows="4"
                            placeholder="Các mục tiêu của nhóm hoặc câu lạc bộ"
                        >{{ old('objectives', $club->objectives) }}</textarea>
                        @error('objectives')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Logo/Image & Banner Upload -->
                    <div class="grid grid-2">
                        <!-- Logo/Image Upload -->
                        <div class="form-group">
                            <label class="form-label">Logo/Ảnh đại diện</label>
                            <div class="upload-area" id="imageUploadArea">
                                <input 
                                    type="file" 
                                    class="form-control d-none @error('image') is-invalid @enderror" 
                                    id="image" 
                                    name="image" 
                                    accept="image/*"
                                >
                                <div class="upload-content" id="imageUploadContent">
                                    <p class="upload-text">Kéo thả ảnh hoặc <strong>bấm để chọn</strong></p>
                                    <p class="upload-hint">JPG, PNG, GIF, WebP (Tối đa 2MB)</p>
                                </div>
                                <div class="preview-area {{ $club->image ? '' : 'd-none' }}" id="imagePreview">
                                    <img id="imagePreviewImg" src="{{ $club->image ? storage_url($club->image) : '' }}" alt="Preview" style="height: 100%; object-fit: cover; border-radius: 6px;">
                                    <button type="button" class="btn-remove" onclick="removeImage('image')">✕</button>
                                </div>
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Banner Upload -->
                        <div class="form-group">
                            <label class="form-label">Banner</label>
                            <div class="upload-area" id="bannerUploadArea">
                                <input 
                                    type="file" 
                                    class="form-control d-none @error('banner') is-invalid @enderror" 
                                    id="banner" 
                                    name="banner" 
                                    accept="image/*"
                                >
                                <div class="upload-content" id="bannerUploadContent">
                                    <p class="upload-text">Kéo thả ảnh hoặc <strong>bấm để chọn</strong></p>
                                    <p class="upload-hint">JPG, PNG, GIF, WebP (Tối đa 2MB)</p>
                                </div>
                                <div class="preview-area {{ $club->banner ? '' : 'd-none' }}" id="bannerPreview">
                                    <img id="bannerPreviewImg" src="{{ $club->banner ? storage_url($club->banner) : '' }}" alt="Preview" style="height: 100%; object-fit: cover; border-radius: 6px;">
                                    <button type="button" class="btn-remove" onclick="removeImage('banner')">✕</button>
                                </div>
                            </div>
                            @error('banner')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Members -->
                    <div style="margin-bottom: 40px; padding-bottom: 30px; border-bottom: 1px solid #e2e8f0;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 15px;">👥 Thành Viên</h3>
                        <p style="color: #6b7280; margin-bottom: 15px;">Chọn thành viên để thêm vào nhóm/CLB ({{ count($users) }} thành viên có sẵn)</p>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="memberSearch" style="font-weight: 600; margin-bottom: 8px;display:block;">🔍 Tìm kiếm thành viên</label>
                            <input type="text" id="memberSearch" placeholder="Tìm kiếm theo tên hoặc email..." 
                                class="form-input" style="border: 2px solid #e5e7eb;">
                            <small style="color: #9ca3af; display: block; margin-top: 5px;">Nhập tên hoặc email để hiển thị danh sách thành viên</small>
                        </div>

                        <div class="checkbox-group" id="membersContainer">
                            @forelse($users as $user)
                                <div class="checkbox-item member-item" data-name="{{ Str::lower($user->name) }}" data-email="{{ Str::lower($user->email) }}" style="display: none;">
                                    <input type="checkbox" id="member_{{ $user->id }}" name="members[]" value="{{ $user->id }}"
                                        {{ in_array($user->id, old('members', $selectedMembers)) ? 'checked' : '' }}>
                                    <label for="member_{{ $user->id }}">{{ $user->name }} <span style="color: #9ca3af;">({{ $user->email }})</span></label>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 20px; color: #9ca3af;">
                                    ℹ️ Không có thành viên nào khác để thêm
                                </div>
                            @endforelse
                        </div>

                        <script>
                            const memberSearch = document.getElementById('memberSearch');
                            const memberItems = document.querySelectorAll('.member-item');

                            memberSearch.addEventListener('keyup', function() {
                                const searchValue = this.value.toLowerCase().trim();
                                let visibleCount = 0;

                                memberItems.forEach(item => {
                                    const name = item.dataset.name;
                                    const email = item.dataset.email;
                                    
                                    const isMatch = searchValue !== '' && (name.includes(searchValue) || email.includes(searchValue));
                                    
                                    item.style.display = isMatch ? 'flex' : 'none';
                                    if (isMatch) visibleCount++;
                                });

                                const noResults = document.getElementById('noResults');
                                if (visibleCount === 0 && searchValue !== '') {
                                    if (!noResults) {
                                        const msg = document.createElement('div');
                                        msg.id = 'noResults';
                                        msg.style.cssText = 'text-align: left; padding: 20px; color: #9ca3af;';
                                        msg.textContent = '❌ Không tìm thấy thành viên nào';
                                        document.getElementById('membersContainer').appendChild(msg);
                                    }
                                } else if (noResults) {
                                    noResults.remove();
                                }
                            });
                        </script>
                    </div>

                    <!-- Buttons -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                        <a href="{{ route('homeyard.clubs.index') }}" class="btn btn-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">💾 Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
    .upload-area {
        position: relative;
        border: 2px dashed var(--border-color);
        border-radius: 8px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: all var(--transition);
        background: var(--bg-light);
    }

    .upload-area:hover {
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.05);
    }

    .upload-content {
        pointer-events: none;
    }

    .upload-icon {
        font-size: 3rem;
        display: block;
        margin-bottom: 10px;
    }

    .upload-text {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
    }

    .upload-hint {
        font-size: 0.85rem;
        color: var(--text-light);
    }

    .preview-area {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        overflow: hidden;
        background: var(--bg-light);
        height: 200px;
    }

    .preview-area img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .btn-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        transition: all var(--transition);
    }

    .btn-remove:hover {
        background: rgba(0, 0, 0, 0.9);
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 600px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        padding: 15px;
        border-radius: 8px;
        background: #f9fafb;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        width: 100%;
    }

    .checkbox-item input[type="checkbox"] {
        width: auto;
        margin-right: 10px;
        cursor: pointer;
    }

    .checkbox-item label {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
        color: #6b7280;
    }

    .d-none {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 for provinces
        $('select[name="provinces[]"]').select2({
            placeholder: 'Chọn tỉnh/thành phố',
            width: '100%',
            allowClear: true
        });

        // Initialize Select2 for members
        $('select[name="members[]"]').select2({
            placeholder: 'Chọn thành viên',
            width: '100%'
        });

        // Image upload handling
        setupImageUpload('image');
        setupImageUpload('banner');
    });

    function setupImageUpload(fieldName) {
        const uploadArea = document.getElementById(fieldName + 'UploadArea');
        const fileInput = document.getElementById(fieldName);
        const previewArea = document.getElementById(fieldName + 'Preview');
        const previewImg = document.getElementById(fieldName + 'PreviewImg');
        const uploadContent = document.getElementById(fieldName + 'UploadContent');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--primary-color)';
            uploadArea.style.background = 'rgba(59, 130, 246, 0.1)';
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = 'var(--border-color)';
            uploadArea.style.background = 'var(--bg-light)';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--border-color)';
            uploadArea.style.background = 'var(--bg-light)';
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(fileInput, previewArea, previewImg, uploadContent);
        });

        fileInput.addEventListener('change', () => {
            handleFileSelect(fileInput, previewArea, previewImg, uploadContent);
        });
    }

    function handleFileSelect(fileInput, previewArea, previewImg, uploadContent) {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const reader = new FileReader();

            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewArea.classList.remove('d-none');
                uploadContent.classList.add('d-none');
            };

            reader.readAsDataURL(file);
        }
    }

    function removeImage(fieldName) {
        const fileInput = document.getElementById(fieldName);
        const previewArea = document.getElementById(fieldName + 'Preview');
        const uploadContent = document.getElementById(fieldName + 'UploadContent');

        fileInput.value = '';
        previewArea.classList.add('d-none');
        uploadContent.classList.remove('d-none');
    }
</script>
@endsection
