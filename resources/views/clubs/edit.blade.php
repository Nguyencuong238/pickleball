@extends('layouts.front')

@section('content')
<style>
    .edit-container {
        padding: 40px 20px;
        max-width: 900px;
        margin: 0 auto;
        margin-top: 100px;
    }

    .edit-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .edit-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .form-section {
        margin-bottom: 30px;
    }

    .form-section h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f3f4f6;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #00D9B5;
        box-shadow: 0 0 0 3px rgba(0, 217, 181, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 300px;
        overflow-y: auto;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
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

    .image-preview {
        width: 200px;
        height: 200px;
        border-radius: 8px;
        object-fit: cover;
        margin-top: 10px;
        display: block;
    }

    .btn-group {
        display: flex;
        gap: 15px;
        margin-top: 40px;
    }

    .btn-submit,
    .btn-cancel,
    .btn-delete {
        flex: 1;
        padding: 14px 30px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }

    .btn-submit {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #6b7280;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-delete {
        background: #fee2e2;
        color: #b91c1c;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    .error-message {
        background: #fee2e2;
        color: #b91c1c;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .error-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .error-list li {
        padding: 4px 0;
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .btn-group {
            flex-direction: column;
        }
    }
</style>

<div class="edit-container">
    <div class="edit-header">
        <h2>✏️ Chỉnh Sửa Câu Lạc Bộ/Nhóm</h2>
        <p>Cập nhật thông tin của {{ $club->name }}</p>
    </div>

    <div class="form-card">
        @if($errors->any())
            <div class="error-message">
                <strong>Vui lòng sửa các lỗi sau:</strong>
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clubs.update', $club) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Thông Tin Cơ Bản -->
            <div class="form-section">
                <h4>📋 Thông Tin Cơ Bản</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Tên Câu Lạc Bộ/Nhóm <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $club->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Loại <span style="color: red;">*</span></label>
                        <select id="type" name="type" required>
                            <option value="club" {{ old('type', $club->type) === 'club' ? 'selected' : '' }}>Câu Lạc Bộ</option>
                            <option value="group" {{ old('type', $club->type) === 'group' ? 'selected' : '' }}>Nhóm</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Mô Tả</label>
                    <textarea id="description" name="description">{{ old('description', $club->description) }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="founded_date">Ngày Thành Lập <span style="color: red;">*</span></label>
                        <input type="date" id="founded_date" name="founded_date" value="{{ old('founded_date', $club->founded_date->format('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="image">Ảnh Đại Diện</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <div id="imagePreviewContainer" style="margin-top: 15px;">
                            @if($club->image)
                                <div style="position: relative; display: inline-block;">
                                    <img src="{{ storage_url($club->image) }}" alt="{{ $club->name }}"
                                        style="width: 200px; height: 200px; border-radius: 8px; object-fit: cover; border: 2px solid #f3f4f6;">
                                    <button type="button" onclick="document.getElementById('image').value = ''; document.getElementById('imagePreviewContainer').innerHTML = ''; return false;"
                                        style="position: absolute; top: 5px; right: 5px; background: #ff4444; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center;">
                                        X
                                    </button>
                                    <div style="margin-top: 8px; color: #6b7280; font-size: 0.85rem;">
                                        Ảnh đại diện hiện tại
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="banner">Ảnh Bìa (Banner)</label>
                    <input type="file" id="banner" name="banner" accept="image/*">
                    <small style="color: #6b7280; display: block; margin-top: 5px;">Kích thước đề xuất: 1200x300 pixels</small>
                    <div id="bannerPreviewContainer" style="margin-top: 15px;">
                        @if($club->banner)
                            <div style="position: relative; display: inline-block; width: 100%;">
                                <img src="{{ storage_url($club->banner) }}" alt="{{ $club->name }} Banner"
                                    style="width: 100%; max-width: 600px; height: 150px; border-radius: 8px; object-fit: cover; border: 2px solid #f3f4f6;">
                                <button type="button" onclick="document.getElementById('banner').value = ''; document.getElementById('bannerPreviewContainer').innerHTML = ''; return false;"
                                    style="position: absolute; top: 5px; right: 5px; background: #ff4444; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center;">
                                    X
                                </button>
                                <div style="margin-top: 8px; color: #6b7280; font-size: 0.85rem;">
                                    Banner hiện tại
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <script>
                    document.getElementById('image').addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        const container = document.getElementById('imagePreviewContainer');

                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                container.innerHTML = `
                                    <div style="position: relative; display: inline-block;">
                                        <img src="${event.target.result}" alt="Preview"
                                            style="width: 200px; height: 200px; border-radius: 8px; object-fit: cover; border: 2px solid #f3f4f6;">
                                        <button type="button" onclick="document.getElementById('image').value = ''; document.getElementById('imagePreviewContainer').innerHTML = ''; return false;"
                                            style="position: absolute; top: 5px; right: 5px; background: #ff4444; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center;">
                                            X
                                        </button>
                                        <div style="margin-top: 8px; color: #6b7280; font-size: 0.85rem;">
                                            ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                                        </div>
                                    </div>
                                `;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            container.innerHTML = '';
                        }
                    });

                    document.getElementById('banner').addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        const container = document.getElementById('bannerPreviewContainer');

                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                container.innerHTML = `
                                    <div style="position: relative; display: inline-block; width: 100%;">
                                        <img src="${event.target.result}" alt="Banner Preview"
                                            style="width: 100%; max-width: 600px; height: 150px; border-radius: 8px; object-fit: cover; border: 2px solid #f3f4f6;">
                                        <button type="button" onclick="document.getElementById('banner').value = ''; document.getElementById('bannerPreviewContainer').innerHTML = ''; return false;"
                                            style="position: absolute; top: 5px; right: 5px; background: #ff4444; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center;">
                                            X
                                        </button>
                                        <div style="margin-top: 8px; color: #6b7280; font-size: 0.85rem;">
                                            ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                                        </div>
                                    </div>
                                `;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            container.innerHTML = '';
                        }
                    });
                </script>

                <div class="form-group">
                    <label for="objectives">Mục Tiêu Hoạt Động</label>
                    <textarea id="objectives" name="objectives">{{ old('objectives', $club->objectives) }}</textarea>
                </div>
            </div>

            <!-- Khu Vực Hoạt Động -->
            <div class="form-section">
                <h4>🗺️ Khu Vực Hoạt Động</h4>
                <p style="color: #6b7280; margin-bottom: 15px;">Chọn một hoặc nhiều tỉnh <span style="color: red;">*</span></p>
                <div class="checkbox-group">
                    @foreach($provinces as $province)
                        <div class="checkbox-item">
                            <input type="checkbox" id="province_{{ $province->id }}" name="provinces[]" value="{{ $province->id }}" 
                                {{ in_array($province->id, old('provinces', $selectedProvinces)) ? 'checked' : '' }}>
                            <label for="province_{{ $province->id }}">{{ $province->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Thành Viên -->
            <div class="form-section">
                <h4>👥 Thành Viên</h4>
                <p style="color: #6b7280; margin-bottom: 15px;">Chọn thành viên để thêm vào câu lạc bộ/nhóm ({{ count($users) }} thành viên có sẵn)</p>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="memberSearch" style="font-weight: 600; margin-bottom: 8px;">🔍 Tìm kiếm thành viên</label>
                    <input type="text" id="memberSearch" placeholder="Tìm kiếm theo tên hoặc email..." 
                        style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease;">
                    <small style="color: #9ca3af; display: block; margin-top: 5px;">Nhập tên hoặc email để hiển thị danh sách thành viên</small>
                </div>

                <div class="checkbox-group" id="membersContainer" style="max-height: 600px;">
                    @forelse($users as $user)
                        <div class="checkbox-item member-item" data-name="{{ Str::lower($user->name) }}" data-email="{{ Str::lower($user->email) }}" style="display: flex;">
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

                    // Ẩn tất cả thành viên ban đầu
                    memberItems.forEach(item => {
                        item.style.display = 'none';
                    });

                    memberSearch.addEventListener('keyup', function() {
                        const searchValue = this.value.toLowerCase().trim();
                        let visibleCount = 0;

                        memberItems.forEach(item => {
                            const name = item.dataset.name;
                            const email = item.dataset.email;
                            
                            // Chỉ hiển thị khi có tìm kiếm
                            const isMatch = searchValue !== '' && (name.includes(searchValue) || email.includes(searchValue));
                            
                            item.style.display = isMatch ? 'flex' : 'none';
                            if (isMatch) visibleCount++;
                        });

                        // Hiển thị thông báo nếu không tìm thấy
                        const noResults = document.getElementById('noResults');
                        if (visibleCount === 0 && searchValue !== '') {
                            if (!noResults) {
                                const msg = document.createElement('div');
                                msg.id = 'noResults';
                                msg.style.cssText = 'text-align: center; padding: 20px; color: #9ca3af;';
                                msg.textContent = '❌ Không tìm thấy thành viên nào';
                                document.getElementById('membersContainer').appendChild(msg);
                            }
                        } else if (noResults) {
                            noResults.remove();
                        }
                    });
                </script>
            </div>

            <!-- Nút Hành Động -->
            <div class="btn-group">
                <button type="submit" class="btn-submit">✓ Cập Nhật</button>
                <a href="{{ route('clubs.show', $club) }}" class="btn-cancel">← Quay Lại</a>
                <button type="button" class="btn-delete" style="width: 100%;" onclick="if(confirm('Bạn chắc chắn muốn xóa câu lạc bộ/nhóm này?')) { document.querySelector('#deleteForm').submit(); }">🗑️ Xóa</button>
            </div>
        </form>
        <form action="{{ route('clubs.destroy', $club) }}" method="POST" style="flex: 1; display: flex;" id="deleteForm">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

@endsection
