<section class="hp-section hp-courts" id="courts">
    <div class="section-inner">
        <div class="section-head reveal">
            <div>
                <div class="section-label">Sân tập</div>
                <h2 class="hp-section-title">Sân pickleball gần bạn</h2>
                <p class="section-desc">Hệ thống sân pickleball chất lượng cao trên toàn quốc</p>
            </div>
            <a class="btn-ghost" href="{{ route('courts') }}">Xem tất cả sân</a>
        </div>
        <div class="hp-courts-grid">
            @forelse($featuredStadiums as $stadium)
                @if($stadium && $stadium->id)
                <div class="hp-court-card-item reveal">
                    <div class="hp-court-img">
                        @php
                            $bannerUrl = $stadium->getFirstMediaUrl('banner') ?: asset('assets/images/court_default.svg');
                        @endphp
                        <img src="{{ $bannerUrl }}" alt="{{ $stadium->name }}">
                        <div class="hp-court-rating">4.8</div>
                    </div>
                    <div class="hp-court-info">
                        <div class="hp-court-name">{{ $stadium->name }}</div>
                        <div class="hp-court-location">{{ $stadium->address }}</div>
                        <div class="court-tags">
                            <span class="court-tag">{{ $stadium->courts->count() }} sân</span>
                            @php
                                $amenities = $stadium->amenities;
                                if (is_string($amenities)) {
                                    $amenities = json_decode($amenities, true);
                                }
                                $amenities = is_array($amenities) ? array_slice($amenities, 0, 3) : [];
                            @endphp
                            @foreach($amenities as $amenity)
                                <span class="court-tag">{{ $amenity }}</span>
                            @endforeach
                        </div>
                        <div class="court-footer-row">
                            <div class="court-price">150.000đ - 300.000đ <small>/ giờ</small></div>
                            <a class="hp-btn-sm" href="{{ route('courts-detail', $stadium) }}">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                @endif
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: #7a9b8a;">Hiện chưa có sân thi đấu nào</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
