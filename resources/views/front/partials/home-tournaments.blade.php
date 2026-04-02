<section class="hp-section hp-tournaments" id="tournaments">
    <div class="section-inner">
        <div class="section-head reveal">
            <div>
                <div class="section-label">Giải đấu</div>
                <h2 class="hp-section-title">Giải đấu sắp diễn ra</h2>
                <p class="section-desc">Đăng ký tham gia các giải đấu từ phong trào đến chuyên nghiệp trên toàn quốc.</p>
            </div>
            <a class="btn-ghost" href="{{ route('tournaments') }}">Xem tất cả &rarr;</a>
        </div>
        <div class="hp-tournaments-grid">
            @forelse($upcomingTournaments as $tournament)
                <div class="hp-tournament-card reveal">
                    <div class="tournament-accent"></div>
                    <div class="tournament-body">
                        <div class="tournament-meta">
                            @php
                                $now = now();
                                if ($now < $tournament->start_date) {
                                    $tagClass = 'tag-soon';
                                    $tagText = 'Sắp mở';
                                } else {
                                    $tagClass = 'tag-open';
                                    $tagText = 'Đang mở đăng ký';
                                }
                            @endphp
                            <span class="tag {{ $tagClass }}">{{ $tagText }}</span>
                            <span class="tournament-date">{{ $tournament->start_date->format('d/m/Y') }}
                                @if($tournament->end_date && $tournament->end_date->format('d/m/Y') != $tournament->start_date->format('d/m/Y'))
                                    &ndash; {{ $tournament->end_date->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                        <a href="{{ route('tournaments-detail', $tournament->slug) }}" class="tournament-name">{{ $tournament->name }}</a>
                        <div class="tournament-info">
                            <div class="tournament-info-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                {{ $tournament->stadium?->name ?? 'Chưa xác định' }}
                            </div>
                            <div class="tournament-info-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                {{ $tournament->athleteCount() }} VĐV
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <div class="prize">
                                @if($tournament->prizes)
                                    {{ number_format($tournament->prizes, 0, ',', '.') }} VNĐ
                                    <span>Giải thưởng</span>
                                @else
                                    Giải thưởng hấp dẫn
                                @endif
                            </div>
                            <a class="hp-btn-sm" href="{{ route('tournaments-detail', $tournament->slug) }}">
                                @if($tournament->is_watch == 1 || $tournament->start_date->isPast())
                                    Xem chi tiết
                                @else
                                    Đăng ký
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: #7a9b8a;">Hiện chưa có giải đấu nào sắp diễn ra</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
