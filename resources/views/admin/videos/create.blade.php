@extends('layouts.app')
@section('title', 'Thêm video Pickleball')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h2>Thêm video Pickleball</h2>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Danh mục</label>
                        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Instructor -->
                    <div class="mb-3">
                        <label for="instructor_id" class="form-label">Giảng viên</label>
                        <select name="instructor_id" id="instructor_id" class="form-control @error('instructor_id') is-invalid @enderror">
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                    {{ $instructor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('instructor_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên Video</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Nhập tên video">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Ảnh</label>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                        <div class="mt-2">
                            <img id="preview-image" src="#" alt="Preview Image"
                                style="display:none; max-width: 200px;">
                        </div>
                        @error('image')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô Tả</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                            style="min-height:150px;" placeholder="Nhập mô tả video">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Video Link -->
                    <div class="mb-3">
                        <label for="video_link" class="form-label">Link Video (Youtube)</label>
                        <input type="text" name="video_link" id="video_link" class="form-control @error('video_link') is-invalid @enderror"
                            value="{{ old('video_link') }}" placeholder="https://www.youtube.com/watch?v=...">
                        @error('video_link')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Duration -->
                    <div class="mb-3">
                        <label for="duration" class="form-label">Thời lượng (HH:MM:SS)</label>
                        <input type="text" name="duration" id="duration" class="form-control @error('duration') is-invalid @enderror"
                            value="{{ old('duration') }}" placeholder="25:30">
                        @error('duration')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Level -->
                    <div class="mb-3">
                        <label for="level" class="form-label">Trình độ</label>
                        <select name="level" id="level" class="form-control @error('level') is-invalid @enderror">
                            <option value="">-- Chọn trình độ --</option>
                            <option value="Người mới" {{ old('level') == 'Người mới' ? 'selected' : '' }}>Người mới</option>
                            <option value="Trung bình" {{ old('level') == 'Trung bình' ? 'selected' : '' }}>Trung bình</option>
                            <option value="Nâng cao" {{ old('level') == 'Nâng cao' ? 'selected' : '' }}>Nâng cao</option>
                            <option value="Chuyên gia" {{ old('level') == 'Chuyên gia' ? 'selected' : '' }}>Chuyên gia</option>
                        </select>
                        @error('level')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Views Count -->
                    <div class="mb-3">
                        <label for="views_count" class="form-label">Lượt xem</label>
                        <input type="number" name="views_count" id="views_count" class="form-control @error('views_count') is-invalid @enderror"
                            value="{{ old('views_count') }}" min="0" placeholder="0">
                        @error('views_count')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Rating -->
                    <div class="mb-3">
                        <label for="rating" class="form-label">Đánh giá (0-5)</label>
                        <input type="number" name="rating" id="rating" class="form-control @error('rating') is-invalid @enderror"
                            value="{{ old('rating') }}" min="0" max="5" step="0.1" placeholder="4.9">
                        @error('rating')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Rating Count -->
                    <div class="mb-3">
                        <label for="rating_count" class="form-label">Số lượng đánh giá</label>
                        <input type="number" name="rating_count" id="rating_count" class="form-control @error('rating_count') is-invalid @enderror"
                            value="{{ old('rating_count') }}" min="0" placeholder="234">
                        @error('rating_count')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Chapters -->
                    <div class="mb-3">
                        <label for="chapters" class="form-label">Chương (Chapters)</label>
                        <div id="chaptersContainer">
                            <div class="chapter-input-group mb-2">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control chapter-time" placeholder="00:00" value="00:00">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control chapter-title" placeholder="Tiêu đề chương">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control chapter-duration" placeholder="3:00">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-chapter">−</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" id="btnAddChapter">+ Thêm chương</button>
                        <input type="hidden" name="chapters" id="chaptersInput">
                        @error('chapters')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <button type="submit" class="btn btn-primary">Tạo</button>
                    <a href="{{ route('admin.videos.index') }}" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preview image
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('preview-image').src = event.target.result;
                    document.getElementById('preview-image').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Chapters management
        function convertTimeToSeconds(timeStr) {
            const parts = timeStr.split(':').map(Number);
            if (parts.length === 2) return parts[0] * 60 + parts[1];
            if (parts.length === 3) return parts[0] * 3600 + parts[1] * 60 + parts[2];
            return 0;
        }

        function getChapters() {
            const chapters = [];
            document.querySelectorAll('.chapter-input-group').forEach(group => {
                const time = group.querySelector('.chapter-time').value;
                const title = group.querySelector('.chapter-title').value;
                const duration = group.querySelector('.chapter-duration').value;
                
                if (title.trim()) {
                    chapters.push({
                        time: convertTimeToSeconds(time),
                        start_time: time,
                        title: title,
                        duration: duration
                    });
                }
            });
            return chapters;
        }

        function updateChaptersInput() {
            const chapters = getChapters();
            document.getElementById('chaptersInput').value = JSON.stringify(chapters);
        }

        document.getElementById('btnAddChapter').addEventListener('click', function() {
            const container = document.getElementById('chaptersContainer');
            const newGroup = document.createElement('div');
            newGroup.className = 'chapter-input-group mb-2';
            newGroup.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" class="form-control chapter-time" placeholder="00:00">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control chapter-title" placeholder="Tiêu đề chương">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control chapter-duration" placeholder="3:00">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-chapter">−</button>
                    </div>
                </div>
            `;
            container.appendChild(newGroup);

            newGroup.querySelector('.btn-remove-chapter').addEventListener('click', function() {
                newGroup.remove();
                updateChaptersInput();
            });

            newGroup.querySelectorAll('input').forEach(input => {
                input.addEventListener('change', updateChaptersInput);
            });
        });

        document.querySelectorAll('.btn-remove-chapter').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.chapter-input-group').remove();
                updateChaptersInput();
            });
        });

        document.querySelectorAll('.chapter-input-group input').forEach(input => {
            input.addEventListener('change', updateChaptersInput);
        });

        // Initialize chapters input on form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            updateChaptersInput();
        });
    </script>
@endsection
