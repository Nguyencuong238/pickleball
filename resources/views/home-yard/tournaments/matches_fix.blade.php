                    <!-- Upcoming Matches Tab -->
                    <div class="tab-content" id="upcoming">
                        <div class="schedule-grid">
                            @if ($upcomingMatches->count() > 0)
                                @foreach ($upcomingMatches as $match)
                                    <div class="match-card">
                                        <div class="match-header">
                                            <div class="match-info">
                                                <span class="match-id">Trận
                                                    #{{ $match->match_number ?? $match->id }}</span>
                                                <span class="badge badge-warning">Sắp tới</span>
                                            </div>
                                            <div class="match-time">
                                                🕐 {{ $match->match_time?->format('H:i') ?? 'N/A' }} • 📅
                                                {{ $match->match_date?->format('d/m/Y') ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="match-body">
                                            <div class="player-side">
                                                <div class="player-card-mini">
                                                    <div class="player-avatar-sm">
                                                        {{ strtoupper(substr($match->athlete1_name ?? 'N/A', 0, 2)) }}
                                                    </div>
                                                    <div class="player-name-sm">{{ $match->athlete1_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="match-score">
                                                <div class="score-display"
                                                    style="font-size: 1.5rem; color: var(--text-light);">VS</div>
                                            </div>
                                            <div class="player-side">
                                                <div class="player-card-mini">
                                                    <div class="player-avatar-sm">
                                                        {{ strtoupper(substr($match->athlete2_name ?? 'N/A', 0, 2)) }}
                                                    </div>
                                                    <div class="player-name-sm">{{ $match->athlete2_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="match-footer">
                                            <div class="match-meta">
                                                <span class="match-meta-item">🏆
                                                    {{ $match->round?->name ?? 'N/A' }}</span>
                                                <span class="match-meta-item">👤
                                                    {{ $match->category?->name ?? 'N/A' }}</span>
                                                @if ($match->court)
                                                    <span class="court-badge">{{ $match->court->name }}</span>
                                                @endif
                                            </div>
                                            <div class="match-actions">
                                                <button class="btn btn-secondary btn-sm" onclick="openUpdateScoreModal({{ $match->tournament_id }}, {{ $match->id }})">📊 Cập nhật điểm</button>
                                                <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p style="text-align: center; padding: 2rem; color: var(--text-secondary);">Không có trận
                                    đấu nào sắp tới</p>
                            @endif
                        </div>
                        <!-- Pagination for Upcoming Matches -->
                        @if ($upcomingMatches->hasPages())
                            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                {{ $upcomingMatches->links('pagination::custom') }}
                            </div>
                        @endif
                    </div>
