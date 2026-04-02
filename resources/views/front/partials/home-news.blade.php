<section class="hp-section hp-news" id="news">
    <div class="section-inner">
        <div class="section-head reveal">
            <div>
                <div class="section-label">Tin tức</div>
                <h2 class="hp-section-title">Tin tức mới nhất</h2>
                <p class="section-desc">Cập nhật tin tức, kiến thức và xu hướng Pickleball</p>
            </div>
            <a class="btn-ghost" href="{{ route('news') }}">Xem tất cả</a>
        </div>
        <div class="hp-news-grid">
            @foreach($latestNews as $news)
                <article class="hp-news-card reveal">
                    <div class="news-image">
                        <img src="{{ storage_url($news->image) }}" alt="{{ $news->title }}">
                        <span class="news-category">{{ $news->category->name ?? 'Tin tức' }}</span>
                    </div>
                    <div class="news-content">
                        <div class="news-meta">
                            <span class="news-date">{{ $news->created_at->format('d/m/Y') }}</span>
                        </div>
                        <h3 class="news-title">{{ $news->title }}</h3>
                        <p class="news-excerpt">{!! Str::words(strip_tags($news->content), 20) !!}</p>
                        <a href="{{ route('news.show', $news->slug) }}" class="news-link">
                            Đọc tiếp
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
