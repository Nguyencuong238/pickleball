{{-- Styles for activity detail/show page --}}
<style>
    .activity-container {
        padding: 40px 20px;
        max-width: 800px;
        margin: 0 auto;
        margin-top: 100px;
    }
    .btn-back {
        background: #f3f4f6;
        color: #6b7280;
        padding: 12px 24px;
        margin-bottom: 30px;
        display: inline-block;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: #e5e7eb;
        color: #374151;
    }
    .activity-detail-card {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-top: 4px solid #00D9B5;
    }
    .activity-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .activity-detail-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
        line-height: 1.3;
    }
    .badge-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .activity-status {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .status-upcoming { background: #dbeafe; color: #0284c7; }
    .status-completed { background: #dcfce7; color: #16a34a; }
    .status-cancelled { background: #fee2e2; color: #b91c1c; }
    .type-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .type-one_off { background: #dbeafe; color: #0284c7; }
    .type-recurring { background: #dcfce7; color: #16a34a; }
    .type-competition { background: #ffedd5; color: #c2410c; }
    .activity-detail-meta {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #e5e7eb;
    }
    .meta-item {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #6b7280;
        font-size: 1rem;
    }
    .meta-item svg {
        width: 20px;
        height: 20px;
        color: #00D9B5;
        flex-shrink: 0;
    }
    .meta-item strong { color: #374151; }
    .activity-detail-description {
        color: #4b5563;
        line-height: 1.8;
        font-size: 1.05rem;
    }
    .activity-detail-description h4 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 15px;
    }
    .club-info-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-top: 30px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .club-avatar {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        object-fit: cover;
    }
    .club-avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, #00D9B5, #0099CC);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.2rem;
    }
    .club-info-text { flex: 1; }
    .club-info-text p { margin: 0; font-size: 0.9rem; color: #6b7280; }
    .club-info-text a { font-weight: 600; color: #1f2937; text-decoration: none; }
    .club-info-text a:hover { color: #00D9B5; }
    .activity-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }
    .btn-action {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-edit { background: #dbeafe; color: #0284c7; }
    .btn-edit:hover { background: #bfdbfe; }
    .btn-delete { background: #fee2e2; color: #b91c1c; }
    .btn-delete:hover { background: #fecaca; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    .alert-success { background: #dcfce7; color: #16a34a; border-left: 4px solid #16a34a; }
    @media (max-width: 768px) {
        .activity-container { padding: 20px 15px; margin-top: 80px; }
        .activity-detail-card { padding: 25px; }
        .activity-detail-title { font-size: 1.4rem; }
        .activity-detail-header { flex-direction: column; }
        .activity-actions { flex-direction: column; }
        .btn-action { width: 100%; justify-content: center; }
    }
</style>
