{{-- Events Tab Content --}}
<div class="events-tab" x-data="eventsTab()">
    {{-- Header with Filter/Sort and Create Button --}}
    <div class="events-header sidebar-card">
        <div class="events-header-row">
            <h3 class="sidebar-card-title" style="margin-bottom: 0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Sự kiện
            </h3>

            @if($canPost)
            <button class="btn btn-primary btn-sm" @click="openCreateModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tạo sự kiện
            </button>
            @endif
        </div>

        {{-- Filter/Sort Controls --}}
        <div class="events-filters">
            <select x-model="statusFilter" @change="filterEvents()" class="filter-select">
                <option value="">Tất cả trạng thái</option>
                <option value="upcoming">Sắp tới</option>
                <option value="completed">Đã hoàn thành</option>
                <option value="cancelled">Đã hủy</option>
            </select>

            <select x-model="sortBy" @change="filterEvents()" class="filter-select">
                <option value="date_desc">Mới nhất trước</option>
                <option value="date_asc">Cũ nhất trước</option>
            </select>
        </div>
    </div>

    {{-- Events List --}}
    <div class="events-list">
        <template x-if="loading">
            <div class="loading-spinner">
                <svg class="spinner" viewBox="0 0 50 50">
                    <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                </svg>
                Đang tải...
            </div>
        </template>

        <template x-for="event in events" :key="event.id">
            <div class="event-card sidebar-card">
                <div class="event-card-header">
                    <div class="event-date-badge">
                        <span class="day" x-text="formatDay(event.activity_date)"></span>
                        <span class="month" x-text="formatMonth(event.activity_date)"></span>
                    </div>
                    <div class="event-main-info">
                        <h4 class="event-title" x-text="event.title"></h4>
                        <div class="event-meta">
                            <span class="event-time">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; display: inline; vertical-align: middle;">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <span x-text="formatTime(event.activity_date)"></span>
                            </span>
                            <template x-if="event.location">
                                <span class="event-location">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; display: inline; vertical-align: middle;">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span x-text="event.location"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                    <span class="event-status" :class="'status-' + event.status" x-text="getStatusText(event.status)"></span>
                </div>

                <template x-if="event.description">
                    <p class="event-description" x-text="event.description"></p>
                </template>

                {{-- Admin Actions --}}
                @if($canPost)
                <div class="event-actions">
                    <button class="btn-action btn-edit" @click="openEditModal(event)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Chỉnh sửa
                    </button>
                    <button class="btn-action btn-delete" @click="deleteEvent(event)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Xóa
                    </button>
                </div>
                @endif
            </div>
        </template>

        <template x-if="!loading && events.length === 0">
            <div class="empty-state sidebar-card">
                <div class="empty-state-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <h3>Chưa có sự kiện nào</h3>
                <p>Hãy tạo sự kiện đầu tiên cho CLB!</p>
            </div>
        </template>
    </div>

    {{-- Create/Edit Event Modal --}}
    @if($canPost)
    <div class="modal-overlay" :class="{ 'active': showEventModal }" x-show="showEventModal" x-cloak @click.self="closeModal()">
        <div class="modal-content">
            <div class="modal-header">
                <h3 x-text="editingEvent ? 'Chỉnh sửa sự kiện' : 'Tạo sự kiện mới'"></h3>
                <button class="modal-close" @click="closeModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Tiêu đề *</label>
                    <input type="text" x-model="eventForm.title" class="form-input" placeholder="Nhập tiêu đề sự kiện">
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea x-model="eventForm.description" class="form-textarea" rows="3" placeholder="Mô tả sự kiện"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày giờ *</label>
                        <input type="datetime-local" x-model="eventForm.activity_date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select x-model="eventForm.status" class="form-input">
                            <option value="upcoming">Sắp tới</option>
                            <option value="completed">Đã hoàn thành</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Địa điểm</label>
                    <input type="text" x-model="eventForm.location" class="form-input" placeholder="Nhập địa điểm">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="closeModal()">Hủy</button>
                <button class="btn btn-primary" @click="submitEvent()" :disabled="submitting">
                    <span x-text="submitting ? 'Đang xử lý...' : (editingEvent ? 'Cập nhật' : 'Tạo sự kiện')"></span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
