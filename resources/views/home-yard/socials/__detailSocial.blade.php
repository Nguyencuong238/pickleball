<div class="modal-content" style="max-width: 700px;">
    <div class="modal-header"
        style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-bottom: none;">
        <h3 class="modal-title" style="color: white; margin: 0;">Chi Tiết Lịch Thi Đấu</h3>
        <button type="button" class="modal-close" style="color: white;" onclick="closeDetailModal()">×</button>
    </div>
    <div class="modal-body" id="detailModalBody" style="max-height: 70vh; overflow-y: auto;">

        <div style="padding: 0 0.5rem;">
            <!-- Title -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1.5rem; font-weight: 700;">
                    {{ $social->name }}</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Sân</div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 1rem;">
                            {{ $social->stadium->name ?? 'N/A' }}</div>
                    </div>
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Số người tối đa</div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 1rem;">
                            {{ $social->max_participants ?? 'N/A' }} người</div>
                    </div>
                </div>
            </div>

            <!-- Days of Week -->
            @if ($social->days_of_week && count($social->days_of_week) > 0)
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">
                        📅 Ngày trong tuần</h4>
                    <div
                        style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px; color: var(--text-primary); line-height: 1.6;">
                        @php
                            $days = [
                                '2' => 'Thứ 2',
                                '3' => 'Thứ 3',
                                '4' => 'Thứ 4',
                                '5' => 'Thứ 5',
                                '6' => 'Thứ 6',
                                '7' => 'Thứ 7',
                                '1' => 'Chủ nhật',
                            ];
                        @endphp
                        {{ implode(', ', array_map(fn($d) => $days[$d], $social->days_of_week)) }}
                    </div>
                </div>
            @endif
            <!-- Date & Time Info -->
            <div style="margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div
                        style="padding: 0.75rem; background: #f0f4ff; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                        <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem;">Giờ
                        </div>
                        <div style="color: var(--text-primary); font-weight: 600;">
                            {{ $social->start_time }} - {{ $social->end_time }}</div>
                    </div>
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Phí tham gia</div>
                        <div style="color: #10b981; font-weight: 700;">
                            {{ $social->fee ? number_format($social->fee, 0, ',', '.') . ' ₫' : 'Miễn phí' }}</div>
                    </div>
                </div>
            </div>

            <!-- Object & Fee Info -->
            <div style="margin-bottom: 1.5rem;">
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Đối tượng</div>
                            @php
                                $levels = [
                                    'beginner' => 'Người mới',
                                    'intermediate' => 'Trung cấp',
                                    'advanced' => 'Nâng cao',
                                ];
                            @endphp
                        <div style="color: var(--text-primary); font-weight: 600;">{{ $levels[$social->object] ?? 'N/A' }}
                        </div>
                    </div>
            </div>


            <!-- Description -->
            <div style="margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">
                    📝 Mô tả</h4>
                <div
                    style="padding: 1rem; background: var(--bg-light); border-radius: 6px; color: var(--text-primary); line-height: 1.6; white-space: pre-wrap;">{{ $social->description ?? 'Không có mô tả' }}</div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">Đóng</button>
    </div>
</div>
