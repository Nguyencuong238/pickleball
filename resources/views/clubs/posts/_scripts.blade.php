{{-- Tiptap - Load via ESM dynamic import --}}
{{-- Alpine.js Post Feed Component --}}
<script>
// Tiptap modules cache
let TiptapModules = null;

// Load Tiptap modules dynamically (ESM)
async function loadTiptapModules() {
    if (TiptapModules) return TiptapModules;

    const [coreModule, starterKitModule, linkModule, placeholderModule] = await Promise.all([
        import('https://esm.sh/@tiptap/core@2.1.13'),
        import('https://esm.sh/@tiptap/starter-kit@2.1.13'),
        import('https://esm.sh/@tiptap/extension-link@2.1.13'),
        import('https://esm.sh/@tiptap/extension-placeholder@2.1.13')
    ]);

    TiptapModules = {
        Editor: coreModule.Editor,
        StarterKit: starterKitModule.default || starterKitModule.StarterKit,
        Link: linkModule.default || linkModule.Link,
        Placeholder: placeholderModule.default || placeholderModule.Placeholder
    };

    return TiptapModules;
}

function postFeed() {
    // Declare editor OUTSIDE return object to avoid Alpine Proxy (per Tiptap docs)
    let editor = null;

    return {
        // Data
        posts: [],
        loading: false,
        hasMore: true,
        currentPage: 1,
        clubSlug: '{{ $club->slug }}',

        // User & permissions
        currentUserId: {{ Auth::id() ?? 'null' }},
        membership: @json($membership),
        canPost: {{ $canPost ? 'true' : 'false' }},
        managementTeam: @json($managementTeam->pluck('pivot.role', 'id')),

        // Comments
        expandedComments: [],
        commentContent: {},
        replyContent: '',
        replyingTo: null,
        commentsPage: {},
        commentsHasMore: {},

        // Modal state
        showCreateModal: false,
        editingPost: null,
        submitting: false,

        // Post form
        postContent: '',
        visibility: 'public',
        mediaType: null,
        selectedImages: [],
        selectedVideo: null,
        youtubeUrl: '',
        youtubeEmbedUrl: '',
        keepMediaIds: [],
        isDragging: false,

        // Tiptap editor state (for UI binding)
        tiptapLoading: false,
        isBold: false,
        isItalic: false,
        isStrike: false,
        isBulletList: false,
        isOrderedList: false,
        isLink: false,

        // Initialize
        init() {
            this.loadPosts();
            this.$watch('showCreateModal', (value) => {
                if (value) {
                    this.$nextTick(() => this.initEditor());
                } else {
                    this.destroyEditor();
                }
            });
        },

        // Initialize Tiptap editor (async)
        async initEditor() {
            if (editor || this.tiptapLoading) return;

            const editorElement = this.$refs.editorContent;
            if (!editorElement) return;

            this.tiptapLoading = true;
            const _this = this;

            try {
                const { Editor, StarterKit, Link, Placeholder } = await loadTiptapModules();

                editor = new Editor({
                    element: editorElement,
                    extensions: [
                        StarterKit.configure({
                            heading: false,
                            codeBlock: false,
                            code: false,
                            blockquote: false,
                            horizontalRule: false,
                        }),
                        Link.configure({
                            openOnClick: false,
                            HTMLAttributes: {
                                target: '_blank',
                                rel: 'noopener noreferrer',
                            },
                        }),
                        Placeholder.configure({
                            placeholder: 'Bạn đang nghĩ gì về Pickleball?',
                        }),
                    ],
                    content: this.postContent || '',
                    onCreate() {
                        _this.updateActiveStates();
                    },
                    onUpdate({ editor: e }) {
                        _this.postContent = e.getHTML();
                        _this.updateActiveStates();
                    },
                    onSelectionUpdate() {
                        _this.updateActiveStates();
                    },
                });
            } catch (error) {
                console.error('Failed to load Tiptap:', error);
            } finally {
                this.tiptapLoading = false;
            }
        },

        // Destroy Tiptap editor
        destroyEditor() {
            if (editor && !editor.isDestroyed) {
                try {
                    editor.destroy();
                } catch (e) {
                    // Ignore destruction errors
                }
            }
            editor = null;
        },

        // Check if editor is ready
        isEditorReady() {
            return editor && !editor.isDestroyed;
        },

        // Update toolbar active states
        updateActiveStates() {
            if (!this.isEditorReady()) return;
            this.isBold = editor.isActive('bold');
            this.isItalic = editor.isActive('italic');
            this.isStrike = editor.isActive('strike');
            this.isBulletList = editor.isActive('bulletList');
            this.isOrderedList = editor.isActive('orderedList');
            this.isLink = editor.isActive('link');
        },

        // Tiptap toolbar actions
        toggleBold() {
            if (!this.isEditorReady()) return;
            editor.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            if (!this.isEditorReady()) return;
            editor.chain().focus().toggleItalic().run();
        },
        toggleStrike() {
            if (!this.isEditorReady()) return;
            editor.chain().focus().toggleStrike().run();
        },
        toggleBulletList() {
            if (!this.isEditorReady()) return;
            editor.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            if (!this.isEditorReady()) return;
            editor.chain().focus().toggleOrderedList().run();
        },
        setLink() {
            if (!this.isEditorReady()) return;
            if (editor.isActive('link')) {
                editor.chain().focus().unsetLink().run();
                return;
            }
            const url = prompt('Nhập URL:');
            if (url) {
                editor.chain().focus().setLink({ href: url }).run();
            }
        },

        // Set editor content (for editing)
        setEditorContent(html, retries = 10) {
            if (this.isEditorReady()) {
                editor.commands.setContent(html);
            } else if (retries > 0) {
                setTimeout(() => this.setEditorContent(html, retries - 1), 100);
            }
        },

        // Clear editor content safely
        clearEditorContent() {
            if (this.isEditorReady()) {
                editor.commands.clearContent();
            }
        },

        // Reset form
        resetForm() {
            this.postContent = '';
            this.visibility = 'public';
            this.mediaType = null;
            this.selectedImages = [];
            this.selectedVideo = null;
            this.youtubeUrl = '';
            this.youtubeEmbedUrl = '';
            this.keepMediaIds = [];
            this.clearEditorContent();
        },

        // Load posts
        async loadPosts() {
            if (this.loading || !this.hasMore) return;

            this.loading = true;
            try {
                const response = await fetch(`/clubs/${this.clubSlug}/posts?page=${this.currentPage}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();

                if (data.success) {
                    // Process posts
                    data.posts.forEach(post => {
                        post.user_reaction = this.getUserReaction(post);
                        post.reaction_counts = this.getReactionCounts(post);
                    });

                    this.posts = [...this.posts, ...data.posts];
                    this.hasMore = data.hasMore;
                    this.currentPage = data.nextPage;
                }
            } catch (error) {
                console.error('Error loading posts:', error);
            } finally {
                this.loading = false;
            }
        },

        // Get user's reaction for a post
        getUserReaction(post) {
            if (!this.currentUserId || !post.reactions) return null;
            const reaction = post.reactions.find(r => r.user_id === this.currentUserId);
            return reaction?.type || null;
        },

        // Get reaction counts
        getReactionCounts(post) {
            if (!post.reactions) return {};
            return post.reactions.reduce((acc, r) => {
                acc[r.type] = (acc[r.type] || 0) + 1;
                return acc;
            }, {});
        },

        // Get total reactions
        getTotalReactions(post) {
            if (!post.reaction_counts) return 0;
            return Object.values(post.reaction_counts).reduce((a, b) => a + b, 0);
        },

        // Submit post (create or update)
        async submitPost() {
            if (!this.postContent.trim() || this.submitting) return;

            this.submitting = true;
            const formData = new FormData();
            formData.append('content', this.postContent);
            formData.append('visibility', this.visibility);

            // Handle media
            if (this.mediaType === 'images' && this.selectedImages.length > 0) {
                formData.append('media_type', 'images');
                this.selectedImages.forEach((img, i) => {
                    formData.append(`images[${i}]`, img.file);
                });
            } else if (this.mediaType === 'video' && this.selectedVideo) {
                formData.append('media_type', 'video');
                formData.append('video', this.selectedVideo.file);
            } else if (this.mediaType === 'youtube' && this.youtubeUrl) {
                formData.append('media_type', 'youtube');
                formData.append('youtube_url', this.youtubeUrl);
            }

            // Keep media IDs for editing
            if (this.editingPost) {
                this.keepMediaIds.forEach(id => {
                    formData.append('keep_media_ids[]', id);
                });
            }

            try {
                const url = this.editingPost
                    ? `/clubs/${this.clubSlug}/posts/${this.editingPost.id}`
                    : `/clubs/${this.clubSlug}/posts`;

                const method = this.editingPost ? 'POST' : 'POST';
                if (this.editingPost) {
                    formData.append('_method', 'PUT');
                }

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    if (this.editingPost) {
                        // Update existing post
                        const index = this.posts.findIndex(p => p.id === this.editingPost.id);
                        if (index !== -1) {
                            data.post.user_reaction = this.getUserReaction(data.post);
                            data.post.reaction_counts = this.getReactionCounts(data.post);
                            this.posts[index] = data.post;
                        }
                    } else {
                        // Add new post to top
                        data.post.user_reaction = null;
                        data.post.reaction_counts = {};
                        this.posts.unshift(data.post);
                    }

                    // Reset form first while editor is still valid
                    this.resetForm();
                    this.editingPost = null;
                    // Then close modal (which will destroy editor via watcher)
                    this.showCreateModal = false;
                    this.showToast(data.message, 'success');
                } else {
                    this.showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                console.error('Error submitting post:', error);
                this.showToast('Có lỗi xảy ra', 'error');
            } finally {
                this.submitting = false;
            }
        },

        // Toggle reaction
        async toggleReaction(post, type) {
            if (!this.currentUserId) {
                window.location.href = '{{ route("login") }}';
                return;
            }

            const previousReaction = post.user_reaction;
            const previousCounts = { ...post.reaction_counts };

            // Optimistic update
            if (previousReaction === type) {
                post.user_reaction = null;
                post.reaction_counts[type] = Math.max(0, (post.reaction_counts[type] || 1) - 1);
            } else {
                if (previousReaction) {
                    post.reaction_counts[previousReaction] = Math.max(0, (post.reaction_counts[previousReaction] || 1) - 1);
                }
                post.user_reaction = type;
                post.reaction_counts[type] = (post.reaction_counts[type] || 0) + 1;
            }

            try {
                const url = `/club-posts/${post.id}/reactions`;
                const method = post.user_reaction ? 'POST' : 'DELETE';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ type: type })
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }
            } catch (error) {
                // Revert on error
                post.user_reaction = previousReaction;
                post.reaction_counts = previousCounts;
                console.error('Error toggling reaction:', error);
            }
        },

        // Get reaction text
        getReactionText(reaction) {
            const texts = {
                'like': 'Thích',
                'love': 'Yêu thích',
                'fire': 'Tuyệt vời'
            };
            return texts[reaction] || 'Thích';
        },

        // Toggle comments visibility
        async toggleComments(post) {
            const index = this.expandedComments.indexOf(post.id);
            if (index === -1) {
                this.expandedComments.push(post.id);
                if (!post.comments || post.comments.length === 0) {
                    await this.loadComments(post);
                }
            } else {
                this.expandedComments.splice(index, 1);
            }
        },

        // Load comments
        async loadComments(post, page = 1) {
            try {
                const response = await fetch(`/club-posts/${post.id}/comments?page=${page}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();

                if (data.success) {
                    if (page === 1) {
                        post.comments = data.comments;
                    } else {
                        post.comments = [...(post.comments || []), ...data.comments];
                    }
                    // Use spread to ensure Alpine reactivity
                    this.commentsPage = {...this.commentsPage, [post.id]: data.currentPage};
                    this.commentsHasMore = {...this.commentsHasMore, [post.id]: data.hasMore};
                }
            } catch (error) {
                console.error('Error loading comments:', error);
            }
        },

        // Load more comments
        async loadMoreComments(post) {
            const currentPage = this.commentsPage[post.id] || 1;
            await this.loadComments(post, currentPage + 1);
        },

        // Submit comment
        async submitComment(post, parentId = null) {
            const content = parentId ? this.replyContent : this.commentContent[post.id];
            if (!content?.trim()) return;

            try {
                const response = await fetch(`/club-posts/${post.id}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        content: content,
                        parent_id: parentId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    if (parentId) {
                        const parentComment = post.comments.find(c => c.id === parentId);
                        if (parentComment) {
                            if (!parentComment.replies) parentComment.replies = [];
                            parentComment.replies.push(data.comment);
                        }
                        this.replyContent = '';
                        this.replyingTo = null;
                    } else {
                        if (!post.comments) post.comments = [];
                        post.comments.push(data.comment);
                        this.commentContent[post.id] = '';
                    }
                    post.all_comments_count = (post.all_comments_count || 0) + 1;
                }
            } catch (error) {
                console.error('Error submitting comment:', error);
            }
        },

        // Delete comment
        async deleteComment(post, comment) {
            if (!confirm('Bạn có chắc muốn xóa bình luận này?')) return;

            try {
                const response = await fetch(`/club-post-comments/${comment.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    // Remove from parent's replies
                    if (comment.parent_id) {
                        const parent = post.comments.find(c => c.id === comment.parent_id);
                        if (parent && parent.replies) {
                            parent.replies = parent.replies.filter(r => r.id !== comment.id);
                        }
                    } else {
                        post.comments = post.comments.filter(c => c.id !== comment.id);
                    }
                    post.all_comments_count = Math.max(0, (post.all_comments_count || 1) - 1);
                }
            } catch (error) {
                console.error('Error deleting comment:', error);
            }
        },

        // Can delete comment
        canDeleteComment(comment) {
            if (!this.currentUserId) return false;
            if (comment.user_id === this.currentUserId) return true;
            const role = this.membership?.role;
            return role === 'creator' || role === 'admin' || role === 'moderator';
        },

        // Open edit modal
        openEditModal(post) {
            this.editingPost = post;
            this.postContent = post.content;
            this.visibility = post.visibility;
            this.keepMediaIds = post.media ? post.media.map(m => m.id) : [];
            this.mediaType = null;
            this.selectedImages = [];
            this.selectedVideo = null;
            this.youtubeUrl = '';
            this.youtubeEmbedUrl = '';
            this.showCreateModal = true;

            // Set editor content - will retry until editor is ready
            this.$nextTick(() => {
                this.setEditorContent(post.content);
            });
        },

        // Toggle pin
        async togglePin(post) {
            try {
                const response = await fetch(`/clubs/${this.clubSlug}/posts/${post.id}/pin`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    post.is_pinned = data.is_pinned;
                    this.showToast(data.message, 'success');
                    // Reload to re-sort
                    this.posts = [];
                    this.currentPage = 1;
                    this.hasMore = true;
                    this.loadPosts();
                }
            } catch (error) {
                console.error('Error toggling pin:', error);
            }
        },

        // Delete post
        async deletePost(post) {
            if (!confirm('Bạn có chắc muốn xóa bài viết này?')) return;

            try {
                const response = await fetch(`/clubs/${this.clubSlug}/posts/${post.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.posts = this.posts.filter(p => p.id !== post.id);
                    this.showToast(data.message, 'success');
                }
            } catch (error) {
                console.error('Error deleting post:', error);
            }
        },

        // Permission helpers
        canEditPost(post) {
            if (!this.currentUserId) return false;
            if (post.user_id === this.currentUserId) return true;
            const role = this.membership?.role;
            return role === 'creator' || role === 'admin';
        },

        canDeletePost(post) {
            if (!this.currentUserId) return false;
            if (post.user_id === this.currentUserId) return true;
            const role = this.membership?.role;
            return role === 'creator' || role === 'admin' || role === 'moderator';
        },

        canPinPost() {
            const role = this.membership?.role;
            return role === 'creator' || role === 'admin';
        },

        // Get author role
        getAuthorRole(userId) {
            return this.managementTeam[userId] || null;
        },

        // Get role badge class
        getRoleBadgeClass(userId) {
            const role = this.managementTeam[userId];
            if (role === 'creator' || role === 'admin') return 'admin';
            if (role === 'moderator') return 'moderator';
            return 'member';
        },

        // Get role badge text
        getRoleBadgeText(userId) {
            const role = this.managementTeam[userId];
            const texts = {
                'creator': 'Chủ nhiệm',
                'admin': 'Admin',
                'moderator': 'Điều hành'
            };
            return texts[role] || '';
        },

        // Format time
        formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (seconds < 60) return 'Vừa xong';
            if (minutes < 60) return `${minutes} phút trước`;
            if (hours < 24) return `${hours} giờ trước`;
            if (days < 7) return `${days} ngày trước`;

            return date.toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },

        // Copy post link
        copyPostLink(post) {
            const url = `${window.location.origin}/clubs/${this.clubSlug}/posts/${post.id}`;
            navigator.clipboard.writeText(url).then(() => {
                this.showToast('Đã sao chép liên kết!', 'success');
            });
        },

        // Get YouTube embed URL
        getYouTubeEmbedUrl(url) {
            if (!url) return '';
            const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
            return match ? `https://www.youtube.com/embed/${match[1]}?rel=0` : '';
        },

        // Validate YouTube URL
        validateYoutubeUrl() {
            this.youtubeEmbedUrl = this.getYouTubeEmbedUrl(this.youtubeUrl);
        },

        // Get image grid class
        getImageGridClass(count) {
            if (count === 1) return 'one-image';
            if (count === 2) return 'two-images';
            if (count === 3) return 'three-images';
            return 'four-plus-images';
        },

        // Handle file select
        handleFileSelect(event, type) {
            const files = event.target.files;
            if (type === 'images') {
                this.processImages(files);
            } else if (type === 'video') {
                this.processVideo(files[0]);
            }
        },

        // Handle drop
        handleDrop(event, type) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (type === 'images') {
                this.processImages(files);
            } else if (type === 'video') {
                this.processVideo(files[0]);
            }
        },

        // Process images
        processImages(files) {
            const maxCount = 10;
            const maxSize = 5 * 1024 * 1024; // 5MB

            Array.from(files).slice(0, maxCount - this.selectedImages.length).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                if (file.size > maxSize) {
                    this.showToast('Ảnh quá lớn (tối đa 5MB)', 'error');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.selectedImages.push({
                        file: file,
                        preview: e.target.result
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        // Process video
        processVideo(file) {
            if (!file) return;
            const maxSize = 50 * 1024 * 1024; // 50MB

            if (!file.type.startsWith('video/')) {
                this.showToast('Định dạng video không hợp lệ', 'error');
                return;
            }
            if (file.size > maxSize) {
                this.showToast('Video quá lớn (tối đa 50MB)', 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.selectedVideo = {
                    file: file,
                    preview: URL.createObjectURL(file)
                };
            };
            reader.readAsDataURL(file);
        },

        // Remove image
        removeImage(index) {
            this.selectedImages.splice(index, 1);
        },

        // Clear media
        clearMedia() {
            this.selectedImages = [];
            this.selectedVideo = null;
            this.youtubeUrl = '';
            this.youtubeEmbedUrl = '';
        },

        // Toggle keep media
        toggleKeepMedia(id) {
            const index = this.keepMediaIds.indexOf(id);
            if (index === -1) {
                this.keepMediaIds.push(id);
            } else {
                this.keepMediaIds.splice(index, 1);
            }
        },

        // Show toast notification
        showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            // Animate in
            setTimeout(() => toast.classList.add('show'), 10);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },

        // Open lightbox
        openLightbox(media, index) {
            // Basic lightbox - can be enhanced
            const images = media.filter(m => m.type === 'image');
            if (images.length === 0) return;

            const lightbox = document.createElement('div');
            lightbox.className = 'lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-content">
                    <button class="lightbox-close">&times;</button>
                    <img src="/storage/${images[index].path}" alt="">
                </div>
            `;
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
                    lightbox.remove();
                }
            });
            document.body.appendChild(lightbox);
        }
    };
}
</script>
