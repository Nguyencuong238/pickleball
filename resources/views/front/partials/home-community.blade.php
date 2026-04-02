<section class="hp-section hp-community" id="community">
    <div class="section-inner">
        <div class="section-head reveal">
            <div>
                <div class="section-label">Cộng đồng</div>
                <h2 class="hp-section-title">Cộng đồng Pickleball toàn quốc</h2>
                <p class="section-desc">Tìm và tham gia cộng đồng pickleball tại tỉnh thành của bạn</p>
            </div>
        </div>
        <div class="community-list">
            @forelse($featuredClubs as $club)
                <div class="comm-card reveal">
                    <div class="comm-card-left">
                        <div class="comm-avatar">
                            <span>{{ strtoupper(mb_substr($club->name, 0, 2)) }}</span>
                        </div>
                    </div>
                    <div class="comm-card-body">
                        <div class="comm-card-top">
                            <div>
                                <div class="comm-name">{{ $club->name }}</div>
                            </div>
                            <div class="comm-badges">
                                @if($club->members_count > 100)
                                    <span class="comm-badge badge-hot">Hot</span>
                                @endif
                                <span class="comm-badge badge-open">Công khai</span>
                            </div>
                        </div>
                        @if($club->description)
                            <p class="comm-desc">{{ Str::limit($club->description, 120) }}</p>
                        @endif
                        <div class="comm-footer">
                            <div class="comm-members-row">
                                <span class="comm-count">{{ number_format($club->members_count) }} thành viên</span>
                            </div>
                            <a href="{{ route('clubs.show', $club) }}" class="btn-comm-join">Tham gia</a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: #7a9b8a;">Chưa có cộng đồng nào</p>
                </div>
            @endforelse
        </div>
        <div class="community-bottom-cta reveal">
            <div style="margin-bottom:16px;">
                <span style="color:var(--text-secondary);font-size:0.95rem;">Không tìm thấy cộng đồng tỉnh bạn?</span>
            </div>
            @auth
                <a href="{{ route('clubs.create') }}" class="btn-primary" style="padding:12px 28px;border-radius:10px;font-size:0.9rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">Tạo cộng đồng mới</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary" style="padding:12px 28px;border-radius:10px;font-size:0.9rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">Tạo cộng đồng mới</a>
            @endauth
        </div>
    </div>
</section>
