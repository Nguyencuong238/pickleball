{{-- Comments Section - Expandable --}}
<div class="post-comments" x-show="expandedComments.includes(post.id)" x-cloak>
    {{-- Comments list --}}
    <div class="comments-list">
        <template x-for="comment in post.comments || []" :key="comment.id">
            <div class="comment-item">
                <template x-if="comment.user.avatar">
                    <img :src="'/storage/' + comment.user.avatar" :alt="comment.user.name" class="comment-avatar">
                </template>
                <template x-if="!comment.user.avatar">
                    <div class="comment-avatar comment-avatar-placeholder" x-text="comment.user.name.charAt(0).toUpperCase()"></div>
                </template>
                <div class="comment-content-wrapper">
                    <div class="comment-bubble">
                        <div class="comment-header">
                            <span class="comment-author" x-text="comment.user.name"></span>
                            <template x-if="getAuthorRole(comment.user.id)">
                                <span class="author-badge small" :class="getRoleBadgeClass(comment.user.id)" x-text="getRoleBadgeText(comment.user.id)"></span>
                            </template>
                        </div>
                        <p class="comment-text" x-text="comment.content"></p>
                    </div>
                    <div class="comment-meta">
                        <span class="comment-time" x-text="formatTime(comment.created_at)"></span>
                        @auth
                            @if($membership)
                            <button class="comment-action-btn" @click="replyingTo = replyingTo === comment.id ? null : comment.id">Trả lời</button>
                            @endif
                            <template x-if="canDeleteComment(comment)">
                                <button class="comment-action-btn comment-action-danger" @click="deleteComment(post, comment)">Xóa</button>
                            </template>
                        @endauth
                    </div>

                    {{-- Replies --}}
                    <template x-if="comment.replies && comment.replies.length > 0">
                        <div class="comment-replies">
                            <template x-for="reply in comment.replies" :key="reply.id">
                                <div class="comment-item reply">
                                    <template x-if="reply.user.avatar">
                                        <img :src="'/storage/' + reply.user.avatar" :alt="reply.user.name" class="comment-avatar small">
                                    </template>
                                    <template x-if="!reply.user.avatar">
                                        <div class="comment-avatar comment-avatar-placeholder small" x-text="reply.user.name.charAt(0).toUpperCase()"></div>
                                    </template>
                                    <div class="comment-content-wrapper">
                                        <div class="comment-bubble">
                                            <div class="comment-header">
                                                <span class="comment-author" x-text="reply.user.name"></span>
                                                <template x-if="getAuthorRole(reply.user.id)">
                                                    <span class="author-badge small" :class="getRoleBadgeClass(reply.user.id)" x-text="getRoleBadgeText(reply.user.id)"></span>
                                                </template>
                                            </div>
                                            <p class="comment-text" x-text="reply.content"></p>
                                        </div>
                                        <div class="comment-meta">
                                            <span class="comment-time" x-text="formatTime(reply.created_at)"></span>
                                            @auth
                                            <template x-if="canDeleteComment(reply)">
                                                <button class="comment-action-btn comment-action-danger" @click="deleteComment(post, reply)">Xóa</button>
                                            </template>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Reply form --}}
                    @auth
                        @if($membership)
                        <template x-if="replyingTo === comment.id">
                            <div class="reply-form">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="comment-avatar small">
                                @else
                                    <div class="comment-avatar comment-avatar-placeholder small">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                                @endif
                                <div class="comment-input-wrapper">
                                    <input type="text"
                                        x-model="replyContent"
                                        placeholder="Viết phản hồi..."
                                        class="comment-input"
                                        @keydown.enter="submitComment(post, comment.id); replyingTo = null">
                                    <button class="comment-submit-btn" @click="submitComment(post, comment.id); replyingTo = null" :disabled="!replyContent.trim()">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        @endif
                    @endauth
                </div>
            </div>
        </template>
    </div>

    {{-- Load more comments --}}
    <template x-if="commentsHasMore[post.id]">
        <button class="load-more-comments" @click="loadMoreComments(post)">
            Xem thêm bình luận...
        </button>
    </template>

    {{-- Comment form --}}
    @auth
        @if($membership)
        <div class="comment-form-inline">
            @if(Auth::user()->avatar)
                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="comment-avatar">
            @else
                <div class="comment-avatar comment-avatar-placeholder">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            @endif
            <div class="comment-input-wrapper">
                <input type="text"
                    x-model="commentContent[post.id]"
                    placeholder="Viết bình luận..."
                    class="comment-input"
                    @keydown.enter="submitComment(post)">
                <div class="comment-input-actions">
                    <button class="comment-submit-btn" @click="submitComment(post)" :disabled="!commentContent[post.id]?.trim()">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @else
        <div class="comment-login-prompt">
            Tham gia club để bình luận
        </div>
        @endif
    @else
    <div class="comment-login-prompt">
        <a href="{{ route('login') }}">Đăng nhập</a> để bình luận
    </div>
    @endauth
</div>
