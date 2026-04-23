<style>
    .tab-navigation {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 0;
        flex-wrap: wrap;
        background: white;
        padding: 0;
        overflow: auto hidden;
        -webkit-overflow-scrolling: touch;

    }

    .tab-navigation::-webkit-scrollbar {
        height: 4px;
    }

    .tab-navigation::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 2px;
    }

    .tab-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.95rem;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        white-space: nowrap;
        min-width: max-content;
    }

    .tab-btn .icon {
        width: 20px;
        height: 20px;
        stroke-width: 2;
        flex-shrink: 0;
    }

    .tab-content {
        padding: 30px 0;
    }

    .tab-pane {
        display: none;
        opacity: 0;
        animation: fadeIn 0.4s ease-out forwards;
    }

    .tab-pane.active {
        display: block;
        opacity: 1;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .detail-main {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .content-card {
        background: white;
        border-radius: 0;
        padding: 28px 30px;
        margin-bottom: 0;
        border-bottom: 1px solid #e5e7eb;
        border: none;
        transition: all 0.3s ease;
        box-shadow: none;
    }

    .content-card:last-child {
        border-bottom: none;
    }

    .content-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 24px 0;
    }

    .content-text p {
        margin: 0 0 16px 0;
        line-height: 1.6;
        color: #374151;
    }

    .content-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 20px 0 12px 0;
    }

    .content-text ul {
        margin: 0;
        padding-left: 24px;
    }

    .content-text li {
        margin-bottom: 8px;
        color: #374151;
        line-height: 1.6;
    }

    .gradient-card {
        background: rgba(0, 217, 181, 0.1);
        border-left: 5px solid var(--primary-color);
        box-shadow: none;
    }

    .gradient-bg {
        padding: 16px 18px;
        background: #fff;
        border-radius: 6px;
    }

    .gradient-text {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .rule-item {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 14px 16px;
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(236, 72, 153, 0.05), rgba(59, 130, 246, 0.05));
        border-left: 4px solid transparent;
        border-image: linear-gradient(135deg, #ec4899, #3b82f6) 1;
        margin-bottom: 12px;
    }

    .rule-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 8px;
        flex-shrink: 0;
        font-size: 1.3rem;
        box-shadow: 0 4px 12px rgba(236, 72, 153, 0.2);
    }

    .rule-text {
        margin: 0;
        color: #1f2937;
        flex: 1;
        font-weight: 500;
        line-height: 1.6;
    }

    .timeline-item {
        margin-bottom: 28px;
        display: flex;
        gap: 16px;
        padding-left: 0;
    }

    .timeline-marker {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #d1d5db;
        flex-shrink: 0;
        margin-top: 4px;
    }

    .timeline-item.completed .timeline-marker {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .timeline-item.current .timeline-marker {
        background: #ec4899;
        width: 18px;
        height: 18px;
        margin-top: 0;
        box-shadow: 0 0 0 5px rgba(236, 72, 153, 0.15);
    }

    .timeline-date {
        background: #ec4899;
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 600;
        margin-bottom: 8px;
        display: inline-block;
        font-size: 0.8rem;
    }

    .timeline-content p {
        margin: 0;
        color: #1f2937;
    }

    .participants-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .participant-stat {
        background: linear-gradient(135deg, rgb(2 211 177 / 10%), rgb(0 153 204 / 10%));
        padding: 20px;
        border-radius: 12px;
        text-align: center;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 18px;
    }

    .gallery-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        aspect-ratio: 4/3;
        background: #f3f4f6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .gallery-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .gallery-item:hover img {
        transform: scale(1.08);
    }

    .gallery-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #9ca3af;
    }

    .gallery-placeholder svg {
        width: 48px;
        height: 48px;
        margin-bottom: 8px;
    }

    .empty-state {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        color: #166534;
        font-weight: 500;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    table thead {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    table th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        color: #1f2937;
    }

    table tbody tr:hover {
        background: #fafbfc;
    }

    .status-badge {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-confirmed {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
    }

    @media (max-width: 768px) {
        .tab-navigation {
            padding: 0;
            gap: 0;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 12px 16px;
            font-size: 0.85rem;
            min-width: auto;
        }

        .tab-btn .icon {
            width: 18px;
            height: 18px;
        }

        .content-card {
            padding: 20px;
        }

        .content-title {
            font-size: 1.15rem;
        }

        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .participants-stats {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }

    /* ===== Front Schedule Tab ===== */
    .front-schedule-cat-tabs {
        display: flex;
        gap: 8px;
        padding: 0 30px 16px;
        flex-wrap: wrap;
    }

    .front-schedule-cat-tab {
        padding: 8px 18px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fff;
        font-size: 0.85rem;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
    }

    .front-schedule-cat-tab.active {
        background: var(--primary-color, #006646);
        color: #fff;
        border-color: var(--primary-color, #006646);
    }

    .front-schedule-cat-content {
        display: none;
    }

    .front-schedule-cat-content.active {
        display: block;
    }

    /* Group grid */
    .front-schedule-group-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 16px;
    }

    .front-schedule-group-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .front-schedule-group-header {
        background: #2d3748;
        color: #fff;
        padding: 10px 14px;
        font-weight: 700;
        font-size: 0.9rem;
        text-align: center;
    }

    /* Match rows */
    .front-schedule-match-list {
        border-bottom: 1px solid #e5e7eb;
    }

    .front-schedule-match-row {
        display: grid;
        grid-template-columns: 70px 1fr auto;
        align-items: center;
        padding: 6px 10px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.8rem;
        min-height: 52px;
    }

    .front-schedule-match-row:last-child {
        border-bottom: none;
    }

    .front-schedule-match-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .front-schedule-match-time {
        font-weight: 700;
        color: #1f2937;
        font-size: 0.82rem;
    }

    .front-schedule-match-code {
        font-size: 0.7rem;
        color: #9ca3af;
    }

    .front-schedule-match-athletes {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .front-schedule-athlete-name {
        color: #374151;
        font-size: 0.78rem;
        word-break: break-word;
    }

    .front-schedule-match-scores {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .front-schedule-set-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        min-width: 24px;
    }

    .front-schedule-score {
        font-weight: 700;
        font-size: 0.82rem;
        color: #9ca3af;
        min-width: 20px;
        text-align: center;
        line-height: 1.4;
    }

    .front-schedule-score.won {
        color: #10b981;
    }

    .front-schedule-score.lost {
        color: #ef4444;
    }

    .front-schedule-empty-msg {
        padding: 12px;
        text-align: center;
        color: #9ca3af;
        font-size: 0.82rem;
    }

    /* Standings per group */
    .front-schedule-standings {
        padding: 0;
    }

    .front-schedule-standings-header,
    .front-schedule-standings-row {
        display: grid;
        grid-template-columns: 30px 1fr 36px 36px 36px;
        padding: 6px 10px;
        font-size: 0.8rem;
        align-items: center;
    }

    .front-schedule-standings-header {
        background: #f9fafb;
        font-weight: 700;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
    }

    .front-schedule-standings-row {
        border-bottom: 1px solid #f3f4f6;
    }

    .front-schedule-standings-row.advanced {
        background: rgba(16, 185, 129, 0.06);
    }

    .front-schedule-standings-row:last-child {
        border-bottom: none;
    }

    .col-rank {
        font-weight: 700;
        color: #6b7280;
    }

    .col-name {
        color: #1f2937;
        font-size: 0.8rem;
        word-break: break-word;
    }

    .col-stat {
        text-align: center;
        font-weight: 700;
    }

    .col-stat.won {
        color: #10b981;
    }

    .col-stat.lost {
        color: #ef4444;
    }

    /* Summary table */
    .front-schedule-summary {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .front-schedule-summary-row {
        display: grid;
        grid-template-columns: 30px minmax(120px, auto) 1fr 36px 36px 36px;
        align-items: center;
        padding: 8px 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.85rem;
    }

    .front-schedule-summary-rank {
        color: #9ca3af;
        font-weight: 600;
    }

    .front-schedule-summary-name {
        color: #1f2937;
        font-weight: 500;
        font-size: 0.82rem;
        word-break: break-word;
    }

    .front-schedule-summary-bar-wrap {
        height: 8px;
        background: #f3f4f6;
        border-radius: 4px;
        overflow: hidden;
        margin: 0 12px;
    }

    .front-schedule-summary-bar {
        height: 100%;
        background: var(--primary-color, #006646);
        border-radius: 4px;
        min-width: 2px;
    }

    /* ===== Knockout Bracket ===== */
    .front-bracket-container {
        display: flex;
        gap: 0;
        overflow-x: auto;
        padding: 16px 0;
        align-items: stretch;
    }

    .front-bracket-round {
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        min-width: 240px;
        padding: 0 24px;
    }

    .front-bracket-round-header {
        text-align: center;
        font-size: 0.82rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 14px;
        padding: 10px 14px;
        background: #f3f4f6;
        border-radius: 6px;
    }

    .front-bracket-round-count {
        font-weight: 500;
        color: #6b7280;
        margin-left: 4px;
    }

    .front-bracket-round-matches {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        flex: 1;
        position: relative;
    }

    .front-bracket-pair {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        position: relative;
        flex: 1 0 auto;
        padding: 4px 0;
    }

    .front-bracket-match {
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        background: #fff;
        position: relative;
        margin: 3px 0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    /* Horizontal stub from each match going right (14px) */
    .front-bracket-round:not(:last-child) .front-bracket-match::before {
        content: '';
        position: absolute;
        right: -14px;
        top: 50%;
        width: 14px;
        height: 2px;
        background: #f97316;
        pointer-events: none;
        z-index: 0;
    }

    /* Vertical connector on pair spanning from first match center to second match center */
    .front-bracket-round:not(:last-child) .front-bracket-pair::before {
        content: '';
        position: absolute;
        right: -14px;
        top: 25%;
        bottom: 25%;
        width: 2px;
        background: #f97316;
        pointer-events: none;
        z-index: 0;
    }

    /* Horizontal bridge from pair midpoint across round gap into next round's match */
    .front-bracket-round:not(:last-child) .front-bracket-pair::after {
        content: '';
        position: absolute;
        right: -48px;
        top: calc(50% - 1px);
        width: 34px;
        height: 2px;
        background: #f97316;
        pointer-events: none;
        z-index: 0;
    }

    /* Pair with only 1 match (odd tail) — no vertical, only straight line */
    .front-bracket-round:not(:last-child) .front-bracket-pair:has(> .front-bracket-match:only-child)::before {
        display: none;
    }

    .front-bracket-match--done {
        border-color: #d1d5db;
    }

    .front-bracket-match--live {
        border-color: #f59e0b;
    }

    .front-bracket-match--bye {
        opacity: 0.85;
    }

    .front-bracket-match-live {
        position: absolute;
        top: 3px;
        right: 3px;
        font-size: 0.6rem;
        font-weight: 700;
        color: #fff;
        background: #f59e0b;
        border-radius: 3px;
        padding: 1px 5px;
        z-index: 2;
    }

    /* Match banner header */
    .front-bracket-match-banner {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #16a34a;
        color: #fff;
        padding: 5px 8px;
        font-size: 0.72rem;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
    }

    .front-bracket-banner-icon {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
        color: #fff;
        opacity: 0.9;
    }

    .front-bracket-banner-code {
        font-weight: 700;
        flex-shrink: 0;
    }

    .front-bracket-banner-meta {
        font-weight: 500;
        opacity: 0.95;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .front-bracket-slot {
        padding: 7px 10px;
        font-size: 0.82rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
        color: #374151;
        min-height: 32px;
        position: relative;
        background: #fff;
    }

    .front-bracket-slot + .front-bracket-slot {
        border-top: 1px solid #f1f5f9;
    }

    .front-bracket-slot--winner {
        font-weight: 700;
        color: #065f46;
        background: #f0fdf4;
    }

    .front-bracket-slot--loser {
        color: #9ca3af;
    }

    .front-bracket-slot--bye .front-bracket-slot-bye-label {
        color: #9ca3af;
        font-weight: 600;
        font-style: italic;
    }

    .front-bracket-slot-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        min-width: 0;
    }

    .front-bracket-slot-sets {
        display: flex;
        gap: 2px;
        flex-shrink: 0;
    }

    .front-bracket-set-score {
        font-weight: 600;
        min-width: 18px;
        text-align: center;
        font-size: 0.72rem;
        padding: 1px 3px;
        border-radius: 2px;
        background: #f3f4f6;
        color: #6b7280;
    }

    .front-bracket-slot--winner .front-bracket-set-score {
        background: #e5e7eb;
        color: #111827;
    }

    .front-bracket-slot-total {
        font-weight: 800;
        min-width: 22px;
        text-align: center;
        font-size: 0.9rem;
        flex-shrink: 0;
        color: #6b7280;
    }

    .front-bracket-slot--winner .front-bracket-slot-total {
        color: #059669;
    }

    .front-bracket-slot--loser .front-bracket-slot-total {
        color: #9ca3af;
    }

    /* ===== Responsive ===== */
    @media (max-width: 1200px) {
        .front-schedule-group-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .front-schedule-group-grid {
            grid-template-columns: 1fr;
        }

        .front-schedule-summary-row {
            grid-template-columns: 24px 90px 1fr 30px 30px 30px;
        }

        .front-bracket-round {
            min-width: 220px;
            padding: 0 18px;
        }

        .front-bracket-round:not(:last-child) .front-bracket-match::before {
            right: -18px;
            width: 18px;
        }

        .front-bracket-round:not(:last-child) .front-bracket-match::after {
            right: -18px;
        }

        .front-bracket-match-banner {
            font-size: 0.68rem;
            padding: 4px 6px;
        }

        .front-bracket-slot {
            padding: 6px 8px;
            font-size: 0.78rem;
        }
    }
</style>

<div class="detail-main">
    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" data-tab="overview">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
            </svg>
            <span>Tổng quan</span>
        </button>
        @if ($tournament->is_watch != 1)
            <button class="tab-btn" data-tab="schedule">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <span>Lịch thi đấu</span>
            </button>
            <button class="tab-btn" data-tab="participants">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span>VĐV</span>
            </button>
        @endif
        <button class="tab-btn" data-tab="gallery">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                <circle cx="8.5" cy="8.5" r="1.5" />
                <polyline points="21 15 16 10 5 21" />
            </svg>
            <span>Gallery</span>
        </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Overview Tab -->
        <div class="tab-pane active" id="overview">
            <div class="content-card">
                <h2 class="content-title">Giới thiệu giải đấu</h2>
                <div class="content-text">
                    <p>{!! nl2br(e($tournament->description)) !!}</p>

                    <h3>Điểm nổi bật:</h3>
                    <ul>
                        <li>✓ Tổng giải thưởng: {{ number_format($tournament->prizes, 0, ',', '.') }} VNĐ</li>
                        <li>✓ Địa điểm: {{ $tournament->location }}</li>
                        <li>✓ Tổng người tham gia: {{ $tournament->max_participants }} VĐV</li>
                        @php
                            $formatMap = ['single' => '🎯 Đơn', 'double' => '👥 Đôi', 'mixed' => '🤝 Hỗn hợp'];
                        @endphp
                        @if ($tournament->competition_format)
                            <li>✓ Thể thức:
                                {{ $formatMap[$tournament->competition_format] ?? $tournament->competition_format }}
                            </li>
                        @endif
                    </ul>


                </div>
            </div>

            <!-- Format & Rank -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 2rem;">
                <div class="content-card gradient-card">
                    <h2 class="content-title">Thể thức thi đấu</h2>
                    @php
                        // Get category formats from tournament categories
                        $categories = $tournament->categories ?? collect();
                        $formatTexts = [];
                        $formatMap = [
                            'single_men' => '🎯 Đơn',
                            'single_women' => '🎯 Đơn',
                            'double_men' => '👥 Đôi',
                            'double_women' => '👥 Đôi',
                            'double_mixed' => '🤝 Hỗn hợp',
                        ];

                        foreach ($categories as $category) {
                            $text = $formatMap[$category->category_type] ?? $category->category_type;
                            if (!in_array($text, $formatTexts)) {
                                $formatTexts[] = $text;
                            }
                        }
                    @endphp

                    @if (!empty($formatTexts))
                        <div class="gradient-bg">
                            <p class="gradient-text">
                                {{ implode(', ', $formatTexts) }}
                            </p>
                        </div>
                    @elseif ($tournament->competition_format)
                        @php 
                            $fallbackMap = ['single' => '🎯 Đơn', 'double' => '👥 Đôi', 'mixed' => '🤝 Hỗn hợp'];
                        @endphp
                        <div class="gradient-bg">
                            <p class="gradient-text">
                                {{ $fallbackMap[$tournament->competition_format] ?? $tournament->competition_format }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="content-card gradient-card">
                    <h2 class="content-title">Hạng đấu</h2>
                    @if ($tournament->tournament_rank)
                        @php
                            $rankMap = [
                                'beginner' => '🟢 Sơ cấp - Người mới bắt đầu',
                                'intermediate' => '🟡 Trung cấp - Có kinh nghiệm',
                                'advanced' => '🟠 Cao cấp - Tay vợt mạnh',
                                'professional' => '🔴 Chuyên nghiệp - Tay vợt hàng đầu',
                            ];
                        @endphp
                        <div class="gradient-bg">
                            <p class="gradient-text">
                                {{ $rankMap[$tournament->tournament_rank] ?? $tournament->tournament_rank }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Rules -->
            <div class="content-card" style="margin-bottom: 0;">
                <h2 class="content-title">Luật thi đấu</h2>
                @if ($tournament->competition_rules && trim($tournament->competition_rules) !== '')
                    @php
                        $ruleText = trim($tournament->competition_rules);
                        $sections = preg_split('/(?=^\d+\s+)/m', $ruleText);
                        $sections = array_filter(array_map('trim', $sections));
                    @endphp
                    
                    @if (!empty($sections))
                        @forelse($sections as $section)
                            @php
                                $lines = array_filter(array_map('trim', explode("\n", $section)));
                                $firstLine = reset($lines);
                                $isHeader = preg_match('/^\d+\s+/', $firstLine);
                            @endphp
                            
                            @if ($isHeader)
                                <div style="margin-top: 16px; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #ec4899, #3b82f6); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1rem;">
                                            {{ preg_match('/^(\d+)/', $firstLine, $matches) ? $matches[1] : '1' }}
                                        </div>
                                        <h3 style="font-size: 1.15rem; font-weight: 700; color: #1f2937; margin: 0;">
                                            {{ preg_replace('/^\d+\s+/', '', $firstLine) }}
                                        </h3>
                                    </div>
                                    
                                    <div style="display: flex; flex-direction: column; gap: 12px;">
                                        @foreach (array_slice($lines, 1) as $rule)
                                            @if (preg_match('/^[-•]/', $rule))
                                                <div style="display: flex; gap: 14px; padding: 12px; background: linear-gradient(135deg, rgba(236, 72, 153, 0.03), rgba(59, 130, 246, 0.03)); border-radius: 8px; border-left: 3px solid #ec4899;">
                                                    <span style="color: #ec4899; font-weight: 700; flex-shrink: 0; margin-top: 2px;">✓</span>
                                                    <p style="margin: 0; color: #374151; line-height: 1.6; font-size: 0.95rem;">
                                                        {{ preg_replace('/^[-•]\s*/', '', $rule) }}
                                                    </p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    @foreach ($lines as $line)
                                        <div style="display: flex; gap: 14px; padding: 12px; background: linear-gradient(135deg, rgba(236, 72, 153, 0.03), rgba(59, 130, 246, 0.03)); border-radius: 8px; border-left: 3px solid #ec4899;">
                                            <span style="color: #ec4899; font-weight: 700; flex-shrink: 0; margin-top: 2px;">✓</span>
                                            <p style="margin: 0; color: #374151; line-height: 1.6; font-size: 0.95rem;">
                                                {{ $line }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @empty
                            <p style="color: #6b7280;">Chưa có thông tin luật thi đấu</p>
                        @endforelse
                    @else
                        @php
                            $lines = array_filter(array_map('trim', explode("\n", $ruleText)));
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach ($lines as $line)
                                <div style="display: flex; gap: 14px; padding: 12px; background: linear-gradient(135deg, rgba(236, 72, 153, 0.03), rgba(59, 130, 246, 0.03)); border-radius: 8px; border-left: 3px solid #ec4899;">
                                    <span style="color: #ec4899; font-weight: 700; flex-shrink: 0; margin-top: 2px;">✓</span>
                                    <p style="margin: 0; color: #374151; line-height: 1.6; font-size: 0.95rem;">
                                        {{ $line }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p style="color: #6b7280; padding: 20px 0; margin: 0;">Chưa có thông tin luật thi đấu</p>
                @endif
            </div>

            @if ($tournament->registration_benefits)
                <div class="content-card">
                    <h2 class="content-title">Quyền lợi khi đăng ký</h2>
                    @php
                        $benefitText = str_replace('/', "\n", $tournament->registration_benefits);
                        $benefits = array_filter(array_map('trim', explode("\n", $benefitText)));
                    @endphp
                    <ul class="benefits-list">
                        @foreach ($benefits as $benefit)
                            <li>✓ {{ preg_replace('/^✓\s*/', '', $benefit) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($tournament->organizer_email || $tournament->organizer_hotline || $tournament->social_information)
                <div class="content-card">
                    <h2 class="content-title">Thông tin liên hệ</h2>
                    <div class="contact-grid">
                        @if ($tournament->organizer_email)
                            <div class="contact-item-h">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <div class="contact-label">Email</div>
                                    <div class="contact-value">{{ $tournament->organizer_email }}</div>
                                </div>
                            </div>
                        @endif
                        @if ($tournament->organizer_hotline)
                            <div class="contact-item-h">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" />
                                </svg>
                                <div>
                                    <div class="contact-label">Hotline</div>
                                    <div class="contact-value">{{ $tournament->organizer_hotline }}</div>
                                </div>
                            </div>
                        @endif
                        @if ($tournament->social_information)
                            <div class="contact-item-h">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 6v6l4 2" />
                                </svg>
                                <div>
                                    <div class="contact-label">Mạng xã hội</div>
                                    <div class="contact-value">{!! nl2br(e($tournament->social_information)) !!}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="content-card">
                <h2 class="content-title">Timeline sự kiện</h2>
                @if ($tournament->event_timeline)
                    @php
                        $timelineLines = array_filter(array_map('trim', explode("\n", $tournament->event_timeline)));
                    @endphp
                    <div class="timeline">
                        @foreach ($timelineLines as $line)
                            <div
                                class="timeline-item @if (preg_match('/Hoàn thành|✓/i', $line)) completed @elseif(preg_match('/Hiện tại|→/i', $line)) current @endif">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    @php
                                        $cleanLine = preg_replace(
                                            '/^(Hoàn thành|Completed|Hiện tại|Current|✓|→)\s*/i',
                                            '',
                                            $line,
                                        );
                                        $hasDate = preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $cleanLine);
                                    @endphp
                                    @if ($hasDate)
                                        <div class="timeline-date">
                                            {{ preg_replace('/^(\d{1,2}\/\d{1,2}\/\d{4})(.*)/', '$1', $cleanLine) }}
                                        </div>
                                        <p>{{ preg_replace('/^\d{1,2}\/\d{1,2}\/\d{4}\s*/', '', $cleanLine) }}</p>
                                    @else
                                        <p>{{ $cleanLine }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="color: #6b7280;">Chưa có thông tin timeline</p>
                @endif
            </div> 
        </div>

        <!-- Schedule Tab -->
        <div class="tab-pane" id="schedule">
            @php
                $hasScheduleData = $tournament->categories->contains(fn($c) => $c->groups->isNotEmpty());
                $hasBracketData = isset($bracketRounds) && $bracketRounds->isNotEmpty();
            @endphp

            @if ($hasScheduleData || $hasBracketData)
                {{-- Category tabs --}}
                @if ($tournament->categories->count() > 1)
                    <div class="front-schedule-cat-tabs">
                        @foreach ($tournament->categories as $catIdx => $cat)
                            <button class="front-schedule-cat-tab {{ $catIdx === 0 ? 'active' : '' }}"
                                    data-schedule-cat="{{ $cat->id }}">
                                {{ $cat->category_name }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @foreach ($tournament->categories as $catIdx => $cat)
                    <div class="front-schedule-cat-content {{ $catIdx === 0 ? 'active' : '' }}"
                         id="schedule-cat-{{ $cat->id }}">

                        {{-- Group stage --}}
                        @if ($cat->groups->isNotEmpty())
                            <div class="content-card">
                                <h2 class="content-title">Vòng bảng</h2>
                                <div class="front-schedule-group-grid">
                                    @foreach ($cat->groups as $group)
                                        <div class="front-schedule-group-card">
                                            <div class="front-schedule-group-header">
                                                {{ $group->group_name }}
                                            </div>
                                            {{-- Match rows --}}
                                            <div class="front-schedule-match-list">
                                                @forelse ($group->matches as $match)
                                                    <div class="front-schedule-match-row">
                                                        <div class="front-schedule-match-info">
                                                            <span class="front-schedule-match-time">{{ $match->match_time ? \Carbon\Carbon::parse($match->match_time)->format('H:i') : '--:--' }}</span>
                                                            <span class="front-schedule-match-code">{{ $group->group_code }}-#{{ $match->match_number }}</span>
                                                        </div>
                                                        <div class="front-schedule-match-athletes">
                                                            <div class="front-schedule-match-athlete">
                                                                <span class="front-schedule-athlete-name">{{ $match->athlete1_name ?: 'Chưa xác định' }}</span>
                                                            </div>
                                                            <div class="front-schedule-match-athlete">
                                                                <span class="front-schedule-athlete-name">{{ $match->athlete2_name ?: 'Chưa xác định' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="front-schedule-match-scores">
                                                            @if (($match->status === 'completed' || $match->status === 'in_progress') && !empty($match->set_scores))
                                                                @foreach ($match->set_scores as $set)
                                                                    @php
                                                                        $sv1 = $set['athlete1_score'] ?? $set['athlete1'] ?? $set['s1'] ?? 0;
                                                                        $sv2 = $set['athlete2_score'] ?? $set['athlete2'] ?? $set['s2'] ?? 0;
                                                                    @endphp
                                                                    <div class="front-schedule-set-col">
                                                                        <span class="front-schedule-score {{ $sv1 > $sv2 ? 'won' : ($sv1 < $sv2 ? 'lost' : '') }}">{{ $sv1 }}</span>
                                                                        <span class="front-schedule-score {{ $sv2 > $sv1 ? 'won' : ($sv2 < $sv1 ? 'lost' : '') }}">{{ $sv2 }}</span>
                                                                    </div>
                                                                @endforeach
                                                            @elseif ($match->status === 'completed' || $match->status === 'in_progress')
                                                                <div class="front-schedule-set-col">
                                                                    @php $s1 = $match->athlete1_score ?? 0; $s2 = $match->athlete2_score ?? 0; @endphp
                                                                    <span class="front-schedule-score {{ $s1 > $s2 ? 'won' : ($s1 < $s2 ? 'lost' : '') }}">{{ $s1 }}</span>
                                                                    <span class="front-schedule-score {{ $s2 > $s1 ? 'won' : ($s2 < $s1 ? 'lost' : '') }}">{{ $s2 }}</span>
                                                                </div>
                                                            @else
                                                                <div class="front-schedule-set-col">
                                                                    <span class="front-schedule-score">-</span>
                                                                    <span class="front-schedule-score">-</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="front-schedule-empty-msg">Chưa có trận đấu</div>
                                                @endforelse
                                            </div>
                                            {{-- Standings --}}
                                            @php
                                                // For doubles: filter out partner duplicates (keep athlete where id < partner_id)
                                                $filteredStandings = $cat->isDoubles()
                                                    ? $group->standings->filter(fn($s) => !$s->athlete?->partner_id || $s->athlete->id < $s->athlete->partner_id)
                                                    : $group->standings;
                                                // Re-rank after filtering
                                                $filteredStandings = $filteredStandings->values();
                                            @endphp
                                            @if ($filteredStandings->isNotEmpty())
                                                <div class="front-schedule-standings">
                                                    <div class="front-schedule-standings-header">
                                                        <span class="col-rank">#</span>
                                                        <span class="col-name">{{ $group->group_name }}</span>
                                                        <span class="col-stat">W</span>
                                                        <span class="col-stat">L</span>
                                                        <span class="col-stat">+/-</span>
                                                    </div>
                                                    @foreach ($filteredStandings as $standing)
                                                        @php $diff = $standing->matches_won - $standing->matches_lost; @endphp
                                                        <div class="front-schedule-standings-row {{ $standing->is_advanced ? 'advanced' : '' }}">
                                                            <span class="col-rank">{{ $loop->iteration }}</span>
                                                            <span class="col-name">{{ $cat->isDoubles() ? ($standing->athlete?->pair_name ?? '--') : ($standing->athlete?->athlete_name ?? '--') }}</span>
                                                            <span class="col-stat won">{{ $standing->matches_won }}</span>
                                                            <span class="col-stat lost">{{ $standing->matches_lost }}</span>
                                                            <span class="col-stat" style="color: {{ $diff > 0 ? '#10b981' : ($diff < 0 ? '#ef4444' : '#9ca3af') }}">{{ $diff > 0 ? '+' : '' }}{{ $diff }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Summary standings --}}
                            {{-- @php
                                $allStandings = $cat->groups->flatMap(fn($g) => $g->standings);
                                // For doubles: filter out partner duplicates
                                if ($cat->isDoubles()) {
                                    $allStandings = $allStandings->filter(fn($s) => !$s->athlete?->partner_id || $s->athlete->id < $s->athlete->partner_id);
                                }
                                $allStandings = $allStandings->sortByDesc('matches_won')->values();
                            @endphp
                            @if ($allStandings->isNotEmpty())
                                <div class="content-card">
                                    <h2 class="content-title">Tổng kết bảng</h2>
                                    <div class="front-schedule-summary">
                                        @foreach ($allStandings as $standing)
                                            <div class="front-schedule-summary-row">
                                                <span class="front-schedule-summary-rank">{{ $loop->iteration }}</span>
                                                <span class="front-schedule-summary-name">{{ $cat->isDoubles() ? ($standing->athlete?->pair_name ?? '--') : ($standing->athlete?->athlete_name ?? '--') }}</span>
                                                <div class="front-schedule-summary-bar-wrap">
                                                    @php
                                                        $total = $standing->matches_won + $standing->matches_lost;
                                                        $pct = $total > 0 ? round(($standing->matches_won / $total) * 100) : 0;
                                                    @endphp
                                                    <div class="front-schedule-summary-bar" style="width: {{ $pct }}%;"></div>
                                                </div>
                                                <span class="col-stat won">{{ $standing->matches_won }}</span>
                                                <span class="col-stat lost">{{ $standing->matches_lost }}</span>
                                                @php $diff = $standing->matches_won - $standing->matches_lost; @endphp
                                                <span class="col-stat" style="color: {{ $diff > 0 ? '#10b981' : ($diff < 0 ? '#ef4444' : '#9ca3af') }}">{{ $diff > 0 ? '+' : '' }}{{ $diff }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif--}}
                        @endif

                        {{-- Knockout bracket --}}
                        @php $catBracketRounds = isset($bracketRounds) ? ($bracketRounds[$cat->id] ?? collect()) : collect(); @endphp
                        @if ($catBracketRounds->isNotEmpty())
                            <div class="content-card">
                                <h2 class="content-title">Vòng đấu loại trực tiếp</h2>
                                <div class="front-bracket-container">
                                    @php
                                        $roundsList = $catBracketRounds->values();
                                    @endphp
                                    @foreach ($roundsList as $roundIdx => $round)
                                        @php
                                            $nextRound = $roundsList[$roundIdx + 1] ?? null;
                                            if ($nextRound) {
                                                $nextMatches = $nextRound->matches->values();
                                                $orderedMatches = $round->matches->sortBy(function ($m) use ($nextMatches) {
                                                    $nextIdx = 9999;
                                                    $subIdx = 9;
                                                    // Prefer winner → next match mapping (actual advancement)
                                                    if ($m->winner_id) {
                                                        foreach ($nextMatches as $idx => $nm) {
                                                            if ($nm->athlete1_id == $m->winner_id) { $nextIdx = $idx; $subIdx = 0; break; }
                                                            if ($nm->athlete2_id == $m->winner_id) { $nextIdx = $idx; $subIdx = 1; break; }
                                                        }
                                                    }
                                                    // Fallback: next_match_id
                                                    if ($nextIdx === 9999 && $m->next_match_id) {
                                                        foreach ($nextMatches as $idx => $nm) {
                                                            if ($nm->id == $m->next_match_id) { $nextIdx = $idx; break; }
                                                        }
                                                    }
                                                    return $nextIdx * 1000000 + $subIdx * 1000 + (int) ($m->bracket_position ?? $m->match_number ?? 0);
                                                })->values();
                                            } else {
                                                $orderedMatches = $round->matches->values();
                                            }
                                        @endphp
                                        <div class="front-bracket-round">
                                            <div class="front-bracket-round-header">
                                                {{ $round->round_name }}
                                                <span class="front-bracket-round-count">({{ $round->completed_matches ?? 0 }}/{{ $round->total_matches ?? 0 }})</span>
                                            </div>
                                            <div class="front-bracket-round-matches">
                                                @foreach ($orderedMatches->chunk(2) as $pair)
                                                    <div class="front-bracket-pair">
                                                        @foreach ($pair as $match)
                                                            @include('front.tournaments.partials._front-bracket-match', ['match' => $match])
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @elseif ($tournament->competition_schedule)
                {{-- Fallback: text-based schedule --}}
                <div class="content-card">
                    <h2 class="content-title">Lịch thi đấu chi tiết</h2>
                    @php $scheduleLines = array_filter(array_map('trim', explode("\n", $tournament->competition_schedule))); @endphp
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        @foreach ($scheduleLines as $scheduleLine)
                            @if (preg_match('/^Ngay\s+\d+/i', $scheduleLine))
                                <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 12px 16px; border-radius: 8px; font-weight: 600; font-size: 1.1rem;">
                                    {{ $scheduleLine }}</div>
                            @else
                                <div style="background: #f9fafb; border-left: 4px solid var(--primary-color); padding: 12px 16px; border-radius: 4px;">
                                    <p style="margin: 0; color: #1f2937; font-size: 0.95rem;">{{ $scheduleLine }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="content-card">
                    <div class="empty-state">Lịch thi đấu sẽ được cập nhật sau khi đóng đăng ký</div>
                </div>
            @endif
        </div>

        <!-- Participants Tab -->
        <div class="tab-pane" id="participants">
            <div class="content-card">
                <h2 class="content-title">Danh sách vận động viên</h2>
                @php
                    $athletes = $tournament->athletes()->with('user')->get()
                        ->unique(function ($a) {
                            $email = strtolower(trim((string) $a->email));
                            $phone = preg_replace('/\D/', '', (string) $a->phone);
                            $name = strtolower(trim((string) $a->athlete_name));
                            return $email . '|' . $phone . '|' . $name;
                        })
                        ->values();
                    $athleteCount = $athletes->count();
                    $remaining = $tournament->max_participants - $athleteCount;
                @endphp
                <div class="participants-stats">
                    <div class="participant-stat">
                        <div class="stat-number">{{ $athleteCount }}/{{ $tournament->max_participants }}</div>
                        <div class="stat-label">Đã đăng ký</div>
                    </div>
                    <div class="participant-stat">
                        <div class="stat-number">{{ max(0, $remaining) }}</div>
                        <div class="stat-label">Còn lại</div>
                    </div>
                </div>
                @if ($athletes->count() > 0)
                    <div style="margin-top: 2rem; overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên VĐV</th>
                                    <th>Điện thoại</th>
                                    <th>Điểm trình độ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($athletes as $index => $athlete)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $athlete->athlete_name }}</td>
                                        <td>{{ $athlete->phone ? 'xxxx' . substr($athlete->phone, -4) : '--' }}</td>
                                        <td>{{ $athlete->user->opr_level ?? '--' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Danh sách VĐV sẽ được công bố sau khi đóng đăng ký (30/11/2025)</p>
                @endif
            </div>
        </div>

        <!-- Gallery Tab -->
        <div class="tab-pane" id="gallery">
            <div class="content-card">
                <h2 class="content-title">Hình ảnh</h2>
                @php
                    $galleryItems = is_array($tournament->gallery)
                        ? $tournament->gallery
                        : ($tournament->gallery
                            ? json_decode($tournament->gallery, true)
                            : []);
                @endphp
                @if (!empty($galleryItems))
                    <div class="gallery-grid">
                        @foreach ($galleryItems as $item)
                            <div class="gallery-item">
                                @if (is_array($item) && isset($item['url']))
                                    <img src="{{ $item['url'] }}" alt="{{ $item['title'] ?? 'Gallery' }}">
                                    @if (isset($item['title']))
                                        <p style="text-align: center; padding: 0.5rem; font-size: 0.875rem;">
                                            {{ $item['title'] }}</p>
                                    @endif
                                @else
                                    <img src="{{ $item }}" alt="Gallery">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">Chưa có hình ảnh</div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    // Schedule category tabs
    document.querySelectorAll('.front-schedule-cat-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            const catId = btn.dataset.scheduleCat;
            document.querySelectorAll('.front-schedule-cat-tab').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.front-schedule-cat-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const target = document.getElementById('schedule-cat-' + catId);
            if (target) target.classList.add('active');
        });
    });
</script>
