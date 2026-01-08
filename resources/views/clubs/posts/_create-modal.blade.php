{{-- Create/Edit Post Modal --}}
<div class="modal-overlay" :class="{ 'active': showCreateModal }" @click.self="showCreateModal = false" x-cloak>
    <div class="modal-content">
        <div class="modal-header">
            <h3 x-text="editingPost ? 'Chỉnh sửa bài viết' : 'Tạo bài viết'"></h3>
            <button class="modal-close" @click="showCreateModal = false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitPost()">
            <div class="modal-body">
                <div class="modal-author">
                    @auth
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="modal-avatar">
                    @else
                        <div class="modal-avatar modal-avatar-placeholder">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    @endif
                    <div class="modal-author-info">
                        <span class="modal-author-name">{{ Auth::user()->name }}</span>
                        <div class="visibility-dropdown" x-data="{ open: false }">
                            <button type="button" class="visibility-selector" @click="open = !open">
                                <template x-if="visibility === 'public'">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                    </svg>
                                </template>
                                <template x-if="visibility === 'members_only'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                </template>
                                <span x-text="visibility === 'public' ? 'Công khai' : 'Thành viên'"></span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                            <div class="visibility-options" x-show="open" @click.away="open = false" x-cloak>
                                <button type="button" class="visibility-option" @click="visibility = 'public'; open = false">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                    </svg>
                                    <div>
                                        <strong>Công khai</strong>
                                        <span>Mọi người có thể xem</span>
                                    </div>
                                </button>
                                <button type="button" class="visibility-option" @click="visibility = 'members_only'; open = false">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    <div>
                                        <strong>Thành viên</strong>
                                        <span>Chỉ thành viên CLB xem được</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endauth
                </div>

                {{-- Post content editor (Tiptap) --}}
                @include('clubs.posts._editor')

                {{-- Hidden file inputs --}}
                <input type="file" id="imageInput" multiple accept="image/*" @change="handleFileSelect($event, 'images'); mediaType = 'images'" class="hidden">
                <input type="file" id="videoInput" accept="video/*" @change="handleFileSelect($event, 'video'); mediaType = 'video'" class="hidden">

                {{-- Media previews (show when has media) --}}
                <div class="media-preview-area" x-show="selectedImages.length > 0 || selectedVideo || youtubeEmbedUrl">
                    {{-- Image previews --}}
                    <div class="media-preview-grid" x-show="selectedImages.length > 0">
                        <template x-for="(img, index) in selectedImages" :key="index">
                            <div class="preview-item">
                                <img :src="img.preview" alt="Preview">
                                <button type="button" class="remove-media-btn" @click="removeImage(index)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <label for="imageInput" class="preview-item add-more-btn" x-show="selectedImages.length < 10">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </label>
                    </div>

                    {{-- Video preview --}}
                    <div class="video-preview" x-show="selectedVideo">
                        <video :src="selectedVideo?.preview" controls></video>
                        <button type="button" class="remove-media-btn" @click="selectedVideo = null; mediaType = null">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    {{-- YouTube preview --}}
                    <div class="youtube-preview" x-show="youtubeEmbedUrl">
                        <div class="video-wrapper">
                            <iframe :src="youtubeEmbedUrl" frameborder="0" allowfullscreen></iframe>
                        </div>
                        <button type="button" class="remove-media-btn" @click="youtubeUrl = ''; youtubeEmbedUrl = ''; mediaType = null">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- YouTube URL input (inline) --}}
                <div class="youtube-input-area" x-show="mediaType === 'youtube' && !youtubeEmbedUrl">
                    <div class="youtube-input-wrapper">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="youtube-icon">
                            <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                        </svg>
                        <input type="text" x-model="youtubeUrl" placeholder="Dán link YouTube vào đây..." class="youtube-input" @input="validateYoutubeUrl()">
                        <button type="button" class="youtube-cancel-btn" @click="mediaType = null; youtubeUrl = ''">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Existing media (when editing) --}}
                <template x-if="editingPost && editingPost.media && editingPost.media.length > 0 && !mediaType">
                    <div class="existing-media">
                        <p class="existing-media-label">Media hiện tại:</p>
                        <div class="media-preview-grid">
                            <template x-for="media in editingPost.media" :key="media.id">
                                <div class="preview-item" :class="{ 'removed': !keepMediaIds.includes(media.id) }">
                                    <template x-if="media.type === 'image'">
                                        <img :src="'/storage/' + media.path" alt="Existing media">
                                    </template>
                                    <template x-if="media.type === 'youtube'">
                                        <div class="youtube-thumb">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <button type="button" class="remove-media-btn" @click="toggleKeepMedia(media.id)">
                                        <template x-if="keepMediaIds.includes(media.id)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                        </template>
                                        <template x-if="!keepMediaIds.includes(media.id)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            </svg>
                                        </template>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div class="modal-footer">
                <div class="footer-media-btns">
                    <label for="imageInput" class="footer-media-btn" title="Thêm ảnh">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </label>
                    <label for="videoInput" class="footer-media-btn" title="Thêm video">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="23 7 16 12 23 17 23 7"/>
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                        </svg>
                    </label>
                    <button type="button" class="footer-media-btn" :class="{ 'active': mediaType === 'youtube' }" @click="mediaType = mediaType === 'youtube' ? null : 'youtube'" title="YouTube">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/>
                            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>
                        </svg>
                    </button>
                </div>
                <button type="submit" class="btn btn-primary" :disabled="submitting || !postContent.trim()">
                    <template x-if="submitting">
                        <svg class="spinner" viewBox="0 0 50 50">
                            <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                        </svg>
                    </template>
                    <span x-text="editingPost ? 'Cập nhật' : 'Đăng'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
