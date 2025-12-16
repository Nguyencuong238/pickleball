<!-- Gallery Media Uploader Component -->
<?php
    // Parse file extensions and their MIME types
    $extensions = array_map('strtolower', array_map('trim', explode(',', $rules)));
    $mediaRules = implode(',', $extensions);
    
    $mimeMap = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
    ];
    
    $mimes = [];
    foreach ($extensions as $ext) {
        $mimes[] = $mimeMap[$ext] ?? "image/$ext";
    }
    
    $acceptMimes = implode(',', $mimes);
    $existingMedia = $model->getMedia($collection ?? 'default');
    $uniqueId = uniqid('gallery-');
    $isMultiple = @$maxItems != 1;
    
    // Prepare existing media IDs
    $existingMediaIds = $existingMedia->count() > 0
        ? implode(',', $existingMedia->pluck('id')->map(fn($id) => (string)$id)->toArray())
        : '';
?>

<style scoped>
    .gallery-media { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
    .gallery-media-item { position: relative; border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; transition: opacity 0.3s, border-color 0.3s; }
    .gallery-media-item img { width: 100%; height: 150px; object-fit: cover; display: block; }
    .gallery-media-item.new-media { border: 2px dashed #60a5fa; }
    .btn-delete-media { position: absolute; top: 5px; right: 5px; background-color: rgba(220, 38, 38, 0.9); color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 12px; cursor: pointer; transition: background-color 0.2s; }
    .btn-delete-media:hover { background-color: rgba(220, 38, 38, 1); }
    .gallery-file-input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; cursor: pointer; }
</style>

<div class="gallery-uploader-container" id="<?php echo e($uniqueId); ?>">
    <small style="color: #64748b; display: block; margin-bottom: 8px;">
        Thêm <?php if($isMultiple): ?> nhiều <?php endif; ?> ảnh 
        (<?php echo e(strtoupper($rules)); ?> - tối đa 2MB / ảnh)
    </small>

    <!-- Media Gallery -->
    <div style="margin-bottom: 15px;">
        <div class="gallery-media">
            <?php $__empty_1 = true; $__currentLoopData = $existingMedia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="gallery-media-item" id="existing-media-<?php echo e($media->id); ?>" data-media-id="<?php echo e($media->id); ?>">
                    <img src="<?php echo e($media->getUrl()); ?>" alt="media-<?php echo e($media->id); ?>">
                    <button type="button" class="btn-delete-media btn-delete-existing" data-id="<?php echo e($media->id); ?>">
                        ✕ Xóa
                    </button>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- File Input -->
    <input 
        type="file" 
        class="gallery-file-input" 
        accept="<?php echo e($acceptMimes); ?>"
        data-media-rules="<?php echo e($mediaRules); ?>"
        data-gallery-id="<?php echo e($uniqueId); ?>"
        <?php if($isMultiple): ?> multiple <?php endif; ?>>

    <!-- Hidden input for tracking media IDs -->
    <input 
        type="hidden" 
        name="<?php echo e($name); ?>" 
        class="media-ids" 
        value="<?php echo e($existingMediaIds); ?>"
        data-media-id="<?php echo e($uniqueId); ?>">
</div>

<script>
    if (!window.GalleryMediaUploader) {
        class GalleryMediaUploader {
            constructor(fileInput, container, csrfToken) {
                this.fileInput = fileInput;
                this.container = container;
                this.csrfToken = csrfToken;
                this.mediaIdsInput = container.querySelector('.media-ids');
                this.galleryMedia = container.querySelector('.gallery-media');
                this.isMultiple = fileInput.hasAttribute('multiple');
                this.mediaRules = fileInput.dataset.mediaRules || '';
            }

            init() {
                this.attachUploadListener();
                this.attachDeleteListeners();
            }

            attachUploadListener() {
                this.fileInput.addEventListener('change', (e) => this.handleUpload(e));
            }

            async handleUpload(e) {
                const files = Array.from(e.target.files);
                if (!files.length) return;
                for (const file of files) await this.uploadFile(file);
                this.fileInput.value = '';
            }

            async uploadFile(file) {
                const formData = new FormData();
                formData.append('rules', this.mediaRules);
                formData.append('media[]', file);
                try {
                    const response = await fetch('/api/upload-media', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken },
                        body: formData,
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        console.error(`Upload error (${response.status}):`, data.message || response.statusText);
                        alert(`Upload failed: ${data.message || response.statusText}`);
                        return;
                    }
                    
                    if (data.success && data.media.length) {
                        this.addMediaItem(data.media[0]);
                    }
                } catch (error) {
                    console.error('Upload failed:', error);
                    alert('Upload failed: ' + error.message);
                }
            }

            addMediaItem(media) {
                if (!this.isMultiple) this.galleryMedia.innerHTML = '';
                const div = this.createMediaElement(media);
                this.galleryMedia.appendChild(div);
                this.addMediaId(media.id);
            }

            createMediaElement(media) {
                const div = document.createElement('div');
                div.className = 'gallery-media-item new-media';
                div.dataset.mediaId = media.id;

                const img = document.createElement('img');
                img.src = media.url;
                img.alt = `media-${media.id}`;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-delete-media btn-delete-new';
                btn.textContent = '✕ Xóa';
                btn.addEventListener('click', (e) => this.deleteNewMedia(e, div, media.id));

                div.appendChild(img);
                div.appendChild(btn);
                return div;
            }

            async deleteNewMedia(e, element, mediaId) {
                e.preventDefault();
                try {
                    const response = await fetch(`/api/delete-media/${mediaId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken },
                    });
                    if ((await response.json()).success) {
                        element.remove();
                        this.removeMediaId(mediaId);
                    }
                } catch (error) {
                    console.error('Delete failed:', error);
                }
            }

            attachDeleteListeners() {
                this.container.querySelectorAll('.btn-delete-existing').forEach((btn) => {
                    btn.addEventListener('click', (e) => this.deleteExistingMedia(e, btn));
                });
            }

            deleteExistingMedia(e, button) {
                e.preventDefault();
                const mediaId = button.dataset.id;
                const mediaElement = document.getElementById(`existing-media-${mediaId}`);
                
                // Get current IDs from input (these are IDs to KEEP)
                const currentIds = this.mediaIdsInput.value ? this.mediaIdsInput.value.split(',') : [];
                const mediaIdStr = mediaId.toString();
                
                // Check if ID is in the keep list
                if (currentIds.includes(mediaIdStr)) {
                    // ID is in list - remove it (mark for deletion)
                    currentIds.splice(currentIds.indexOf(mediaIdStr), 1);
                    // Hide the preview
                    if (mediaElement) {
                        mediaElement.style.display = 'none';
                    }
                } else {
                    // ID not in list - add it back (undo deletion)
                    currentIds.push(mediaIdStr);
                    // Show the preview
                    if (mediaElement) {
                        mediaElement.style.display = '';
                    }
                }
                
                this.mediaIdsInput.value = currentIds.join(',');
            }

            addMediaId(mediaId) {
                const currentIds = this.isMultiple
                    ? (this.mediaIdsInput.value ? this.mediaIdsInput.value.split(',') : [])
                    : [];
                const mediaIdStr = mediaId.toString();
                if (!currentIds.includes(mediaIdStr)) {
                    currentIds.push(mediaIdStr);
                    this.mediaIdsInput.value = currentIds.join(',');
                }
            }

            removeMediaId(mediaId) {
                const currentIds = this.mediaIdsInput.value ? this.mediaIdsInput.value.split(',') : [];
                const mediaIdStr = mediaId.toString();
                const index = currentIds.indexOf(mediaIdStr);
                if (index > -1) {
                    currentIds.splice(index, 1);
                    this.mediaIdsInput.value = currentIds.join(',');
                }
            }
        }

        window.GalleryMediaUploader = GalleryMediaUploader;
    }

    // Initialize uploader for a specific gallery ID
     window.initGalleryUploader = function(galleryId) {
         const fileInput = document.querySelector(`[data-gallery-id="${galleryId}"]`);
         const container = document.getElementById(galleryId);
         
         // Get CSRF token from multiple sources
         let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                         document.querySelector('input[name="_token"]')?.value || 
                         '<?php echo e(csrf_token()); ?>';
         
         if (fileInput && container) {
             const uploader = new GalleryMediaUploader(fileInput, container, csrfToken);
             uploader.init();
         }
     };
     
     // Auto-initialize all gallery uploaders (for page load and AJAX)
     function initAllGalleryUploaders() {
         document.querySelectorAll('.gallery-uploader-container').forEach(container => {
             const uniqueId = container.id;
             // Only initialize if not already initialized (check for data attribute)
             if (!container.dataset.initialized) {
                 window.initGalleryUploader(uniqueId);
                 container.dataset.initialized = 'true';
             }
         });
     }
     
     if (document.readyState === 'loading') {
         document.addEventListener('DOMContentLoaded', initAllGalleryUploaders);
     } else {
         initAllGalleryUploaders();
     }
     
     // Also initialize when new content is added to the DOM (AJAX)
     if (window.MutationObserver) {
         const observer = new MutationObserver(() => {
             initAllGalleryUploaders();
         });
         observer.observe(document.body, { childList: true, subtree: true });
     }
</script>
<?php /**PATH D:\laragon\www\pickleball_booking\resources\views/components/media-uploader.blade.php ENDPATH**/ ?>