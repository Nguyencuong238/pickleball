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
    </script>
@endsection
