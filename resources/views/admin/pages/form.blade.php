@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validation Errors:</strong>
        <ul style="margin-bottom: 0; margin-top: 10px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <form method="POST" id="pageForm">
        @csrf
        @if(isset($page))
            @method('PUT')
        @endif

        <!-- Row 1: Title and Status -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Title -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Page Title *</label>
                <input type="text" name="title" class="form-control" value="{{ $page->title ?? old('title') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <!-- Status -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Status *</label>
                <select name="status" class="form-control" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="draft" {{ (isset($page) && $page->status === 'draft') || old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ (isset($page) && $page->status === 'published') || old('status') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
        </div>

        <!-- Slug -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Slug (URL)</label>
            <input type="text" name="slug" class="form-control" value="{{ $page->slug ?? old('slug') }}"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: monospace;" placeholder="auto-generated from title">
            <small style="color: #6b7280; display: block; margin-top: 5px;">Leave blank to auto-generate from title</small>
        </div>

        <!-- Content -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Content *</label>
            <textarea id="contentEditor" name="content" class="form-control" rows="12" required
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: 'Monaco', monospace;">{{ $page->content ?? old('content') }}</textarea>
        </div>

        <!-- Row 2: SEO -->
        <div style="margin-bottom: 20px;">
            <h5 style="color: #1e293b; margin-bottom: 15px; font-weight: 600;">SEO Settings</h5>
            
            <!-- Meta Description -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="2"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;" placeholder="Brief description for search engines">{{ $page->meta_description ?? old('meta_description') }}</textarea>
                <small style="color: #6b7280; display: block; margin-top: 5px;">Recommended: 50-160 characters</small>
            </div>

            <!-- Meta Keywords -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" value="{{ $page->meta_keywords ?? old('meta_keywords') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;" placeholder="keyword1, keyword2, keyword3">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Separate multiple keywords with commas</small>
            </div>
        </div>

        <!-- Buttons -->
        <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('admin.pages.index') }}" class="btn" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">Cancel</a>
            <button type="submit" class="btn" style="background-color: #00D9B5; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                {{ isset($page) ? 'Update Page' : 'Create Page' }}
            </button>
        </div>
    </form>
</div>

<!-- Simple TinyMCE setup for content editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#contentEditor',
        menubar: true,
        plugins: 'link image code lists',
        toolbar: 'undo redo | bold italic underline strikethrough | bullist numlist | link image | code',
        height: 400
    });
</script>
