# Phase 05: Frontend Views

**Priority:** Critical
**Status:** Pending
**Depends on:** Phase 04

---

## Context

Replace existing `clubs/show.blade.php` with new design from `clubs/newpage.blade.php`. Add Alpine.js components for:
- Post feed with infinite scroll
- Create/edit post modal
- Reactions with picker
- Comments with replies

CSS already exists in `public/assets/css/styles-club.css`.

---

## Related Files

**Modify:**
- `resources/views/clubs/show.blade.php` - Replace with newpage design
- `app/Http/Controllers/ClubController.php` - Update show() to pass required data

**Create:**
- `resources/views/clubs/posts/_feed.blade.php`
- `resources/views/clubs/posts/_post-card.blade.php`
- `resources/views/clubs/posts/_create-modal.blade.php`
- `resources/views/clubs/posts/_comments.blade.php`
- `resources/views/clubs/posts/show.blade.php` (SEO single post page)

---

## Implementation Steps

### Step 1: Update ClubController show()

Modify `app/Http/Controllers/ClubController.php`:

```php
public function show(Club $club)
{
    $club->load(['creator', 'members', 'provinces', 'activities']);

    $user = Auth::user();
    $membership = null;
    $canPost = false;

    if ($user) {
        $member = $club->members()->where('user_id', $user->id)->first();
        if ($member) {
            $membership = $member->pivot->role;
            $canPost = in_array($membership, ['creator', 'admin', 'moderator']);
        }
    }

    // Get management team (creator, admin, moderator)
    $managementTeam = $club->members()
        ->wherePivotIn('role', ['creator', 'admin', 'moderator'])
        ->get();

    return view('clubs.show', compact(
        'club',
        'membership',
        'canPost',
        'managementTeam'
    ));
}
```

### Step 2: Create show.blade.php (based on newpage)

Replace entire content of `resources/views/clubs/show.blade.php` with dynamic version:

```blade
@extends('layouts.front')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-club.css') }}">

<section class="club-cover">
    <div class="cover-image">
        @if($club->image)
            <img src="{{ asset('storage/' . $club->image) }}" alt="{{ $club->name }}" style="width:100%;height:100%;object-fit:cover;">
        @else
            <svg viewBox="0 0 1200 300" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
                <defs>
                    <linearGradient id="coverGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#00D9B5;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#006699;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <rect fill="url(#coverGrad)" width="1200" height="300"/>
            </svg>
        @endif
    </div>
    <div class="container">
        <div class="club-header-info">
            <div class="club-avatar">
                @if($club->image)
                    <img src="{{ asset('storage/' . $club->image) }}" alt="{{ $club->name }}">
                @else
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 120'%3E%3Ccircle cx='60' cy='60' r='60' fill='%23fff'/%3E%3Ccircle cx='60' cy='60' r='55' fill='%2300D9B5'/%3E%3Ctext x='60' y='72' font-size='36' font-weight='bold' text-anchor='middle' fill='white'%3E{{ strtoupper(substr($club->name, 0, 2)) }}%3C/text%3E%3C/svg%3E" alt="{{ $club->name }}">
                @endif
            </div>
            <div class="club-title-section">
                <h1 class="club-name">{{ $club->name }}</h1>
                @if($club->description)
                    <p class="club-tagline">{{ Str::limit($club->description, 100) }}</p>
                @endif
                <div class="club-stats-row">
                    <span class="club-stat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        <strong>{{ $club->members->count() }}</strong> thanh vien
                    </span>
                    <span class="club-stat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <strong>{{ $club->activities->count() }}</strong> su kien
                    </span>
                    @if($club->provinces->first())
                        <span class="club-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $club->provinces->first()->name }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="club-actions">
                @include('clubs._action-buttons', ['club' => $club, 'membership' => $membership])
            </div>
        </div>
    </div>
</section>

<!-- Club Navigation Tabs -->
<section class="club-nav-tabs">
    <div class="container">
        <div class="tabs-wrapper">
            <button class="tab-btn active" data-tab="timeline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="17" y1="10" x2="3" y2="10"/>
                    <line x1="21" y1="6" x2="3" y2="6"/>
                    <line x1="21" y1="14" x2="3" y2="14"/>
                    <line x1="17" y1="18" x2="3" y2="18"/>
                </svg>
                Bang tin
            </button>
            <button class="tab-btn" data-tab="about">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Gioi thieu
            </button>
            <button class="tab-btn" data-tab="events">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                </svg>
                Su kien
            </button>
            <button class="tab-btn" data-tab="members">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
                Thanh vien
            </button>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="club-main" x-data="postFeed('{{ route('clubs.posts.index', $club) }}')" x-init="loadPosts()">
    <div class="container">
        <div class="club-layout">
            <!-- Left Column - Timeline -->
            <div class="timeline-column">
                @if($canPost)
                    @include('clubs.posts._create-card')
                @endif

                <!-- Timeline Posts -->
                <div class="timeline-posts">
                    @include('clubs.posts._feed')
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="sidebar-column">
                @include('clubs._sidebar', ['club' => $club, 'managementTeam' => $managementTeam])
            </div>
        </div>
    </div>
</main>

<!-- Create Post Modal -->
@if($canPost)
    @include('clubs.posts._create-modal', ['club' => $club])
@endif

@include('clubs.posts._scripts')
@endsection
```

### Step 3: Create partials directory and files

Create `resources/views/clubs/posts/` folder and add:

**_create-card.blade.php:**
```blade
<!-- Create Post Card -->
<div class="create-post-card">
    <div class="create-post-header">
        <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'data:image/svg+xml,...' }}" alt="Your avatar" class="user-avatar">
        <button class="create-post-input" @click="openModal = true">
            Ban dang nghi gi ve Pickleball?
        </button>
    </div>
    <div class="create-post-actions">
        <button class="post-action-btn" data-type="photo" @click="openModal = true; mediaType = 'images'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
            <span>Anh</span>
        </button>
        <button class="post-action-btn" data-type="video" @click="openModal = true; mediaType = 'video'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="23 7 16 12 23 17 23 7"/>
                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
            </svg>
            <span>Video</span>
        </button>
    </div>
</div>
```

**_feed.blade.php:**
```blade
<!-- Posts rendered by Alpine -->
<template x-for="post in posts" :key="post.id">
    <article class="post-card" :class="{ 'pinned': post.is_pinned }">
        <!-- Pinned Badge -->
        <template x-if="post.is_pinned">
            <div class="post-pinned-badge">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
                </svg>
                Bai viet ghim
            </div>
        </template>

        <!-- Post Header -->
        <div class="post-header">
            <img :src="post.author.avatar ? '/storage/' + post.author.avatar : getDefaultAvatar(post.author.name)" :alt="post.author.name" class="post-avatar">
            <div class="post-author-info">
                <div class="post-author">
                    <span class="author-name" x-text="post.author.name"></span>
                    <template x-if="post.author_role">
                        <span class="author-badge" :class="getRoleBadgeClass(post.author_role)" x-text="getRoleLabel(post.author_role)"></span>
                    </template>
                </div>
                <div class="post-meta">
                    <span class="post-time" x-text="formatTime(post.created_at)"></span>
                    <span class="dot">[DOT]</span>
                    <span class="post-visibility">
                        <template x-if="post.visibility === 'public'">
                            <span>[GLOBE] Cong khai</span>
                        </template>
                        <template x-if="post.visibility === 'members_only'">
                            <span>[LOCK] Thanh vien</span>
                        </template>
                    </span>
                    <template x-if="post.edited_at">
                        <span class="edited-badge">[DOT] Da chinh sua</span>
                    </template>
                </div>
            </div>
            <div class="post-menu" x-data="{ menuOpen: false }">
                <button class="post-menu-btn" @click="menuOpen = !menuOpen">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="5" r="2"/>
                        <circle cx="12" cy="12" r="2"/>
                        <circle cx="12" cy="19" r="2"/>
                    </svg>
                </button>
                <div class="post-menu-dropdown" x-show="menuOpen" @click.away="menuOpen = false" x-cloak>
                    <template x-if="canEdit(post)">
                        <button @click="editPost(post); menuOpen = false">Chinh sua</button>
                    </template>
                    <template x-if="canDelete(post)">
                        <button @click="deletePost(post.id); menuOpen = false">Xoa</button>
                    </template>
                    <template x-if="canPin(post)">
                        <button @click="togglePin(post.id); menuOpen = false" x-text="post.is_pinned ? 'Bo ghim' : 'Ghim bai'"></button>
                    </template>
                    <button @click="copyLink(post.id); menuOpen = false">Sao chep lien ket</button>
                </div>
            </div>
        </div>

        <!-- Post Content -->
        <div class="post-content" x-html="post.content"></div>

        <!-- Post Media -->
        <template x-if="post.media && post.media.length > 0">
            <div class="post-media" :class="getMediaGridClass(post.media)">
                <template x-for="(media, index) in post.media" :key="media.id">
                    <div class="media-item">
                        <template x-if="media.type === 'image'">
                            <img :src="media.url" :alt="'Image ' + (index + 1)">
                        </template>
                        <template x-if="media.type === 'video'">
                            <video controls :src="media.url"></video>
                        </template>
                        <template x-if="media.type === 'youtube'">
                            <div class="video-wrapper">
                                <iframe :src="media.embed_url" frameborder="0" allowfullscreen></iframe>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        <!-- Post Stats -->
        <div class="post-stats">
            <div class="reactions" x-show="post.total_reactions > 0">
                <div class="reaction-icons">
                    <template x-if="post.reaction_counts?.like">
                        <span class="reaction like">[THUMBSUP]</span>
                    </template>
                    <template x-if="post.reaction_counts?.love">
                        <span class="reaction love">[HEART]</span>
                    </template>
                    <template x-if="post.reaction_counts?.fire">
                        <span class="reaction fire">[FIRE]</span>
                    </template>
                </div>
                <span class="reaction-count" x-text="post.total_reactions"></span>
            </div>
            <div class="engagement">
                <span x-text="post.all_comments_count + ' binh luan'"></span>
            </div>
        </div>

        <!-- Post Actions -->
        <div class="post-actions">
            <div class="reaction-wrapper" x-data="{ pickerOpen: false }">
                <button class="post-action like-btn" :class="{ 'active': post.user_reaction }"
                    @click="toggleReaction(post.id, 'like')"
                    @mouseenter="pickerOpen = true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                    </svg>
                    <span x-text="post.user_reaction ? getReactionLabel(post.user_reaction) : 'Thich'"></span>
                </button>
                <!-- Reaction Picker -->
                <div class="reaction-picker" x-show="pickerOpen" @mouseleave="pickerOpen = false" x-cloak>
                    <button @click="toggleReaction(post.id, 'like'); pickerOpen = false" title="Thich">[THUMBSUP]</button>
                    <button @click="toggleReaction(post.id, 'love'); pickerOpen = false" title="Yeu thich">[HEART]</button>
                    <button @click="toggleReaction(post.id, 'fire'); pickerOpen = false" title="Tuyet voi">[FIRE]</button>
                </div>
            </div>
            <button class="post-action comment-btn" @click="toggleComments(post.id)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>Binh luan</span>
            </button>
            <button class="post-action share-btn" @click="copyLink(post.id)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="18" cy="5" r="3"/>
                    <circle cx="6" cy="12" r="3"/>
                    <circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
                <span>Chia se</span>
            </button>
        </div>

        <!-- Comments Section -->
        @include('clubs.posts._comments')
    </article>
</template>

<!-- Loading State -->
<div x-show="loading" class="loading-indicator">
    <div class="spinner"></div>
    Dang tai...
</div>

<!-- Load More / End Message -->
<template x-if="!loading && hasMore">
    <button class="load-more-btn" @click="loadMore()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
        </svg>
        Xem them bai viet
    </button>
</template>

<template x-if="!loading && !hasMore && posts.length > 0">
    <div class="end-message">Khong con bai viet nao</div>
</template>

<template x-if="!loading && posts.length === 0">
    <div class="empty-message">Chua co bai viet nao</div>
</template>
```

**_comments.blade.php:**
```blade
<!-- Comments Section -->
<div class="post-comments" x-show="expandedComments.includes(post.id)" x-cloak>
    <!-- Comments List -->
    <template x-for="comment in getPostComments(post.id)" :key="comment.id">
        <div class="comment-item">
            <img :src="comment.user.avatar ? '/storage/' + comment.user.avatar : getDefaultAvatar(comment.user.name)" :alt="comment.user.name" class="comment-avatar">
            <div class="comment-bubble">
                <span class="comment-author" x-text="comment.user.name"></span>
                <p class="comment-text" x-text="comment.content"></p>
                <div class="comment-meta">
                    <span class="comment-time" x-text="formatTime(comment.created_at)"></span>
                    <button class="comment-action-btn" @click="replyTo = comment.id">Tra loi</button>
                    <template x-if="canDeleteComment(comment)">
                        <button class="comment-action-btn" @click="deleteComment(comment.id)">Xoa</button>
                    </template>
                </div>

                <!-- Replies -->
                <template x-if="comment.replies && comment.replies.length > 0">
                    <div class="comment-replies">
                        <template x-for="reply in comment.replies" :key="reply.id">
                            <div class="comment-item reply">
                                <img :src="reply.user.avatar ? '/storage/' + reply.user.avatar : getDefaultAvatar(reply.user.name)" class="comment-avatar">
                                <div class="comment-bubble">
                                    <span class="comment-author" x-text="reply.user.name"></span>
                                    <p class="comment-text" x-text="reply.content"></p>
                                    <div class="comment-meta">
                                        <span class="comment-time" x-text="formatTime(reply.created_at)"></span>
                                        <template x-if="canDeleteComment(reply)">
                                            <button class="comment-action-btn" @click="deleteComment(reply.id)">Xoa</button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Comment Input -->
    @auth
    <div class="comment-form-inline">
        <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'data:image/svg+xml,...' }}" alt="Your avatar" class="comment-avatar">
        <div class="comment-input-wrapper">
            <input type="text"
                :placeholder="replyTo ? 'Tra loi...' : 'Viet binh luan...'"
                class="comment-input"
                x-model="commentText"
                @keyup.enter="submitComment(post.id)">
            <button class="submit-comment-btn" @click="submitComment(post.id)">[SEND]</button>
        </div>
    </div>
    @else
    <div class="login-prompt">
        <a href="{{ route('login') }}">Dang nhap</a> de binh luan
    </div>
    @endauth
</div>
```

**_create-modal.blade.php:**
```blade
<!-- Create Post Modal -->
<div class="modal-overlay" :class="{ 'active': openModal }" @click.self="closeModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h3 x-text="editingPost ? 'Chinh sua bai viet' : 'Tao bai viet'"></h3>
            <button class="modal-close" @click="closeModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-author">
                <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'data:image/svg+xml,...' }}" alt="Your avatar" class="modal-avatar">
                <div class="modal-author-info">
                    <span class="modal-author-name">{{ Auth::user()->name }}</span>
                    <select x-model="visibility" class="visibility-selector">
                        <option value="public">[GLOBE] Cong khai</option>
                        <option value="members_only">[LOCK] Thanh vien</option>
                    </select>
                </div>
            </div>

            <!-- Content Editor (will be replaced by Tiptap in Phase 06) -->
            <div id="editor" class="post-editor" contenteditable="true"
                @input="content = $el.innerHTML"
                data-placeholder="Ban dang nghi gi ve Pickleball?"></div>

            <!-- Media Preview -->
            <div class="media-preview" x-show="previewMedia.length > 0 || youtubeUrl">
                <template x-for="(preview, index) in previewMedia" :key="index">
                    <div class="preview-item">
                        <img :src="preview" alt="Preview">
                        <button class="remove-media-btn" @click="removeMedia(index)">[X]</button>
                    </div>
                </template>
                <template x-if="youtubeUrl">
                    <div class="preview-item youtube">
                        <iframe :src="getYoutubeEmbed(youtubeUrl)" frameborder="0"></iframe>
                        <button class="remove-media-btn" @click="youtubeUrl = ''">[X]</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Media Type Selector -->
        <div class="modal-add-to-post">
            <span>Them vao bai viet</span>
            <div class="add-options">
                <button class="add-option-btn" :class="{ 'active': mediaType === 'images' }"
                    @click="mediaType = 'images'" data-type="photo" title="Them anh">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                </button>
                <button class="add-option-btn" :class="{ 'active': mediaType === 'video' }"
                    @click="mediaType = 'video'" data-type="video" title="Them video">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="23 7 16 12 23 17 23 7"/>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                    </svg>
                </button>
                <button class="add-option-btn" :class="{ 'active': mediaType === 'youtube' }"
                    @click="mediaType = 'youtube'" data-type="youtube" title="YouTube">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- File/URL Input -->
        <template x-if="mediaType === 'images'">
            <div class="media-input">
                <input type="file" id="imageInput" accept="image/*" multiple @change="handleImageSelect($event)" hidden>
                <label for="imageInput" class="file-select-btn">Chon anh (toi da 10)</label>
            </div>
        </template>
        <template x-if="mediaType === 'video'">
            <div class="media-input">
                <input type="file" id="videoInput" accept="video/*" @change="handleVideoSelect($event)" hidden>
                <label for="videoInput" class="file-select-btn">Chon video (toi da 50MB)</label>
            </div>
        </template>
        <template x-if="mediaType === 'youtube'">
            <div class="media-input">
                <input type="url" x-model="youtubeUrl" placeholder="Dan lien ket YouTube..." class="youtube-input">
            </div>
        </template>

        <div class="modal-footer">
            <button class="btn btn-primary btn-block" @click="submitPost()" :disabled="submitting || !content.trim()">
                <span x-show="!submitting" x-text="editingPost ? 'Cap nhat' : 'Dang bai'"></span>
                <span x-show="submitting">Dang xu ly...</span>
            </button>
        </div>
    </div>
</div>
```

**_scripts.blade.php:**
```blade
<script>
function postFeed(apiUrl) {
    return {
        apiUrl: apiUrl,
        posts: [],
        loading: false,
        hasMore: true,
        page: 1,
        openModal: false,
        editingPost: null,
        content: '',
        visibility: 'public',
        mediaType: null,
        previewMedia: [],
        selectedFiles: [],
        youtubeUrl: '',
        submitting: false,
        expandedComments: [],
        comments: {},
        commentText: '',
        replyTo: null,
        currentUserId: {{ Auth::id() ?? 'null' }},
        userRole: '{{ $membership ?? '' }}',

        async loadPosts() {
            if (this.loading) return;
            this.loading = true;
            try {
                const response = await fetch(`${this.apiUrl}?page=${this.page}`);
                const data = await response.json();
                if (data.success) {
                    this.posts = [...this.posts, ...data.posts];
                    this.hasMore = data.hasMore;
                    this.page = data.nextPage;
                }
            } catch (error) {
                console.error('Error loading posts:', error);
            } finally {
                this.loading = false;
            }
        },

        loadMore() {
            if (this.hasMore && !this.loading) {
                this.loadPosts();
            }
        },

        async submitPost() {
            if (!this.content.trim() || this.submitting) return;
            this.submitting = true;

            const formData = new FormData();
            formData.append('content', this.content);
            formData.append('visibility', this.visibility);
            if (this.mediaType) formData.append('media_type', this.mediaType);
            if (this.youtubeUrl) formData.append('youtube_url', this.youtubeUrl);
            this.selectedFiles.forEach(file => formData.append('images[]', file));

            const url = this.editingPost
                ? `{{ url('clubs') }}/{{ $club->slug }}/posts/${this.editingPost.id}`
                : `{{ route('clubs.posts.store', $club) }}`;

            try {
                const response = await fetch(url, {
                    method: this.editingPost ? 'POST' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    if (this.editingPost) {
                        const index = this.posts.findIndex(p => p.id === this.editingPost.id);
                        if (index !== -1) this.posts[index] = data.post;
                    } else {
                        this.posts.unshift(data.post);
                    }
                    this.closeModal();
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.submitting = false;
            }
        },

        closeModal() {
            this.openModal = false;
            this.editingPost = null;
            this.content = '';
            this.visibility = 'public';
            this.mediaType = null;
            this.previewMedia = [];
            this.selectedFiles = [];
            this.youtubeUrl = '';
        },

        editPost(post) {
            this.editingPost = post;
            this.content = post.content;
            this.visibility = post.visibility;
            this.openModal = true;
        },

        async deletePost(postId) {
            if (!confirm('Ban co chac muon xoa bai viet nay?')) return;
            try {
                const response = await fetch(`{{ url('clubs') }}/{{ $club->slug }}/posts/${postId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.posts = this.posts.filter(p => p.id !== postId);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async togglePin(postId) {
            try {
                const response = await fetch(`{{ url('clubs') }}/{{ $club->slug }}/posts/${postId}/pin`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    const post = this.posts.find(p => p.id === postId);
                    if (post) post.is_pinned = data.is_pinned;
                    // Re-sort posts
                    this.posts.sort((a, b) => {
                        if (a.is_pinned && !b.is_pinned) return -1;
                        if (!a.is_pinned && b.is_pinned) return 1;
                        return new Date(b.created_at) - new Date(a.created_at);
                    });
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async toggleReaction(postId, type) {
            try {
                const response = await fetch(`{{ url('club-posts') }}/${postId}/reactions`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ type })
                });
                const data = await response.json();
                if (data.success) {
                    const post = this.posts.find(p => p.id === postId);
                    if (post) {
                        post.user_reaction = data.type;
                        post.reaction_counts = data.counts;
                        post.total_reactions = data.total;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        toggleComments(postId) {
            const index = this.expandedComments.indexOf(postId);
            if (index === -1) {
                this.expandedComments.push(postId);
                this.loadComments(postId);
            } else {
                this.expandedComments.splice(index, 1);
            }
        },

        async loadComments(postId) {
            try {
                const response = await fetch(`{{ url('club-posts') }}/${postId}/comments`);
                const data = await response.json();
                if (data.success) {
                    this.comments[postId] = data.comments;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        getPostComments(postId) {
            return this.comments[postId] || [];
        },

        async submitComment(postId) {
            if (!this.commentText.trim()) return;
            try {
                const response = await fetch(`{{ url('club-posts') }}/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        content: this.commentText,
                        parent_id: this.replyTo
                    })
                });
                const data = await response.json();
                if (data.success) {
                    if (this.replyTo) {
                        const parent = this.comments[postId].find(c => c.id === this.replyTo);
                        if (parent) {
                            if (!parent.replies) parent.replies = [];
                            parent.replies.push(data.comment);
                        }
                    } else {
                        if (!this.comments[postId]) this.comments[postId] = [];
                        this.comments[postId].unshift(data.comment);
                    }
                    this.commentText = '';
                    this.replyTo = null;
                    // Update comment count
                    const post = this.posts.find(p => p.id === postId);
                    if (post) post.all_comments_count++;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async deleteComment(commentId) {
            if (!confirm('Xoa binh luan nay?')) return;
            try {
                const response = await fetch(`{{ url('club-post-comments') }}/${commentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) {
                    // Remove from local state
                    for (const postId in this.comments) {
                        this.comments[postId] = this.comments[postId].filter(c => {
                            if (c.replies) {
                                c.replies = c.replies.filter(r => r.id !== commentId);
                            }
                            return c.id !== commentId;
                        });
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        // Helper methods
        handleImageSelect(event) {
            const files = Array.from(event.target.files).slice(0, 10);
            this.selectedFiles = files;
            this.previewMedia = [];
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => this.previewMedia.push(e.target.result);
                reader.readAsDataURL(file);
            });
        },

        handleVideoSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedFiles = [file];
                this.previewMedia = [URL.createObjectURL(file)];
            }
        },

        removeMedia(index) {
            this.previewMedia.splice(index, 1);
            this.selectedFiles.splice(index, 1);
        },

        getDefaultAvatar(name) {
            const initials = name ? name.substring(0, 2).toUpperCase() : '??';
            return `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%2300D9B5'/%3E%3Ctext x='20' y='26' font-size='14' text-anchor='middle' fill='white'%3E${initials}%3C/text%3E%3C/svg%3E`;
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = (now - date) / 1000;
            if (diff < 60) return 'Vua xong';
            if (diff < 3600) return Math.floor(diff / 60) + ' phut truoc';
            if (diff < 86400) return Math.floor(diff / 3600) + ' gio truoc';
            if (diff < 604800) return Math.floor(diff / 86400) + ' ngay truoc';
            return date.toLocaleDateString('vi-VN');
        },

        getRoleBadgeClass(role) {
            return {
                'admin': 'admin',
                'creator': 'admin',
                'moderator': 'coach',
                'member': 'member'
            }[role] || 'member';
        },

        getRoleLabel(role) {
            return {
                'creator': 'Chu nhiem',
                'admin': 'Admin',
                'moderator': 'Dieu hanh',
                'member': 'Thanh vien'
            }[role] || 'Thanh vien';
        },

        getReactionLabel(type) {
            return { 'like': 'Da thich', 'love': 'Yeu thich', 'fire': 'Tuyet voi' }[type] || 'Thich';
        },

        getMediaGridClass(media) {
            const count = media.length;
            if (count === 1) return 'single-media';
            if (count === 2) return 'two-images';
            return 'multi-images';
        },

        getYoutubeEmbed(url) {
            const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?]+)/);
            return match ? `https://www.youtube.com/embed/${match[1]}` : '';
        },

        copyLink(postId) {
            const url = `{{ url('clubs') }}/{{ $club->slug }}/posts/${postId}`;
            navigator.clipboard.writeText(url);
            alert('Da sao chep lien ket!');
        },

        canEdit(post) {
            if (!this.currentUserId) return false;
            if (post.user_id === this.currentUserId) return true;
            return ['creator', 'admin'].includes(this.userRole);
        },

        canDelete(post) {
            if (!this.currentUserId) return false;
            if (post.user_id === this.currentUserId) return true;
            return ['creator', 'admin', 'moderator'].includes(this.userRole);
        },

        canPin(post) {
            return ['creator', 'admin'].includes(this.userRole);
        },

        canDeleteComment(comment) {
            if (!this.currentUserId) return false;
            if (comment.user_id === this.currentUserId) return true;
            return ['creator', 'admin', 'moderator'].includes(this.userRole);
        }
    };
}
</script>
```

### Step 4: Create additional partials

**_action-buttons.blade.php:**
```blade
@auth
    @if($membership === 'creator' || $membership === 'admin')
        <a href="{{ route('clubs.edit', $club) }}" class="btn btn-primary">
            [EDIT] Chinh sua
        </a>
        <a href="{{ route('clubs.join-requests', $club) }}" class="btn btn-outline">
            [LIST] Yeu cau
        </a>
    @elseif($membership)
        <button class="btn btn-outline" disabled>
            [CHECK] Da tham gia
        </button>
    @else
        @php
            $hasRequest = \App\Models\ClubJoinRequest::where(['club_id' => $club->id, 'user_id' => Auth::id()])->exists();
        @endphp
        @if($hasRequest)
            <button class="btn btn-outline" disabled>
                [CLOCK] Dang cho duyet
            </button>
        @else
            <form action="{{ route('clubs.request-join', $club) }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    [PLUS] Tham gia
                </button>
            </form>
        @endif
    @endif
@else
    <a href="{{ route('login') }}" class="btn btn-primary">
        [PLUS] Tham gia
    </a>
@endauth
<button class="btn btn-icon" onclick="navigator.share ? navigator.share({url: window.location.href}) : navigator.clipboard.writeText(window.location.href)">
    [SHARE]
</button>
```

**_sidebar.blade.php:**
```blade
<!-- Management Team Card -->
<div class="sidebar-card">
    <h3 class="sidebar-card-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
        Ban dieu hanh
    </h3>
    <div class="management-team">
        @foreach($managementTeam as $member)
        <div class="team-member">
            <img src="{{ $member->avatar ? asset('storage/' . $member->avatar) : 'data:image/svg+xml,...' }}" alt="{{ $member->name }}" class="member-avatar">
            <div class="member-info">
                <span class="member-name">{{ $member->name }}</span>
                <span class="member-role {{ $member->pivot->role }}">
                    {{ ['creator' => 'Chu nhiem', 'admin' => 'Admin', 'moderator' => 'Dieu hanh'][$member->pivot->role] ?? '' }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Members Card -->
<div class="sidebar-card">
    <div class="sidebar-card-header">
        <h3 class="sidebar-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
            Thanh vien
        </h3>
        <span class="member-count">{{ $club->members->count() }} thanh vien</span>
    </div>
    <div class="members-grid">
        @foreach($club->members->take(8) as $member)
        <a href="#" class="member-avatar-item" title="{{ $member->name }}">
            <img src="{{ $member->avatar ? asset('storage/' . $member->avatar) : 'data:image/svg+xml,...' }}" alt="{{ $member->name }}">
        </a>
        @endforeach
        @if($club->members->count() > 8)
        <a href="#" class="member-avatar-item more">
            <span>+{{ $club->members->count() - 8 }}</span>
        </a>
        @endif
    </div>
</div>

<!-- Upcoming Events Card -->
<div class="sidebar-card">
    <h3 class="sidebar-card-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
        </svg>
        Su kien sap toi
    </h3>
    <div class="upcoming-events">
        @forelse($club->activities->where('activity_date', '>=', now())->take(3) as $activity)
        <a href="{{ route('clubs.activities.show', [$club, $activity]) }}" class="event-item">
            <div class="event-date">
                <span class="day">{{ $activity->activity_date->format('d') }}</span>
                <span class="month">Th{{ $activity->activity_date->format('m') }}</span>
            </div>
            <div class="event-info">
                <h4>{{ $activity->title }}</h4>
                @if($activity->location)
                <p>{{ $activity->location }}</p>
                @endif
            </div>
        </a>
        @empty
        <p class="empty-message">Chua co su kien</p>
        @endforelse
    </div>
    @if($club->activities->count() > 0)
    <a href="{{ route('clubs.activities.index', $club) }}" class="sidebar-link">Xem tat ca su kien [ARROW]</a>
    @endif
</div>
```

### Step 5: Create SEO Single Post Page

**clubs/posts/show.blade.php:**
```blade
@extends('layouts.front')

@section('title', Str::limit(strip_tags($post->content), 60) . ' - ' . $club->name)

@section('meta')
<meta property="og:title" content="{{ Str::limit(strip_tags($post->content), 60) }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($post->content), 160) }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ route('clubs.posts.show', [$club, $post]) }}">
@if($post->media->where('type', 'image')->first())
<meta property="og:image" content="{{ asset('storage/' . $post->media->where('type', 'image')->first()->path) }}">
@elseif($club->image)
<meta property="og:image" content="{{ asset('storage/' . $club->image) }}">
@endif
<meta property="article:published_time" content="{{ $post->created_at->toIso8601String() }}">
<meta property="article:author" content="{{ $post->author->name }}">
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-club.css') }}">

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav mb-4">
        <a href="{{ route('clubs.show', $club) }}">{{ $club->name }}</a>
        <span class="separator">/</span>
        <span>Bai viet</span>
    </nav>

    <div class="single-post-layout">
        <article class="post-card single-post">
            <!-- Post Header -->
            <div class="post-header">
                <img src="{{ $post->author->avatar ? asset('storage/' . $post->author->avatar) : 'data:image/svg+xml,...' }}" alt="{{ $post->author->name }}" class="post-avatar">
                <div class="post-author-info">
                    <div class="post-author">
                        <span class="author-name">{{ $post->author->name }}</span>
                    </div>
                    <div class="post-meta">
                        <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                        <span class="dot">[DOT]</span>
                        <span class="post-visibility">
                            @if($post->visibility === 'public')
                                [GLOBE] Cong khai
                            @else
                                [LOCK] Thanh vien
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Post Content -->
            <div class="post-content">{!! $post->content !!}</div>

            <!-- Post Media -->
            @if($post->media->count() > 0)
            <div class="post-media {{ $post->media->count() > 1 ? 'multi-images' : 'single-media' }}">
                @foreach($post->media as $media)
                <div class="media-item">
                    @if($media->type === 'image')
                        <img src="{{ $media->url }}" alt="Post image">
                    @elseif($media->type === 'video')
                        <video controls src="{{ $media->url }}"></video>
                    @elseif($media->type === 'youtube')
                        <div class="video-wrapper">
                            <iframe src="{{ $media->embed_url }}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            <!-- Post Stats -->
            <div class="post-stats">
                @if($post->totalReactions > 0)
                <div class="reactions">
                    <div class="reaction-icons">
                        @if($post->reactionCounts['like'] ?? 0)
                            <span class="reaction like">[THUMBSUP]</span>
                        @endif
                        @if($post->reactionCounts['love'] ?? 0)
                            <span class="reaction love">[HEART]</span>
                        @endif
                        @if($post->reactionCounts['fire'] ?? 0)
                            <span class="reaction fire">[FIRE]</span>
                        @endif
                    </div>
                    <span class="reaction-count">{{ $post->totalReactions }}</span>
                </div>
                @endif
                <div class="engagement">
                    <span>{{ $post->comments->count() }} binh luan</span>
                </div>
            </div>

            <!-- Comments List -->
            <div class="post-comments expanded">
                @foreach($post->comments as $comment)
                <div class="comment-item">
                    <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : 'data:image/svg+xml,...' }}" alt="{{ $comment->user->name }}" class="comment-avatar">
                    <div class="comment-bubble">
                        <span class="comment-author">{{ $comment->user->name }}</span>
                        <p class="comment-text">{{ $comment->content }}</p>
                        <div class="comment-meta">
                            <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>

                        @if($comment->replies->count() > 0)
                        <div class="comment-replies">
                            @foreach($comment->replies as $reply)
                            <div class="comment-item reply">
                                <img src="{{ $reply->user->avatar ? asset('storage/' . $reply->user->avatar) : 'data:image/svg+xml,...' }}" class="comment-avatar">
                                <div class="comment-bubble">
                                    <span class="comment-author">{{ $reply->user->name }}</span>
                                    <p class="comment-text">{{ $reply->content }}</p>
                                    <div class="comment-meta">
                                        <span class="comment-time">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </article>

        <!-- Back to Club -->
        <div class="text-center mt-4">
            <a href="{{ route('clubs.show', $club) }}" class="btn btn-primary">
                [ARROW_LEFT] Quay lai {{ $club->name }}
            </a>
        </div>
    </div>
</div>
@endsection
```

---

## Todo List

- [ ] Update ClubController::show() method
- [ ] Create clubs/posts/ directory
- [ ] Create _create-card.blade.php
- [ ] Create _feed.blade.php
- [ ] Create _comments.blade.php
- [ ] Create _create-modal.blade.php
- [ ] Create _scripts.blade.php
- [ ] Create _action-buttons.blade.php
- [ ] Create _sidebar.blade.php
- [ ] Create posts/show.blade.php (SEO single post page)
- [ ] Replace show.blade.php with new design
- [ ] Test page loads without errors
- [ ] Test post creation modal opens
- [ ] Test infinite scroll loads posts

---

## Success Criteria

- [ ] Club page displays new design
- [ ] Management team can see create post card
- [ ] Posts load via AJAX
- [ ] Reactions work
- [ ] Comments expand/collapse
- [ ] Modal opens/closes correctly

---

## Next Steps

Proceed to Phase 06: Tiptap Integration
