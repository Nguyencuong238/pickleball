{{-- Styles for activity detail/show page - Reclub-inspired --}}
<style>
/* ========== Container ========== */
.activity-container {
    padding: 20px 16px;
    max-width: 640px;
    margin: 100px auto 0;
}

/* ========== Alert ========== */
.alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.9rem; }
.alert-success { background: #dcfce7; color: #16a34a; border-left: 4px solid #16a34a; }

/* ========== Header Banner ========== */
.activity-header-banner {
    background: linear-gradient(135deg, #006646 0%, #00B89C 50%, #009688 100%);
    padding: 20px 20px 24px;
    color: white;
    border-radius: 16px 16px 0 0;
    position: relative;
}
.header-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.header-back-btn {
    color: white;
    text-decoration: none;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    transition: background 0.2s;
}
.header-back-btn:hover { background: rgba(255,255,255,0.25); }
.header-datetime {
    font-size: 0.85rem;
    opacity: 0.9;
    font-weight: 500;
}
.header-actions {
    display: flex;
    gap: 6px;
    align-items: center;
}
.header-action-btn {
    color: white;
    background: rgba(255,255,255,0.15);
    border: none;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s;
}
.header-action-btn:hover { background: rgba(255,255,255,0.25); }
.header-badge-row {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
}
.header-type-badge, .header-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.header-type-badge { background: rgba(255,255,255,0.2); color: white; }
.header-status-badge { background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.9); }
.header-status-badge.status-cancelled { background: rgba(239,68,68,0.3); }
.header-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.3;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Header dropdown menu */
.header-menu-wrapper { position: relative; }
.header-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    min-width: 180px;
    z-index: 200;
    overflow: hidden;
}
.header-dropdown.show { display: block; }
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    color: #374151;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    border: none;
    background: none;
    width: 100%;
    cursor: pointer;
    transition: background 0.15s;
}
.dropdown-item:hover { background: #f3f4f6; }
.dropdown-danger { color: #dc2626; }
.dropdown-danger:hover { background: #fef2f2; }

/* ========== Main Card ========== */
.activity-main-card {
    background: white;
    border-radius: 0 0 16px 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

/* ========== Tab Navigation ========== */
.tab-navigation {
    display: flex;
    border-bottom: 2px solid #e5e7eb;
    background: white;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.tab-navigation::-webkit-scrollbar { display: none; }
.tab-btn {
    flex: 1;
    min-width: max-content;
    padding: 14px 16px;
    border: none;
    background: none;
    color: #9ca3af;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
    transition: color 0.2s;
}
.tab-btn:hover { color: #6b7280; }
.tab-btn.active {
    color: #006646;
}
.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0; right: 0;
    height: 3px;
    background: #006646;
    border-radius: 3px 3px 0 0;
}
.tab-btn svg { flex-shrink: 0; }
.tab-count {
    background: #e5e7eb;
    color: #6b7280;
    font-size: 0.7rem;
    padding: 2px 7px;
    border-radius: 10px;
    font-weight: 700;
}
.tab-btn.active .tab-count {
    background: #d1fae5;
    color: #059669;
}

/* ========== Tab Content ========== */
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
    animation: tabFadeIn 0.2s ease;
}
@keyframes tabFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* ========== Detail Tab ========== */
.detail-tab { padding: 0; }
.detail-section {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
}
.detail-section:last-of-type { border-bottom: none; }
.section-icon {
    width: 40px; height: 40px;
    background: #f0fdf4;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    color: #006646;
}
.section-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.section-content strong {
    color: #1f2937;
    font-size: 0.95rem;
}
.section-content span {
    color: #6b7280;
    font-size: 0.85rem;
}
.section-label {
    font-size: 0.8rem;
    color: #9ca3af;
    margin-top: 2px;
}
.section-action-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #006646;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    margin-top: 4px;
}
.section-action-link:hover { color: #009688; text-decoration: underline; }
.chevron-right {
    color: #d1d5db;
    flex-shrink: 0;
    align-self: center;
}

/* Organizer section */
.organizer-section {
    padding: 20px;
    gap: 12px;
}
.organizer-avatar-wrap { flex-shrink: 0; }
.organizer-avatar {
    width: 48px; height: 48px;
    border-radius: 12px;
    object-fit: cover;
}
.organizer-avatar-placeholder {
    width: 48px; height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #006646, #52c98c);
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
}
.organizer-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.organizer-info strong { font-size: 1rem; color: #1f2937; }
.organizer-sub { font-size: 0.8rem; color: #9ca3af; }
.btn-outline-sm {
    padding: 6px 14px;
    border: 1.5px solid #006646;
    color: #006646;
    background: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s;
    align-self: center;
}
.btn-outline-sm:hover {
    background: #006646;
    color: white;
}

/* Participant preview */
.participant-preview {
    cursor: pointer;
    align-items: center;
}
.participant-preview:hover { background: #f9fafb; }
.avatar-row {
    display: flex;
    align-items: center;
}
.avatar-sm {
    width: 36px; height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
    margin-left: -8px;
}
.avatar-sm:first-child { margin-left: 0; }
.avatar-sm-placeholder {
    background: linear-gradient(135deg, #006646, #52c98c);
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
}
.avatar-more {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    margin-left: -8px;
    border: 2px solid white;
}
.text-muted { color: #9ca3af; font-size: 0.85rem; }

/* Activity type inline badge */
.type-badge-inline {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 2px;
    width: fit-content;
}
.type-badge-inline.type-one_off { background: #dbeafe; color: #0284c7; }
.type-badge-inline.type-recurring { background: #dcfce7; color: #16a34a; }
.type-badge-inline.type-competition { background: #ffedd5; color: #c2410c; }

/* Description */
.detail-description {
    padding: 20px;
    color: #4b5563;
    line-height: 1.7;
    font-size: 0.95rem;
    border-top: 1px solid #f3f4f6;
}
.detail-description h4 {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 10px;
}
.detail-description p { margin: 0; }

/* Club info in detail tab */
.detail-club-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #f8fafc;
    border-top: 1px solid #f3f4f6;
}
.club-avatar-sm {
    width: 40px; height: 40px;
    border-radius: 10px;
    object-fit: cover;
}
.club-avatar-sm-placeholder {
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #006646, #52c98c);
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
}
.detail-club-info div { display: flex; flex-direction: column; gap: 1px; }
.detail-club-info span { font-size: 0.8rem; }
.detail-club-info a {
    font-weight: 600;
    color: #1f2937;
    text-decoration: none;
    font-size: 0.9rem;
}
.detail-club-info a:hover { color: #006646; }

/* ========== Share Section ========== */
.detail-share-section {
    padding: 16px 20px;
    border-top: 1px solid #f3f4f6;
}
.btn-share-activity {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 24px;
    border: 1.5px solid #e5e7eb;
    background: white;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-share-activity:hover {
    border-color: #006646;
    color: #006646;
    background: #f0fdf4;
}

/* Share modal */
.share-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.share-modal {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    text-align: center;
}
.share-modal h3 {
    margin: 0 0 16px;
    font-size: 1.1rem;
    color: #1f2937;
}
.share-qr-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}
.share-qr-wrap canvas {
    border-radius: 8px;
}
.share-link-input {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.85rem;
    color: #374151;
    background: #f9fafb;
    box-sizing: border-box;
}
.share-modal-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
}
.share-btn-copy {
    flex: 1;
    padding: 10px;
    background: #006646;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.2s;
}
.share-btn-copy:hover { background: #00c4a3; }
.share-btn-close {
    flex: 1;
    padding: 10px;
    background: #f3f4f6;
    color: #555;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.2s;
}
.share-btn-close:hover { background: #e5e7eb; }

/* ========== RSVP Button ========== */
.rsvp-button-wrap {
    padding: 16px 20px;
    border-top: 1px solid #f3f4f6;
}
.btn-rsvp {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.95rem;
    text-decoration: none;
    text-align: center;
}
.btn-join {
    background: linear-gradient(135deg, #006646, #0db89d);
    color: white;
}
.btn-join:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(0,217,181,0.3);
}
.btn-cancel-rsvp {
    background: #fee2e2;
    color: #b91c1c;
    margin-top: 8px;
}
.btn-cancel-rsvp:hover { background: #fecaca; }
.rsvp-status-inline {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
}
.rsvp-status-inline.confirmed { background: #dcfce7; color: #16a34a; }
.rsvp-status-inline.waitlisted { background: #fef3c7; color: #92400e; }

/* ========== Participants Tab ========== */
.participants-tab { padding: 0; }
.participants-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 20px 12px;
}
.participants-header h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}
.waitlist-badge-sm {
    background: #fef3c7;
    color: #92400e;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}
.skill-notice-sm {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #ede9fe;
    color: #6d28d9;
    padding: 8px 20px;
    margin: 0 20px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Participant card list */
.participant-section { padding: 0 20px 12px; }
.participant-heading {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #6b7280;
    margin: 0 0 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.participant-heading.waitlisted { color: #92400e; }
.participant-card-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.participant-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    transition: background 0.15s;
}
.participant-card:hover { background: #f9fafb; }
.participant-avatar-lg {
    width: 44px; height: 44px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.participant-avatar-lg-placeholder {
    background: linear-gradient(135deg, #006646, #52c98c);
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-size: 1rem;
    font-weight: 700;
}
.participant-avatar-lg-placeholder.waitlisted {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}
.participant-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.participant-name {
    color: #1f2937;
    font-weight: 600;
    font-size: 0.9rem;
}
.participant-opr {
    color: #9ca3af;
    font-size: 0.8rem;
}
.participant-status-icon {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.participant-status-icon.confirmed {
    background: #dcfce7;
    color: #16a34a;
}
.waitlist-position {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
}

/* Empty state */
.participant-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #d1d5db;
}
.empty-title {
    margin: 12px 0 4px;
    font-weight: 600;
    color: #9ca3af;
    font-size: 0.95rem;
}
.empty-sub {
    margin: 0;
    color: #d1d5db;
    font-size: 0.85rem;
}

/* ========== Competition Tab ========== */
/* Competition panel inherits its own styles from _competition-styles.blade.php */

/* ========== Toast Notification ========== */
.toast-notification {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: #1f2937;
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 500;
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 999;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.toast-notification.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* ========== Mobile Sticky CTA ========== */
@media (max-width: 768px) {
    .activity-container {
        padding: 12px 0 0;
        margin-top: 80px;
        padding-bottom: 0;
    }
    .activity-header-banner {
        border-radius: 0;
    }
    .activity-main-card {
        border-radius: 0;
    }
    .header-title { font-size: 1.3rem; }
    .alert { margin: 0 16px 12px; }
}

@media (min-width: 769px) {
    .activity-container {
        padding: 30px 20px;
        margin-top: 80px;
    }
}

/* ========== Print ========== */
@media print {
    .tab-navigation,
    .header-actions,
    .rsvp-button-wrap,
    .btn-outline-sm { display: none; }
    .tab-content { display: block !important; opacity: 1 !important; }
    .activity-header-banner { background: #006646 !important; -webkit-print-color-adjust: exact; }
}
</style>
