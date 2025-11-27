@extends('layouts.app')
@section('title', 'Thêm giảng viên')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h2>Thêm giảng viên</h2>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.instructors.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên giảng viên</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Nhập tên giảng viên">
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

                    <!-- Experience -->
                    <div class="mb-3">
                        <label for="experience" class="form-label">Kinh nghiệm giảng dạy</label>
                        <textarea name="experience" id="experience" class="form-control @error('experience') is-invalid @enderror" 
                            style="min-height:150px;" placeholder="Mô tả kinh nghiệm giảng dạy">{{ old('experience') }}</textarea>
                        @error('experience')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ward -->
                    <div class="mb-3">
                        <label for="ward" class="form-label">Phường</label>
                        <input type="text" name="ward" id="ward" class="form-control @error('ward') is-invalid @enderror"
                            value="{{ old('ward') }}" placeholder="Nhập tên phường">
                        @error('ward')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Province -->
                    <div class="mb-3">
                        <label for="province_id" class="form-label">Tỉnh, TP</label>
                        <select name="province_id" id="province_id" class="form-control @error('province_id') is-invalid @enderror">
                            <option value="">-- Chọn tỉnh, TP --</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('province_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <button type="submit" class="btn btn-primary">Tạo</button>
                    <a href="{{ route('admin.instructors.index') }}" class="btn btn-secondary">Hủy</a>
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
