{{-- Posts Feed - Rendered via Alpine.js --}}
<div class="timeline-posts">
    {{-- Loading skeleton --}}
    <template x-if="loading && posts.length === 0">
        <div class="post-skeleton">
            <div class="skeleton-header">
                <div class="skeleton-avatar"></div>
                <div class="skeleton-info">
                    <div class="skeleton-name"></div>
                    <div class="skeleton-meta"></div>
                </div>
            </div>
            <div class="skeleton-content">
                <div class="skeleton-line"></div>
                <div class="skeleton-line short"></div>
            </div>
        </div>
    </template>

    {{-- Posts list --}}
    <template x-for="post in posts" :key="post.id">
        <article class="post-card" :class="{ 'pinned': post.is_pinned }">
            {{-- Pinned badge --}}
            <template x-if="post.is_pinned">
                <div class="post-pinned-badge">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
                    </svg>
                    Bài viết ghim
                </div>
            </template>

            {{-- Post header --}}
            <div class="post-header">
                <template x-if="post.author.avatar">
                    <img :src="'/storage/' + post.author.avatar" :alt="post.author.name" class="post-avatar">
                </template>
                <template x-if="!post.author.avatar">
                    <div class="post-avatar post-avatar-placeholder" x-text="post.author.name.charAt(0).toUpperCase()"></div>
                </template>

                <div class="post-author-info">
                    <div class="post-author">
                        <span class="author-name" x-text="post.author.name"></span>
                        <template x-if="getAuthorRole(post.author.id)">
                            <span class="author-badge" :class="getRoleBadgeClass(post.author.id)" x-text="getRoleBadgeText(post.author.id)"></span>
                        </template>
                        <template x-if="post.is_edited">
                            <span class="edited-badge">Đã chỉnh sửa</span>
                        </template>
                    </div>
                    <div class="post-meta">
                        <span class="post-time" x-text="formatTime(post.created_at)"></span>
                        <span class="dot">·</span>
                        <span class="post-visibility">
                            <template x-if="post.visibility === 'public'">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                </svg>
                            </template>
                            <template x-if="post.visibility === 'members_only'">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </template>
                            <span x-text="post.visibility === 'public' ? 'Công khai' : 'Thành viên'"></span>
                        </span>
                    </div>
                </div>

                {{-- Post menu --}}
                <div class="post-menu-wrapper" x-data="{ menuOpen: false }">
                    <button class="post-menu-btn" @click="menuOpen = !menuOpen">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="5" r="2"/>
                            <circle cx="12" cy="12" r="2"/>
                            <circle cx="12" cy="19" r="2"/>
                        </svg>
                    </button>
                    <div class="post-menu-dropdown" x-show="menuOpen" @click.away="menuOpen = false" x-cloak>
                        <button class="menu-item" @click="copyPostLink(post); menuOpen = false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                            </svg>
                            Sao chép liên kết
                        </button>
                        <template x-if="canEditPost(post)">
                            <button class="menu-item" @click="openEditModal(post); menuOpen = false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Chỉnh sửa
                            </button>
                        </template>
                        <template x-if="canPinPost()">
                            <button class="menu-item" @click="togglePin(post); menuOpen = false">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
                                </svg>
                                <span x-text="post.is_pinned ? 'Bỏ ghim' : 'Ghim bài viết'"></span>
                            </button>
                        </template>
                        <template x-if="canDeletePost(post)">
                            <button class="menu-item menu-item-danger" @click="deletePost(post); menuOpen = false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                                Xóa
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Post content --}}
            <div class="post-content" x-html="post.content"></div>

            {{-- Post media --}}
            <template x-if="post.media && post.media.length > 0">
                <div class="post-media">
                    {{-- Images grid --}}
                    <template x-if="post.media[0].type === 'image'">
                        <div class="post-images-grid" :class="getImageGridClass(post.media.length)">
                            <template x-for="(media, idx) in post.media.slice(0, 4)" :key="media.id">
                                <div class="post-image-item" @click="openLightbox(post.media, idx)">
                                    <img :src="'/storage/' + media.path" :alt="'Ảnh ' + (idx + 1)">
                                    <template x-if="idx === 3 && post.media.length > 4">
                                        <div class="more-images-overlay">
                                            <span x-text="'+' + (post.media.length - 4)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Video --}}
                    <template x-if="post.media[0].type === 'video'">
                        <div class="post-video">
                            <video controls>
                                <source :src="'/storage/' + post.media[0].path" type="video/mp4">
                            </video>
                        </div>
                    </template>

                    {{-- YouTube --}}
                    <template x-if="post.media[0].type === 'youtube'">
                        <div class="post-video">
                            <div class="video-wrapper">
                                <iframe :src="getYouTubeEmbedUrl(post.media[0].youtube_url)"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Post stats --}}
            <div class="post-stats">
                <div class="reactions">
                    <template x-if="getTotalReactions(post) > 0">
                        <div class="reaction-icons">
                            <template x-if="post.reaction_counts && post.reaction_counts.like">
                                <span class="reaction like">👍</span>
                            </template>
                            <template x-if="post.reaction_counts && post.reaction_counts.love">
                                <span class="reaction love">❤️</span>
                            </template>
                            <template x-if="post.reaction_counts && post.reaction_counts.fire">
                                <span class="reaction fire">🔥</span>
                            </template>
                        </div>
                    </template>
                    <span class="reaction-count" x-text="getTotalReactions(post) > 0 ? getTotalReactions(post) : ''"></span>
                </div>
                <div class="engagement" style="cursor: pointer;">
                    <template x-if="post.all_comments_count > 0">
                        <span x-text="post.all_comments_count + ' bình luận'" @click="toggleComments(post)"></span>
                    </template>
                </div>
            </div>

            {{-- Post actions --}}
            <div class="post-actions">
                <div class="reaction-button-wrapper" x-data="{ reactionPickerOpen: false }">
                    <button class="post-action like-btn"
                        :class="{ 'active': post.user_reaction }"
                        @click="toggleReaction(post, post.user_reaction || 'like')"
                        @mouseenter="reactionPickerOpen = true"
                        @mouseleave="setTimeout(() => reactionPickerOpen = false, 300)">
                        <template x-if="!post.user_reaction || post.user_reaction === 'like'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                            </svg>
                        </template>
                        <template x-if="post.user_reaction === 'love'">
                            <span class="reaction-emoji">❤️</span>
                        </template>
                        <template x-if="post.user_reaction === 'fire'">
                            <span class="reaction-emoji">🔥</span>
                        </template>
                        <span x-text="getReactionText(post.user_reaction)"></span>
                    </button>
                    {{-- Reaction picker --}}
                    <div class="reaction-picker"
                        x-show="reactionPickerOpen"
                        @mouseenter="reactionPickerOpen = true"
                        @mouseleave="reactionPickerOpen = false"
                        x-cloak>
                        <button class="reaction-option" @click="toggleReaction(post, 'like'); reactionPickerOpen = false" title="Thích">
                            👍
                        </button>
                        <button class="reaction-option" @click="toggleReaction(post, 'love'); reactionPickerOpen = false" title="Yêu thích">
                            ❤️
                        </button>
                        <button class="reaction-option" @click="toggleReaction(post, 'fire'); reactionPickerOpen = false" title="Tuyệt vời">
                            🔥
                        </button>
                    </div>
                </div>

                <button class="post-action comment-btn" @click="toggleComments(post)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Bình luận</span>
                </button>

                <button class="post-action share-btn" @click="copyPostLink(post)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3"/>
                        <circle cx="6" cy="12" r="3"/>
                        <circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    <span>Chia sẻ</span>
                </button>
            </div>

            {{-- Comments section --}}
            @include('clubs.posts._comments')
        </article>
    </template>

    {{-- Load more / Infinite scroll trigger --}}
    <template x-if="hasMore">
        <div class="load-more-trigger" x-intersect="loadPosts()">
            <template x-if="loading">
                <div class="loading-spinner">
                    <svg class="spinner" viewBox="0 0 50 50">
                        <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                    </svg>
                    Đang tải...
                </div>
            </template>
        </div>
    </template>

    {{-- No more posts --}}
    <template x-if="!hasMore && posts.length > 0">
        <div class="no-more-posts">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            Đã hiển thị tất cả bài viết
        </div>
    </template>

    {{-- Empty state --}}
    <template x-if="!loading && posts.length === 0">
        <div class="empty-posts">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <h3>Chưa có bài viết nào</h3>
            <p>Hãy là người đầu tiên đăng bài trong CLB!</p>
        </div>
    </template>
</div>
