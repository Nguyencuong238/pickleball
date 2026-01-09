@extends('layouts.front')

@section('title', $club->name . ' | Pickleball Vietnam')

@section('meta')
<meta name="description" content="{{ Str::limit($club->description, 160) }}">
<meta property="og:title" content="{{ $club->name }} | Pickleball Vietnam">
<meta property="og:description" content="{{ Str::limit($club->description, 160) }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ route('clubs.show', $club) }}">
@if($club->image)
<meta property="og:image" content="{{ asset('storage/' . $club->image) }}">
@endif
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-club.css') }}?v=1.2">

{{-- Additional styles for posts --}}
<style>
.modal-body {
    max-height: 50vh;
}
/* Avatar placeholders */
.user-avatar-placeholder,
.post-avatar-placeholder,
.comment-avatar-placeholder,
.modal-avatar-placeholder,
.member-avatar-placeholder,
.member-avatar-placeholder-grid {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    font-weight: 700;
    border-radius: 50%;
}

.user-avatar-placeholder,
.post-avatar-placeholder {
    width: 44px;
    height: 44px;
    font-size: 18px;
}

.comment-avatar-placeholder {
    width: 36px;
    height: 36px;
    font-size: 14px;
}

.comment-avatar-placeholder.small {
    width: 28px;
    height: 28px;
    font-size: 12px;
}

.modal-avatar-placeholder {
    width: 44px;
    height: 44px;
    font-size: 18px;
}

.member-avatar-placeholder {
    width: 48px;
    height: 48px;
    font-size: 18px;
}

.member-avatar-placeholder-grid {
    width: 100%;
    aspect-ratio: 1;
    font-size: 14px;
}

/* Post menu */
.post-menu-wrapper {
    position: relative;
}

.post-menu-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    min-width: 200px;
    z-index: 100;
    overflow: hidden;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md);
    background: none;
    border: none;
    font-size: var(--font-size-sm);
    color: var(--text-primary);
    cursor: pointer;
    transition: background var(--transition-base);
}

.menu-item svg {
    width: 18px;
    height: 18px;
    color: var(--text-secondary);
}

.menu-item:hover {
    background: var(--bg-light);
}

.menu-item-danger {
    color: #ef4444;
}

.menu-item-danger svg {
    color: #ef4444;
}

/* Edited badge */
.edited-badge {
    font-size: var(--font-size-xs);
    color: var(--text-light);
    font-style: italic;
}

/* Reaction picker */
.reaction-button-wrapper {
    position: relative;
    flex: 1;
}

.reaction-picker {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border-radius: 2rem;
    box-shadow: var(--shadow-lg);
    padding: 6px 12px;
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.reaction-option {
    font-size: 24px;
    background: none;
    border: none;
    cursor: pointer;
    transition: transform var(--transition-base);
    padding: 4px;
}

.reaction-option:hover {
    transform: scale(1.3);
}

.reaction-emoji {
    font-size: 20px;
}

/* Role badges */
.author-badge.admin {
    background: rgba(255, 217, 61, 0.2);
    color: #B8860B;
}

.author-badge.moderator {
    background: rgba(0, 153, 204, 0.2);
    color: #0099CC;
}

.author-badge.member {
    background: rgba(0, 217, 181, 0.1);
    color: var(--primary-color);
}

.author-badge.small {
    font-size: 10px;
    padding: 1px 6px;
}

/* Media type selector */
.media-type-selector {
    display: flex;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-md);
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--border-color);
}

.media-type-btn {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--bg-light);
    border: 2px solid transparent;
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-base);
}

.media-type-btn svg {
    width: 20px;
    height: 20px;
}

.media-type-btn.active {
    border-color: var(--primary-color);
    background: rgba(0, 217, 181, 0.1);
    color: var(--primary-color);
}

.media-type-btn:hover:not(.active) {
    background: var(--border-color);
}

/* Drop zone */
.media-upload-area {
    margin-top: var(--spacing-md);
}

.drop-zone {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    text-align: center;
    transition: all var(--transition-base);
}

.drop-zone.dragging {
    border-color: var(--primary-color);
    background: rgba(0, 217, 181, 0.05);
}

.drop-zone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--text-secondary);
    cursor: pointer;
}

.drop-zone-content svg {
    width: 48px;
    height: 48px;
    color: var(--text-light);
}

.drop-zone-content small {
    color: var(--text-light);
}

/* Media preview grid */
.media-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: var(--spacing-sm);
    margin-top: var(--spacing-md);
}

.preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: var(--radius-md);
    overflow: hidden;
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-item.removed {
    opacity: 0.4;
}

.remove-media-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 24px;
    height: 24px;
    background: rgba(0, 0, 0, 0.6);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
}

.remove-media-btn svg {
    width: 14px;
    height: 14px;
}

/* Video preview */
.video-preview {
    position: relative;
    margin-top: var(--spacing-md);
}

.video-preview video {
    width: 100%;
    border-radius: var(--radius-lg);
}

/* YouTube input */
.youtube-input-area {
    margin-top: var(--spacing-md);
}

.youtube-input-wrapper {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    background: var(--bg-light);
    border-radius: var(--radius-md);
    padding: var(--spacing-sm) var(--spacing-md);
}

.youtube-icon {
    width: 24px;
    height: 24px;
    color: #ff0000;
}

.youtube-input {
    flex: 1;
    background: none;
    border: none;
    font-size: var(--font-size-base);
    color: var(--text-primary);
}

.youtube-input:focus {
    outline: none;
}

.youtube-preview {
    position: relative;
    margin-top: var(--spacing-md);
}

.youtube-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: #282828;
    color: #ff0000;
}

.youtube-thumb svg {
    width: 40px;
    height: 40px;
}

/* Visibility dropdown */
.visibility-dropdown {
    position: relative;
}

.visibility-options {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    min-width: 220px;
    z-index: 100;
    overflow: hidden;
    margin-top: 4px;
}

.visibility-option {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md);
    background: none;
    border: none;
    text-align: left;
    cursor: pointer;
    transition: background var(--transition-base);
}

.visibility-option:hover {
    background: var(--bg-light);
}

.visibility-option svg {
    color: var(--text-secondary);
}

.visibility-option div {
    display: flex;
    flex-direction: column;
}

.visibility-option strong {
    font-size: var(--font-size-sm);
    color: var(--text-primary);
}

.visibility-option span {
    font-size: var(--font-size-xs);
    color: var(--text-light);
}

/* Comment enhancements */
.comment-content-wrapper {
    flex: 1;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.comment-replies {
    margin-top: var(--spacing-sm);
    padding-left: var(--spacing-md);
    border-left: 2px solid var(--border-color);
}

.comment-item.reply {
    margin-bottom: var(--spacing-sm);
}

.reply-form {
    display: flex;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-sm);
}

.comment-submit-btn {
    background: var(--primary-color);
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: opacity var(--transition-base);
}

.comment-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.comment-submit-btn svg {
    width: 16px;
    height: 16px;
}

.comment-action-danger {
    color: #ef4444;
}

.comment-login-prompt {
    text-align: center;
    padding: var(--spacing-md);
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
}

.comment-login-prompt a {
    color: var(--primary-color);
    font-weight: 600;
}

.load-more-comments {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
    font-weight: 600;
    cursor: pointer;
    padding: var(--spacing-sm) 0;
}

.load-more-comments:hover {
    color: var(--primary-color);
}

/* Loading states */
.post-skeleton {
    background: white;
    border-radius: var(--radius-lg);
    padding: var(--spacing-md);
}

.skeleton-header {
    display: flex;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.skeleton-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

.skeleton-info {
    flex: 1;
}

.skeleton-name {
    height: 16px;
    width: 150px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    margin-bottom: 8px;
}

.skeleton-meta {
    height: 12px;
    width: 100px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}

.skeleton-content {
    padding-top: var(--spacing-md);
}

.skeleton-line {
    height: 14px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    margin-bottom: 8px;
}

.skeleton-line.short {
    width: 60%;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.load-more-trigger {
    padding: var(--spacing-md);
    text-align: center;
}

.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
    color: var(--text-secondary);
}

.spinner {
    width: 24px;
    height: 24px;
    animation: rotate 2s linear infinite;
}

.spinner .path {
    stroke: var(--primary-color);
    stroke-linecap: round;
    animation: dash 1.5s ease-in-out infinite;
}

@keyframes rotate {
    100% { transform: rotate(360deg); }
}

@keyframes dash {
    0% {
        stroke-dasharray: 1, 150;
        stroke-dashoffset: 0;
    }
    50% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -35;
    }
    100% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -124;
    }
}

.no-more-posts,
.empty-posts {
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--text-secondary);
}

.no-more-posts svg,
.empty-posts svg {
    width: 48px;
    height: 48px;
    margin-bottom: var(--spacing-md);
    color: var(--text-light);
}

.empty-posts h3 {
    margin-bottom: var(--spacing-xs);
    color: var(--text-primary);
}

/* Toast notifications */
.toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: var(--text-primary);
    color: white;
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    z-index: 9999;
    opacity: 0;
    transition: all 0.3s ease;
}

.toast.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

.toast-success {
    background: var(--primary-color);
}

.toast-error {
    background: #ef4444;
}

/* Image grid layouts */
.post-images-grid.one-image .post-image-item img {
    max-height: 500px;
    object-fit: contain;
    background: #f5f5f5;
}

.post-images-grid.four-plus-images {
    grid-template-columns: repeat(2, 1fr);
}

.more-images-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: 700;
}

/* Lightbox */
.lightbox {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.lightbox-content img {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
}

.lightbox-close {
    position: absolute;
    top: -40px;
    right: 0;
    background: none;
    border: none;
    color: white;
    font-size: 32px;
    cursor: pointer;
}

/* Badge count */
.badge-count {
    background: #ef4444;
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 4px;
}

/* Existing media in edit mode */
.existing-media {
    margin-top: var(--spacing-md);
}

.existing-media-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-sm);
}

/* Hidden utility */
.hidden {
    display: none;
}

[x-cloak] {
    display: none !important;
}
</style>

<div x-data="postFeed()" x-init="init()">
    {{-- Club Cover --}}
    <section class="club-cover">
        <div class="cover-image">
            @if($club->image)
                <img src="{{ asset('storage/' . $club->image) }}" alt="{{ $club->name }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <svg viewBox="0 0 1200 300" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
                    <defs>
                        <linearGradient id="coverGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#00D9B5;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#0099CC;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#006699;stop-opacity:1" />
                        </linearGradient>
                        <pattern id="courtPattern" patternUnits="userSpaceOnUse" width="100" height="100">
                            <rect fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2" width="100" height="100"/>
                            <line x1="50" y1="0" x2="50" y2="100" stroke="rgba(255,255,255,0.1)" stroke-width="2"/>
                            <line x1="0" y1="50" x2="100" y2="50" stroke="rgba(255,255,255,0.1)" stroke-width="2"/>
                        </pattern>
                    </defs>
                    <rect fill="url(#coverGrad)" width="1200" height="300"/>
                    <rect fill="url(#courtPattern)" width="1200" height="300"/>
                    <circle cx="200" cy="150" r="80" fill="rgba(255,255,255,0.1)"/>
                    <circle cx="1000" cy="100" r="120" fill="rgba(255,255,255,0.05)"/>
                    <circle cx="600" cy="250" r="60" fill="rgba(255,255,255,0.08)"/>
                </svg>
            @endif
        </div>
        <div class="container">
            <div class="club-header-info">
                <div class="club-avatar">
                    @if($club->image)
                        <img src="{{ asset('storage/' . $club->image) }}" alt="{{ $club->name }}">
                    @else
                        <div style="width: 140px; height: 140px; border-radius: var(--radius-xl); border: 5px solid white; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: 700;">
                            {{ strtoupper(substr($club->name, 0, 2)) }}
                        </div>
                    @endif
                    @if($club->status === 'verified')
                    <span class="verified-badge">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                        </svg>
                    </span>
                    @endif
                </div>
                <div class="club-title-section">
                    <h1 class="club-name">{{ $club->name }}</h1>
                    @if($club->objectives)
                    <p class="club-tagline">{{ Str::limit($club->objectives, 60) }}</p>
                    @endif
                    <div class="club-stats-row">
                        <span class="club-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <strong>{{ $club->members->count() }}</strong> thành viên
                        </span>
                        <span class="club-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <strong>{{ $club->activities->count() }}</strong> sự kiện
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
                @include('clubs.posts._action-buttons')
            </div>
        </div>
    </section>

    {{-- Club Navigation Tabs --}}
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
                    Bảng tin
                </button>
                <button class="tab-btn" data-tab="about">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    Giới thiệu
                </button>
                <button class="tab-btn" data-tab="events">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Sự kiện
                </button>
                <button class="tab-btn" data-tab="members">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Thành viên
                </button>
            </div>
        </div>
    </section>

    {{-- Main Content --}}
    <main class="club-main">
        <div class="container">
            <div class="club-layout">
                {{-- Left Column - Timeline --}}
                <div class="timeline-column">
                    {{-- Create Post Card --}}
                    @include('clubs.posts._create-card')

                    {{-- Posts Feed --}}
                    @include('clubs.posts._feed')
                </div>

                {{-- Right Column - Sidebar --}}
                @include('clubs.posts._sidebar')
            </div>
        </div>
    </main>

    {{-- Create/Edit Post Modal --}}
    @auth
        @include('clubs.posts._create-modal')
    @endauth
</div>

{{-- Alpine.js CDN with Intersect plugin --}}
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

{{-- Alpine.js Scripts --}}
@include('clubs.posts._scripts')

<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});
</script>
@endsection
