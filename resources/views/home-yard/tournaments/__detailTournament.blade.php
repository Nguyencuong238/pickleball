<div class="modal-content" style="max-width: 700px;">
    <div class="modal-header"
        style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-bottom: none;">
        <h3 class="modal-title" style="color: white; margin: 0;">Chi Tiết Giải Đấu</h3>
        <button type="button" class="modal-close" style="color: white;" onclick="closeDetailModal()">×</button>
    </div>
    <div class="modal-body" id="detailModalBody" style="max-height: 70vh; overflow-y: auto;">

        <div style="padding: 0 0.5rem;">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1.5rem; font-weight: 700;">
                    {{ $tournament->name }}</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    @php
                        $tyepTournaments = [
                            'single' => 'Đơn',
                            'double' => 'Đôi',
                            'mixed' => 'Đôi nam nữ',
                        ];
                    @endphp
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Loại giải</div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 1rem;">
                            {{ @$tyepTournaments[$tournament->competition_format] }}</div>
                    </div>
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Số VĐV tối đa</div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 1rem;">
                            {{ $tournament->max_participants }} người</div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div
                        style="padding: 0.75rem; background: #f0f4ff; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                        <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem;">Ngày bắt đầu
                        </div>
                        <div style="color: var(--text-primary); font-weight: 600;">
                            {{ $tournament->start_date->format('d/m/Y') }}</div>
                    </div>
                    <div
                        style="padding: 0.75rem; background: #f0f4ff; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                        <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem;">Ngày kết thúc
                        </div>
                        <div style="color: var(--text-primary); font-weight: 600;">
                            {{ $tournament->end_date->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Địa điểm</div>
                        <div style="color: var(--text-primary); font-weight: 600;">{{ $tournament->location }}</div>
                    </div>
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Giải thưởng</div>
                        <div style="color: #10b981; font-weight: 700;">{{ $tournament->prizes }}</div>
                    </div>
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Lệ phí đăng ký</div>
                        <div style="color: var(--text-primary); font-weight: 600;">{{ $tournament->price }}</div>
                    </div>
                </div>
            </div>



            <div style="margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">📝
                    Mô tả</h4>
                <div
                    style="padding: 1rem; background: var(--bg-light); border-radius: 6px; color: var(--text-primary); line-height: 1.6; white-space: pre-wrap;">{{ $tournament->description }}</div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">⚽
                    Quy định &amp; Quyền lợi</h4>
                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                    <div
                        style="padding: 0.75rem; background: #f8f9fa; border-radius: 4px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Quy định thi đấu</div>
                        <div style="color: var(--text-primary); line-height: 1.5; white-space: pre-wrap;">{{ $tournament->competition_rules }}</div>
                    </div>
                    <div
                        style="padding: 0.75rem; background: #f8f9fa; border-radius: 4px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Quyền lợi khi tham gia</div>
                        <div style="color: var(--text-primary); line-height: 1.5; white-space: pre-wrap;">{{ $tournament->registration_benefits }}</div>
                    </div>

                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">
                    📮 Thông tin liên hệ</h4>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Email liên hệ</div>
                        <div style="color: var(--text-primary); font-weight: 600;">{{ $tournament->organizer_email }}</div>
                    </div>
                    <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                        <div
                            style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Số điện thoại</div>
                        <div style="color: var(--text-primary); font-weight: 600;">{{ $tournament->organizer_hotline }}</div>
                    </div>
                </div>
            </div>

            <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px; margin-bottom: 1.5rem;">
                <div
                    style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    Mạng xã hội</div>
                <div style="color: var(--text-primary); font-weight: 600;">{{ $tournament->organizer_social }}</div>
            </div>


            <div style="margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">
                    Banner</h4>
                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                    <div style="padding: 0.75rem; border-radius: 4px;">
                        @php
                            $bannerUrl = $tournament->getFirstMediaUrl('banner');
                        @endphp
                        @if($bannerUrl)
                            <img src="{{ $bannerUrl }}"
                                style="max-width: 100%; height: 200px; border-radius: 6px; object-fit: cover;" alt="Banner Giải Đấu">
                        @else
                            <div style="max-width: 100%; height: 200px; border-radius: 6px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem;">
                                Chưa có hình ảnh Banner
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">
                    Hình ảnh</h4>
                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                    @if($tournament->getMedia('gallery')->count() > 0)
                        <div style="margin: 0.75rem; border-radius: 4px; display: flex; gap: 10px; overflow-x: auto;">
                            @foreach ($tournament->getMedia('gallery') as $media)
                                <img src="{{ $media->getUrl() }}"
                                    style="max-width: 100%; height: 200px; border-radius: 6px; margin-bottom:15px; object-fit: cover;"
                                    alt="Hình ảnh Giải Đấu">
                            @endforeach
                        </div>
                    @else
                        <div style="padding: 1rem; text-align: center; background: var(--bg-light); border-radius: 6px; color: var(--text-light);">
                            Chưa có hình ảnh
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">Đóng</button>
    </div>
</div>
