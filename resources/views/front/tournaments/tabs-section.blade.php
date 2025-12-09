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
            <button class="tab-btn" data-tab="results">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                </svg>
                <span>Kết quả</span>
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

                    @if ($tournament->registration_benefits)
                        <h3>Quyền lợi khi đăng ký:</h3>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            @php
                                $benefitText = str_replace('/', "\n", $tournament->registration_benefits);
                                $benefits = array_filter(array_map('trim', explode("\n", $benefitText)));
                            @endphp
                            @foreach ($benefits as $benefit)
                                <li style="padding: 8px 0; color: #1f2937; font-weight: 500;">✓
                                    {{ preg_replace('/^✓\s*/', '', $benefit) }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Format & Rank -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 2rem;">
                <div class="content-card gradient-card">
                    <h2 class="content-title">Thể thức thi đấu</h2>
                    @if ($tournament->competition_format)
                        @php $formatMap = ['single' => '🎯 Đơn', 'double' => '👥 Đôi', 'mixed' => '🤝 Hỗn hợp']; @endphp
                        <div class="gradient-bg">
                            <p class="gradient-text">
                                {{ $formatMap[$tournament->competition_format] ?? $tournament->competition_format }}</p>
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
            <div class="content-card">
                <h2 class="content-title">Quy định thi đấu</h2>
                @if ($tournament->competition_rules)
                    @php
                        $ruleText = trim($tournament->competition_rules);
                        // Split by main sections (lines starting with numbers like "1 ", "2 ", etc)
                        $sections = preg_split('/(?=^\d+\s+)/m', $ruleText);
                        $sections = array_filter(array_map('trim', $sections));
                    @endphp
                    
                    @foreach ($sections as $section)
                        @php
                            $lines = array_filter(array_map('trim', explode("\n", $section)));
                            $firstLine = reset($lines);
                            $isHeader = preg_match('/^\d+\s+/', $firstLine);
                        @endphp
                        
                        @if ($isHeader)
                            <div style="margin-top: 28px; margin-bottom: 20px;">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px;">
                                    <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #ec4899, #3b82f6); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1rem;">
                                        {{ preg_match('/^(\d+)/', $firstLine, $matches) ? $matches[1] : '1' }}
                                    </div>
                                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #1f2937; margin: 0;">
                                        {{ preg_replace('/^\d+\s+/', '', $firstLine) }}
                                    </h3>
                                </div>
                                
                                <div style="display: flex; flex-direction: column; gap: 14px;">
                                    @foreach (array_slice($lines, 1) as $rule)
                                        @if (preg_match('/^[-•]/', $rule))
                                            <div style="display: flex; gap: 14px; padding: 14px; background: linear-gradient(135deg, rgba(236, 72, 153, 0.03), rgba(59, 130, 246, 0.03)); border-radius: 8px; border-left: 3px solid #ec4899;">
                                                <span style="color: #ec4899; font-weight: 700; flex-shrink: 0; margin-top: 2px;">✓</span>
                                                <p style="margin: 0; color: #374151; line-height: 1.6; font-size: 0.95rem;">
                                                    {{ preg_replace('/^[-•]\s*/', '', $rule) }}
                                                </p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <p style="color: #6b7280;">Chưa có thông tin quy định thi đấu</p>
                @endif
            </div>

            <!-- Timeline -->
            <div class="content-card">
                <h2 class="content-title">Timeline sự kiện</h2>
                @if ($tournament->event_timeline)
                    @php
                        $timelineLines = array_filter(array_map('trim', explode("\n", $tournament->event_timeline)));
                    @endphp
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
                                        {{ preg_replace('/^(\d{1,2}\/\d{1,2}\/\d{4})(.*)/', '$1', $cleanLine) }}</div>
                                    <p>{{ preg_replace('/^\d{1,2}\/\d{1,2}\/\d{4}\s*/', '', $cleanLine) }}</p>
                                @else
                                    <p>{{ $cleanLine }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p style="color: #6b7280;">Chưa có thông tin timeline</p>
                @endif
            </div>
        </div>

        <!-- Schedule Tab -->
        <div class="tab-pane" id="schedule">
            <div class="content-card">
                <h2 class="content-title">Lịch thi đấu chi tiết</h2>
                @if ($tournament->competition_schedule)
                    @php $scheduleLines = array_filter(array_map('trim', explode("\n", $tournament->competition_schedule))); @endphp
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        @foreach ($scheduleLines as $scheduleLine)
                            @if (preg_match('/^Ngày\s+\d+/i', $scheduleLine))
                                <div
                                    style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 12px 16px; border-radius: 8px; font-weight: 600; font-size: 1.1rem;">
                                    {{ $scheduleLine }}</div>
                            @elseif(preg_match('/^Sáng|^Chiều|^Tối/i', $scheduleLine))
                                <div
                                    style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 10px 14px; border-radius: 6px; font-weight: 500; font-size: 0.95rem;">
                                    {{ $scheduleLine }}</div>
                            @else
                                <div
                                    style="background: #f9fafb; border-left: 4px solid var(--primary-color); padding: 12px 16px; border-radius: 4px;">
                                    <p style="margin: 0; color: #1f2937; font-size: 0.95rem;">{{ $scheduleLine }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">Lịch thi đấu sẽ được cập nhật sau khi đóng đăng ký (30/11/2025)</div>
                @endif
            </div>
        </div>

        <!-- Results Tab -->
        <div class="tab-pane" id="results">
            <div class="content-card">
                <h2 class="content-title">Kết quả thi đấu</h2>
                @if ($tournament->results)
                    @php $lines = array_filter(array_map('trim', explode("\n", $tournament->results))); @endphp
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach ($lines as $line)
                            @if (preg_match('/^Ngày\s+\d+/i', $line))
                                <div
                                    style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 12px 16px; border-radius: 8px; font-weight: 600;">
                                    {{ $line }}</div>
                            @else
                                <div
                                    style="background: #f9fafb; border-left: 4px solid var(--primary-color); padding: 12px 16px; border-radius: 4px;">
                                    <p style="margin: 0; color: #1f2937; font-weight: 500;">{{ $line }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">Kết quả sẽ được cập nhật trong quá trình diễn ra giải đấu</div>
                @endif
            </div>
        </div>

        <!-- Participants Tab -->
        <div class="tab-pane" id="participants">
            <div class="content-card">
                <h2 class="content-title">Danh sách vận động viên</h2>
                @php
                    $athletes = $tournament->athletes()->get();
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
                                    <th>Email</th>
                                    <th>Điện thoại</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($athletes as $index => $athlete)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $athlete->athlete_name }}</td>
                                        <td>{{ $athlete->email }}</td>
                                        <td>{{ $athlete->phone }}</td>
                                        <td>
                                            <span
                                                class="status-badge @if ($athlete->status == 1) status-confirmed @else status-pending @endif">
                                                {{ $athlete->status == 1 ? 'Xác nhận' : 'Chờ xác nhận' }}
                                            </span>
                                        </td>
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
                <h2 class="content-title">Hình ảnh từ các mùa giải trước</h2>
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
                    <div class="gallery-grid">
                        <div class="gallery-item">
                            <div class="gallery-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>
                                <p>Chưa có hình ảnh</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
</script>
