@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Sửa tin tức</h2>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề</label>
                        <input type="text" name="title" id="title" class="form-control"
                            value="{{ $news->title }}">
                    </div>

                    <!-- Slug -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" value="{{ $news->slug }}"
                            readonly>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung</label>
                        <textarea name="content" id="content" class="form-control" style="min-height:400px;">{{ $news->content }}</textarea>
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Hình ảnh</label>
                        <input type="file" name="image" id="image" class="form-control">
                        <div class="mt-2">
                            <img id="preview-image" src="{{ $news->image ? asset('storage/' . $news->image) : '#' }}"
                                alt="Preview Image" style="max-width:200px;"
                                {{ $news->image ? '' : 'style=display:none;' }}>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.tiny.cloud/1/{{ env('TINYMCE') }}/tinymce/8/tinymce.min.js" referrerpolicy="origin"
        crossorigin="anonymous"></script>

    <script>
        tinymce.init({
            selector: 'textarea',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        });

        // Hàm slug chuẩn tiếng Việt
        function toSlug(str) {
            str = str.toLowerCase();
            const from = "áàạảãâấầậẩẫăắằặẳẵéèẹẻẽêếềệểễíìịỉĩóòọỏõôốồộổỗơớờợởỡúùụủũưứừựửữýỳỵỷỹđ";
            const to = "aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooooouuuuuuuuuuuyyyyyd";

            for (let i = 0; i < from.length; i++) {
                str = str.replace(new RegExp(from[i], 'g'), to[i]);
            }

            str = str.replace(/[^a-z0-9\s-]/g, '');
            str = str.replace(/\s+/g, '-');
            str = str.replace(/-+/g, '-');
            str = str.replace(/^-+|-+$/g, '');

            return str;
        }

        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');

        titleInput.addEventListener('input', function() {
            slugInput.value = toSlug(this.value);
        });

        // Preview image
        const imageInput = document.getElementById('image');
        const previewImage = document.getElementById('preview-image');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
