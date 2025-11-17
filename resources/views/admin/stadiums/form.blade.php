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
    <form method="POST" action="{{ isset($stadium) ? route('admin.stadiums.update', $stadium) : route('admin.stadiums.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($stadium))
            @method('PUT')
        @endif

        <!-- Row 1: Name and Status -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Name -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Stadium Name *</label>
                <input type="text" name="name" class="form-control" value="{{ $stadium->name ?? old('name') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <!-- Status -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Status *</label>
                <select name="status" class="form-control" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="active" {{ (isset($stadium) && $stadium->status === 'active') || old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ (isset($stadium) && $stadium->status === 'inactive') || old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Description</label>
            <textarea name="description" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $stadium->description ?? old('description') }}</textarea>
        </div>

        <!-- Row 2: Address -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Address *</label>
            <input type="text" name="address" class="form-control" value="{{ $stadium->address ?? old('address') }}" required
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Row 3: Contact Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Phone</label>
                <input type="tel" name="phone" class="form-control" value="{{ $stadium->phone ?? old('phone') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Email</label>
                <input type="email" name="email" class="form-control" value="{{ $stadium->email ?? old('email') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Website</label>
                <input type="url" name="website" class="form-control" value="{{ $stadium->website ?? old('website') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Row 4: Courts Count, Hours -->
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Number of Courts *</label>
                <input type="number" name="courts_count" class="form-control" value="{{ $stadium->courts_count ?? old('courts_count', 1) }}" min="1" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Opening Hours</label>
                <input type="text" name="opening_hours" class="form-control" value="{{ $stadium->opening_hours ?? old('opening_hours') }}" placeholder="e.g. 06:00 - 22:00"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Banner Image -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Banner Image</label>
            <small style="color: #64748b; display: block; margin-bottom: 8px;">Recommended size: 1920x600px (JPG, PNG, WebP, max 2MB)</small>
            @if(isset($stadium) && $stadium->getFirstMedia('banner'))
                <div style="margin-bottom: 10px;">
                    <img src="{{ $stadium->getFirstMedia('banner')->getUrl() }}" alt="{{ $stadium->name }}" style="max-width: 300px; max-height: 150px; border-radius: 6px; border: 1px solid #e2e8f0;">
                </div>
            @endif
            <input type="file" name="banner" class="form-control" accept="image/jpeg,image/png,image/webp"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Gallery Images -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Gallery Images</label>
            <small style="color: #64748b; display: block; margin-bottom: 8px;">Upload multiple images (JPG, PNG, WebP, max 2MB each)</small>
            
            @if(isset($stadium) && $stadium->getMedia('images')->count() > 0)
                <div style="margin-bottom: 15px;">
                    <h6 style="color: #1e293b; font-weight: 600; margin-bottom: 10px;">Current Images ({{ $stadium->getMedia('images')->count() }})</h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;">
                        @foreach($stadium->getMedia('images') as $image)
                            <div style="position: relative; border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; transition: opacity 0.3s;" 
                                id="media-{{ $image->id }}" class="media-item">
                                <img src="{{ $image->getUrl() }}" alt="" style="width: 100%; height: 150px; object-fit: cover;">
                                <button type="button" class="btn-mark-delete" data-id="{{ $image->id }}" 
                                    style="position: absolute; top: 5px; right: 5px; background-color: rgba(220, 38, 38, 0.9); color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 12px; cursor: pointer;">
                                    ✕ Xóa
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <input type="file" name="gallery[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Hidden input for deleted media IDs -->
        <input type="hidden" name="deleted_media_ids" id="deleted_media_ids" value="">

        <!-- Buttons -->
        <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('admin.stadiums.index') }}" class="btn" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">Cancel</a>
            <button type="submit" class="btn" style="background-color: #00D9B5; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                {{ isset($stadium) ? 'Update Stadium' : 'Create Stadium' }}
            </button>
        </div>
    </form>
</div>

<script>
// Track marked for deletion
const deletedMediaIds = new Set();

document.querySelectorAll('.btn-mark-delete').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const mediaId = this.dataset.id;
        const mediaElement = document.getElementById(`media-${mediaId}`);
        
        if (deletedMediaIds.has(mediaId)) {
            // Undo deletion
            deletedMediaIds.delete(mediaId);
            mediaElement.style.opacity = '1';
            mediaElement.style.pointerEvents = 'auto';
            this.textContent = '✕ Xóa';
            this.style.backgroundColor = 'rgba(220, 38, 38, 0.9)';
        } else {
            // Mark for deletion
            deletedMediaIds.add(mediaId);
            mediaElement.style.display = 'none';
        }
        
        // Update hidden input
        document.getElementById('deleted_media_ids').value = Array.from(deletedMediaIds).join(',');
    });
});

</script>
