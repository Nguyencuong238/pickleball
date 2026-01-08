{{-- Create Post Card - Only visible to management (creator, admin, moderator) --}}
@if($canPost)
<div class="create-post-card">
    <div class="create-post-header">
        @if(Auth::user()->avatar)
            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="user-avatar">
        @else
            <div class="user-avatar user-avatar-placeholder">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
        @endif
        <button class="create-post-input" @click="showCreateModal = true; editingPost = null; resetForm()">
            Bạn đang nghĩ gì về Pickleball?
        </button>
    </div>
    <div class="create-post-actions">
        <button class="post-action-btn" data-type="photo" @click="showCreateModal = true; editingPost = null; resetForm(); mediaType = 'images'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
            <span>Ảnh</span>
        </button>
        <button class="post-action-btn" data-type="video" @click="showCreateModal = true; editingPost = null; resetForm(); mediaType = 'video'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="23 7 16 12 23 17 23 7"/>
                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
            </svg>
            <span>Video</span>
        </button>
        <button class="post-action-btn" data-type="event" @click="showCreateModal = true; editingPost = null; resetForm(); mediaType = 'youtube'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/>
                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>
            </svg>
            <span>YouTube</span>
        </button>
    </div>
</div>
@endif
