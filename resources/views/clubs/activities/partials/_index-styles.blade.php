{{-- Styles for activity index/list page --}}
<style>
    .activities-container {
        padding: 40px 20px;
        max-width: 1000px;
        margin: 0 auto;
        margin-top: 100px;
    }
    .activities-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        flex-wrap: wrap;
        gap: 20px;
    }
    .activities-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
    }
    .btn-create-activity {
        padding: 12px 24px;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
    }
    .btn-create-activity:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
        color: white;
    }
    .activities-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .activity-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #00D9B5;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        color: inherit;
    }
    .activity-card:hover {
        box-shadow: 0 8px 30px rgba(0, 217, 181, 0.15);
        transform: translateX(5px);
    }
    .activity-card.type-competition { border-left-color: #f59e0b; }
    .activity-card.type-recurring { border-left-color: #16a34a; }
    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .activity-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .activity-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
        word-break: break-word;
    }
    .type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .type-one_off { background: #dbeafe; color: #0284c7; }
    .type-recurring { background: #dcfce7; color: #16a34a; }
    .type-competition { background: #ffedd5; color: #c2410c; }
    .activity-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-upcoming { background: #dbeafe; color: #0284c7; }
    .status-completed { background: #dcfce7; color: #16a34a; }
    .status-cancelled { background: #fee2e2; color: #b91c1c; }
    .activity-meta {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        flex-wrap: wrap;
        color: #6b7280;
        font-size: 0.95rem;
    }
    .activity-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .activity-description {
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    .activity-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    .btn-action {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-edit { background: #dbeafe; color: #0284c7; }
    .btn-edit:hover { background: #bfdbfe; }
    .btn-delete { background: #fee2e2; color: #b91c1c; }
    .btn-delete:hover { background: #fecaca; }
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
    .btn-back:hover { background: #e5e7eb; }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        color: #9ca3af;
    }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    .alert-success { background: #dcfce7; color: #16a34a; border-left: 4px solid #16a34a; }
    .participant-count {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 500;
    }
    @media (max-width: 768px) {
        .activities-header { flex-direction: column; align-items: flex-start; }
        .activities-header h2 { font-size: 1.5rem; }
        .activity-header { flex-direction: column; }
        .activity-actions { width: 100%; }
        .btn-action { flex: 1; text-align: center; }
    }
</style>
