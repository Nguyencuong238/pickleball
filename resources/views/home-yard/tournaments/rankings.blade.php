@extends('layouts.homeyard')
<style>
    /* Page-specific styles */
    .ranking-filters {
        background: var(--bg-white);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }

    .filter-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid var(--border-color);
        background: var(--bg-white);
        border-radius: var(--radius-full);
        cursor: pointer;
        transition: all var(--transition);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .filter-btn.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
    }

    .podium {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
        align-items: end;
    }

    .podium-item {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        padding: 2rem;
        text-align: center;
        position: relative;
        transition: all var(--transition);
    }

    .podium-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .podium-item.first {
        order: 2;
        padding-top: 3rem;
        box-shadow: var(--shadow-xl);
    }

    .podium-item.second {
        order: 1;
    }

    .podium-item.third {
        order: 3;
    }

    .podium-medal {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
    }

    .podium-item.first .podium-medal {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        width: 100px;
        height: 100px;
        font-size: 3rem;
    }

    .podium-item.second .podium-medal {
        background: linear-gradient(135deg, #C0C0C0, #808080);
    }

    .podium-item.third .podium-medal {
        background: linear-gradient(135deg, #CD7F32, #8B4513);
    }

    .podium-avatar {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
        border: 4px solid white;
        box-shadow: var(--shadow-md);
    }

    .podium-item.first .podium-avatar {
        width: 100px;
        height: 100px;
        font-size: 2rem;
    }

    .podium-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .podium-item.first .podium-name {
        font-size: 1.5rem;
    }

    .podium-stats {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .podium-stat {
        text-align: center;
    }

    .podium-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .podium-stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .ranking-row {
        background: var(--bg-white);
        padding: 1.25rem 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 0.75rem;
        display: grid;
        grid-template-columns: 60px 1fr auto auto;
        align-items: center;
        gap: 1.5rem;
        transition: all var(--transition);
        border: 2px solid transparent;
    }

    .ranking-row:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-md);
        transform: translateX(5px);
    }

    .rank-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        text-align: center;
    }

    .rank-change {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        margin-left: 0.5rem;
    }

    .rank-change.up {
        background: rgba(74, 222, 128, 0.1);
        color: var(--accent-green);
    }

    .rank-change.down {
        background: rgba(255, 107, 107, 0.1);
        color: var(--accent-red);
    }

    .rank-change.same {
        background: var(--bg-light);
        color: var(--text-light);
    }

    .player-ranking-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .player-avatar-rank {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1rem;
    }

    .player-details-rank {
        flex: 1;
    }

    .player-name-rank {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .player-meta-rank {
        font-size: 0.75rem;
        color: var(--text-light);
        display: flex;
        gap: 1rem;
    }

    .stats-row-rank {
        display: flex;
        gap: 2rem;
        font-size: 0.875rem;
    }

    .stat-item-rank {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-value-rank {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--text-primary);
    }

    .stat-label-rank {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }

    .ranking-actions {
        display: flex;
        gap: 0.5rem;
    }

    .trophy-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .leaderboard-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .leaderboard-title {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .leaderboard-subtitle {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-top: 0.25rem;
    }

    .points-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 1.25rem;
        backdrop-filter: blur(10px);
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-overview-card {
        background: var(--bg-white);
        padding: 1.25rem;
        border-radius: var(--radius-lg);
        text-align: center;
        box-shadow: var(--shadow-sm);
    }

    .stat-overview-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .stat-overview-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .stat-overview-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .performance-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .performance-badge.excellent {
        background: rgba(74, 222, 128, 0.1);
        color: var(--accent-green);
    }

    .performance-badge.good {
        background: rgba(0, 217, 181, 0.1);
        color: var(--primary-color);
    }

    .performance-badge.average {
        background: rgba(255, 211, 61, 0.1);
        color: #ca8a04;
    }

    .pagination {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
        padding: 1rem;
    }

    .pagination-btn {
        padding: 0.5rem 0.75rem;
        border: 2px solid var(--border-color);
        background: white;
        border-radius: var(--radius-lg);
        cursor: pointer;
        font-weight: 600;
        transition: all var(--transition);
        font-size: 0.875rem;
    }

    .pagination-btn:hover:not(.active) {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Header -->
            <div class="top-header">
                <div class="header-left">
                    <h1>Bảng Xếp Hạng</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="{{route('homeyard.overview')}}" class="breadcrumb-link">Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Bảng Xếp Hạng</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Tìm kiếm VĐV...">
                    </div>
                    <div class="header-notifications">
                        <button class="notification-btn">
                            🔔
                            <span class="notification-badge">5</span>
                        </button>
                    </div>
                    <div class="header-user">
                        <div class="user-avatar">AD</div>
                        <div class="user-info">
                            <div class="user-name">Admin User</div>
                            <div class="user-role">Quản trị viên</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-overview fade-in" id="statsOverview">
                <!-- Stats will be loaded via JavaScript -->
            </div>

            <!-- Ranking Filters -->
            <div class="ranking-filters fade-in">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">Lọc
                        theo giải đấu:</label>
                    <select id="tournamentFilter" class="form-control"
                        style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: var(--radius-lg); font-size: 0.875rem;">
                        <option value="">-- Tất cả giải đấu --</option>
                        <!-- Options will be loaded via JavaScript -->
                    </select>
                </div>
                <div class="filter-buttons" id="rankingFilters">
                    <!-- Filter buttons will be generated by JavaScript -->
                </div>
            </div>

            <!-- Podium -->
            <div class="podium fade-in" id="podium">
                <!-- Top 3 will be loaded via JavaScript -->
            </div>

            <!-- Leaderboard -->
            <div class="card fade-in">
                <div class="leaderboard-header">
                    <div>
                        <div class="leaderboard-title" id="leaderboardTitle">🏆 Bảng Xếp Hạng Tổng Hợp</div>
                        <div class="leaderboard-subtitle" id="leaderboardSubtitle">Cập nhật lần cuối: --</div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-secondary btn-sm"
                            style="background: rgba(255,255,255,0.2); border: none; color: white;">
                            📥 Xuất Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Ranking Rows will be loaded via JavaScript -->
                </div>
                <div class="card-footer">
                    <div class="pagination" id="pagination">
                        <!-- Pagination will be loaded via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@section('js')
    <script>
        // Filter options configuration with category type mapping
        const rankingFilters = [{
                value: 'all',
                label: '🏆 Tổng Hợp',
                categoryType: null
            },
            {
                value: 'singles',
                label: '👤 Đơn Nam',
                categoryType: 'single_men'
            },
            {
                value: 'women',
                label: '👤 Đơn Nữ',
                categoryType: 'single_women'
            },
            {
                value: 'doubles',
                label: '👥 Đôi Nam',
                categoryType: 'double_men'
            },
            {
                value: 'women-doubles',
                label: '👥 Đôi Nữ',
                categoryType: 'double_women'
            },
            {
                value: 'mixed',
                label: '👫 Đôi Nam Nữ',
                categoryType: 'double_mixed'
            }
        ];

        // Map category type to category ID (will be loaded from API)
        let categoryTypeToIdMap = {};

        // Initialize filter buttons
        function initializeFilterButtons() {
            const container = document.getElementById('rankingFilters');
            if (!container) return;

            container.innerHTML = rankingFilters.map((filter, index) => `
                <button class="filter-btn ${index === 0 ? 'active' : ''}" data-filter="${filter.value}">
                    ${filter.label}
                </button>
            `).join('');

            // Attach event listeners using event delegation
            container.addEventListener('click', (e) => {
                if (e.target.classList.contains('filter-btn')) {
                    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                    const category = e.target.getAttribute('data-filter');
                    filterRanking(category);
                }
            });
        }

        // Load tournaments for filter and build category mapping
        async function loadTournaments() {
            try {
                const response = await fetch('/homeyard/tournaments-list', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('Tournaments data:', data);
                    const select = document.getElementById('tournamentFilter');

                    if (data.tournaments && Array.isArray(data.tournaments)) {
                        data.tournaments.forEach(tournament => {
                            const option = document.createElement('option');
                            option.value = tournament.id;
                            option.textContent = tournament.name;
                            select.appendChild(option);

                            // Build category type to ID map from tournament categories
                            if (tournament.categories && Array.isArray(tournament.categories)) {
                                console.log('Tournament categories:', tournament.categories);
                                tournament.categories.forEach(cat => {
                                    if (cat.category_type) {
                                        categoryTypeToIdMap[cat.category_type] = cat.id;
                                        console.log('Mapped', cat.category_type, 'to ID', cat.id);
                                    }
                                });
                            }
                        });
                        console.log('Final categoryTypeToIdMap:', categoryTypeToIdMap);
                    }
                }
            } catch (error) {
                console.error('Error loading tournaments:', error);
            }
        }

        // Filter by tournament
        let currentTournamentFilter = null; // Store current tournament filter

        document.addEventListener('DOMContentLoaded', () => {
            initializeFilterButtons(); // Initialize filter buttons
            loadTournaments();
            loadAllRankings(1); // Load all rankings on page load with page 1
            loadStats(); // Load stats on page load

            const tournamentFilter = document.getElementById('tournamentFilter');
            if (tournamentFilter) {
                tournamentFilter.addEventListener('change', function() {
                    const tournamentId = this.value;
                    currentTournamentFilter = tournamentId || null; // Store filter
                    currentCategory = 'all'; // Reset category filter
                    currentCategoryType = null;
                    updateFilterButtonsUI(); // Update UI

                    // Clear search when tournament changes
                    const searchInput = document.querySelector('.search-input');
                    if (searchInput) searchInput.value = '';

                    if (tournamentId) {
                        // Load rankings for selected tournament (reset to page 1)
                        loadRankingsByTournament(tournamentId, 1, null);
                        loadStats(tournamentId); // Load stats for this tournament
                    } else {
                        // Load all rankings
                        loadAllRankings(1, null);
                        loadStats(); // Load stats for all
                    }
                });
            }

            // Add search functionality
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    searchAthlete(e.target.value);
                });
            }
        });

        // Update filter buttons UI to show selected filter
        function updateFilterButtonsUI() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.getAttribute('data-filter') === currentCategory) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        // Store original rankings data for search
        let originalRankingsData = null;
        let lastRequestPage = 1;
        let lastRequestCategoryType = null;

        // Search athlete by name - loads all data from all pages
        function searchAthlete(searchTerm) {
            const rankingContainer = document.querySelector('.card-body');
            if (!rankingContainer) return;

            const term = searchTerm.toLowerCase().trim();
            console.log('Searching for:', term);

            // If search term is empty, reload the previous page
            if (!term) {
                if (currentTournamentFilter) {
                    loadRankingsByTournament(currentTournamentFilter, lastRequestPage, lastRequestCategoryType);
                } else {
                    loadAllRankings(lastRequestPage, lastRequestCategoryType);
                }
                return;
            }

            // Load all data (no pagination) for searching across all pages
            loadAllDataForSearch(term);
        }

        // Load all rankings data without pagination for search
        async function loadAllDataForSearch(searchTerm) {
            try {
                const rankingContainer = document.querySelector('.card-body');
                let allStandings = [];
                let url;
                let page = 1;
                let hasMore = true;

                // Fetch all pages
                while (hasMore) {
                    if (currentTournamentFilter) {
                        url = `/homeyard/tournaments/${currentTournamentFilter}/rankings?page=${page}&per_page=100`;
                        if (lastRequestCategoryType) {
                            // For specific tournament, convert category type to category_id
                            if (categoryTypeToIdMap[lastRequestCategoryType]) {
                                url += `&category_id=${categoryTypeToIdMap[lastRequestCategoryType]}`;
                            }
                        }
                    } else {
                        url = `/homeyard/tournaments/rankings-all?page=${page}&per_page=100`;
                        if (lastRequestCategoryType) {
                            url += `&category_type=${lastRequestCategoryType}`;
                        }
                    }

                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        let standings = data.standings || data.rankings || [];
                        allStandings = allStandings.concat(standings);

                        // Check if there are more pages
                        if (data.current_page && data.last_page && data.current_page >= data.last_page) {
                            hasMore = false;
                        } else if (!data.current_page) {
                            hasMore = false;
                        }

                        page++;
                    } else {
                        hasMore = false;
                    }
                }

                // Filter by search term
                let standings = allStandings.filter(standing => {
                    const athlete = standing.athlete || standing;
                    const athleteName = (athlete.athlete_name || '').toLowerCase();
                    return athleteName.includes(searchTerm.toLowerCase());
                });

                if (standings.length === 0) {
                    rankingContainer.innerHTML =
                        '<p style="padding: 2rem; text-align: center; color: var(--text-secondary);">Không tìm thấy VĐV nào phù hợp</p>';
                    return;
                }

                // Render filtered standings
                let html = '';
                standings.forEach((standing, index) => {
                    const athlete = standing.athlete || standing;
                    const matchesPlayed = standing.matches_played || 0;
                    const matchesWon = standing.matches_won || 0;
                    const winRate = standing.win_rate !== undefined ?
                        Math.round(standing.win_rate) :
                        (matchesPlayed > 0 ? Math.round((matchesWon / matchesPlayed) * 100) : 0);

                    let rankChange = '';
                    if (standing.rank_change > 0) {
                        rankChange = `<span class="rank-change up">↑${standing.rank_change}</span>`;
                    } else if (standing.rank_change < 0) {
                        rankChange = `<span class="rank-change down">↓${Math.abs(standing.rank_change)}</span>`;
                    } else {
                        rankChange = `<span class="rank-change same">—</span>`;
                    }

                    const athleteName = athlete.athlete_name || 'N/A';
                    const athleteEmail = athlete.email || 'N/A';
                    const athletePhone = athlete.phone || 'N/A';

                    const initials = athleteName !== 'N/A' ?
                        athleteName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) :
                        'N/A';

                    html += `
                     <div class="ranking-row">
                         <div>
                             <span class="rank-number">${index + 1}</span>
                            ${rankChange}
                         </div>
                         <div class="player-ranking-info">
                             <div class="player-avatar-rank">${initials}</div>
                             <div class="player-details-rank">
                                 <div class="player-name-rank">${athleteName}</div>
                                 <div class="player-meta-rank">
                                     <span>📧 ${athleteEmail}</span>
                                     <span>📱 ${athletePhone}</span>
                                 </div>
                             </div>
                         </div>
                         <div class="stats-row-rank">
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${standing.points || 0}</div>
                                 <div class="stat-label-rank">Điểm</div>
                             </div>
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${matchesPlayed}</div>
                                 <div class="stat-label-rank">Trận</div>
                             </div>
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${matchesWon}</div>
                                 <div class="stat-label-rank">Thắng</div>
                             </div>
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${standing.matches_lost || 0}</div>
                                 <div class="stat-label-rank">Thua</div>
                             </div>
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${winRate}%</div>
                                 <div class="stat-label-rank">Tỷ lệ</div>
                             </div>
                         </div>
                         <div class="ranking-actions">
                             <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                             <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                         </div>
                      </div>
                  `;
                });

                rankingContainer.innerHTML = html;
            } catch (error) {
                console.error('Error loading data for search:', error);
                const rankingContainer = document.querySelector('.card-body');
                if (rankingContainer) {
                    rankingContainer.innerHTML =
                        '<p style="padding: 2rem; text-align: center; color: var(--text-secondary);">Lỗi khi tìm kiếm</p>';
                }
            }
        }

        // Load tournament stats
        async function loadStats(tournamentId = null) {
            try {
                let url = '/homeyard/tournaments/stats';
                if (tournamentId) {
                    url += `?tournament_id=${tournamentId}`;
                }
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const stats = await response.json();
                    // If filtering by tournament, get total count from rankings endpoint
                    if (tournamentId && currentTournamentFilter) {
                        const rankingsUrl = `/homeyard/tournaments/${tournamentId}/rankings?page=1&per_page=1`;
                        const rankingsResponse = await fetch(rankingsUrl, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (rankingsResponse.ok) {
                            const rankingsData = await rankingsResponse.json();
                            // Get total from pagination data
                            let totalAthletes = 0;
                            if (rankingsData.pagination) {
                                totalAthletes = rankingsData.pagination.total;
                            } else if (rankingsData.total) {
                                totalAthletes = rankingsData.total;
                            }
                            if (totalAthletes > 0) {
                                stats.total_athletes = totalAthletes; // Override with actual count
                            }
                        }
                    }
                    updateStatsDisplay(stats);
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Update stats display
        function updateStatsDisplay(stats) {
            const html = `
                  <div class="stat-overview-card">
                      <div class="stat-overview-icon">👥</div>
                      <div class="stat-overview-value">${stats.total_athletes}</div>
                      <div class="stat-overview-label">Tổng VĐV</div>
                  </div>
                  <div class="stat-overview-card">
                      <div class="stat-overview-icon">🏆</div>
                      <div class="stat-overview-value">${stats.total_athletes}</div>
                      <div class="stat-overview-label">VĐV Xếp Hạng</div>
                  </div>
                  <div class="stat-overview-card">
                      <div class="stat-overview-icon">🎯</div>
                      <div class="stat-overview-value">${stats.total_matches}</div>
                      <div class="stat-overview-label">Trận Đã Thi Đấu</div>
                  </div>
                  <div class="stat-overview-card">
                      <div class="stat-overview-icon">📊</div>
                      <div class="stat-overview-value">${stats.avg_win_rate}%</div>
                      <div class="stat-overview-label">Tỷ Lệ Thắng TB</div>
                  </div>
              `;
            const statsContainer = document.getElementById('statsOverview');
            if (statsContainer) {
                statsContainer.innerHTML = html;
            }
        }

        // Update rankings display
        function updateRankingsDisplay(data) {
            const rankingContainer = document.querySelector('.card-body');
            if (!rankingContainer) return;

            // Save original data for search functionality
            originalRankingsData = data;
            lastRequestPage = data.current_page || 1;
            lastRequestCategoryType = currentCategoryType;

            let standings = data.standings || data.rankings || [];

            // Handle pagination metadata from both formats
            if (!data.current_page && data.pagination) {
                data.current_page = data.pagination.current_page;
                data.per_page = data.pagination.per_page;
                data.total = data.pagination.total;
                data.last_page = data.pagination.total_pages;
            }

            if (!standings || standings.length === 0) {
                rankingContainer.innerHTML =
                    '<p style="padding: 2rem; text-align: center; color: var(--text-secondary);">Không có dữ liệu xếp hạng</p>';
                // Clear podium too
                const podium = document.getElementById('podium');
                if (podium) podium.innerHTML = '';
                return;
            }

            // Update podium (top 3) - only on page 1
            if (parseInt(data.current_page) === 1 || !data.current_page) {
                updatePodium(standings);
            } else {
                const podium = document.getElementById('podium');
                if (podium) podium.innerHTML = '';
            }

            // Update leaderboard header
            const now = new Date();
            const timeStr = now.toLocaleString('vi-VN');
            document.getElementById('leaderboardSubtitle').textContent = `Cập nhật lần cuối: ${timeStr}`;

            let html = '';
            standings.forEach((standing, index) => {
                // Handle both direct athlete object and nested athlete property
                const athlete = standing.athlete || standing;
                const matchesPlayed = standing.matches_played || 0;
                const matchesWon = standing.matches_won || 0;
                const winRate = standing.win_rate !== undefined ?
                    Math.round(standing.win_rate) :
                    (matchesPlayed > 0 ? Math.round((matchesWon / matchesPlayed) * 100) : 0);

                // Calculate actual rank based on page and per_page (only if pagination data exists)
                let actualRank = index + 1;
                if (data.current_page && data.per_page) {
                    actualRank = (data.current_page - 1) * data.per_page + index + 1;
                }

                // Determine rank change
                let rankChange = '';
                if (standing.rank_change > 0) {
                    rankChange = `<span class="rank-change up">↑${standing.rank_change}</span>`;
                } else if (standing.rank_change < 0) {
                    rankChange = `<span class="rank-change down">↓${Math.abs(standing.rank_change)}</span>`;
                } else {
                    rankChange = `<span class="rank-change same">—</span>`;
                }

                // Get athlete name and email
                const athleteName = athlete.athlete_name || 'N/A';
                const athleteEmail = athlete.email || 'N/A';
                const athletePhone = athlete.phone || 'N/A';

                // Get initials for avatar
                const initials = athleteName !== 'N/A' ?
                    athleteName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) :
                    'N/A';

                html += `
                      <div class="ranking-row">
                          <div>
                              <span class="rank-number">${actualRank}</span>
                             ${rankChange}
                         </div>
                         <div class="player-ranking-info">
                             <div class="player-avatar-rank">${initials}</div>
                             <div class="player-details-rank">
                                 <div class="player-name-rank">${athleteName}</div>
                                 <div class="player-meta-rank">
                                     <span>📧 ${athleteEmail}</span>
                                     <span>📱 ${athletePhone}</span>
                                 </div>
                             </div>
                         </div>
                         <div class="stats-row-rank">
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${standing.points || 0}</div>
                                 <div class="stat-label-rank">Điểm</div>
                             </div>
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${matchesPlayed}</div>
                                 <div class="stat-label-rank">Trận</div>
                             </div>
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${matchesWon}</div>
                                 <div class="stat-label-rank">Thắng</div>
                             </div>
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${standing.matches_lost || 0}</div>
                                 <div class="stat-label-rank">Thua</div>
                             </div>
                             <div class="stat-item-rank">
                                 <div class="stat-value-rank">${winRate}%</div>
                                 <div class="stat-label-rank">Tỷ lệ</div>
                             </div>
                         </div>
                         <div class="ranking-actions">
                             <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                             <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                         </div>
                     </div>
                 `;
            });

            rankingContainer.innerHTML = html;

            // Update pagination
            const pagination = document.getElementById('pagination');
            if (pagination && data.last_page > 1) {
                let paginationHtml = '';
                const maxPages = 5;
                const currentPage = parseInt(data.current_page) || 1;
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(data.last_page, startPage + maxPages - 1);

                if (endPage - startPage < maxPages - 1) {
                    startPage = Math.max(1, endPage - maxPages + 1);
                }

                // Previous button
                if (currentPage > 1) {
                    if (currentTournamentFilter) {
                        paginationHtml +=
                            `<button class="pagination-btn" onclick="loadRankingsByTournament('${currentTournamentFilter}', ${currentPage - 1}, ${currentCategoryType ? `'${currentCategoryType}'` : 'null'})">← Trước</button>`;
                    } else {
                        paginationHtml +=
                            `<button class="pagination-btn" onclick="loadAllRankings(${currentPage - 1}, ${currentCategoryType ? `'${currentCategoryType}'` : 'null'})">← Trước</button>`;
                    }
                }

                // Page buttons
                for (let i = startPage; i <= endPage; i++) {
                    if (i === currentPage) {
                        paginationHtml += `<button class="pagination-btn active">${i}</button>`;
                    } else {
                        if (currentTournamentFilter) {
                            paginationHtml +=
                                `<button class="pagination-btn" onclick="loadRankingsByTournament('${currentTournamentFilter}', ${i}, ${currentCategoryType ? `'${currentCategoryType}'` : 'null'})">${i}</button>`;
                        } else {
                            paginationHtml +=
                                `<button class="pagination-btn" onclick="loadAllRankings(${i}, ${currentCategoryType ? `'${currentCategoryType}'` : 'null'})">${i}</button>`;
                        }
                    }
                }

                // Next button
                if (currentPage < data.last_page) {
                    if (currentTournamentFilter) {
                        paginationHtml +=
                            `<button class="pagination-btn" onclick="loadRankingsByTournament('${currentTournamentFilter}', ${currentPage + 1}, ${currentCategoryType ? `'${currentCategoryType}'` : 'null'})">Sau →</button>`;
                    } else {
                        paginationHtml +=
                            `<button class="pagination-btn" onclick="loadAllRankings(${currentPage + 1}, ${currentCategoryType ? `'${currentCategoryType}'` : 'null'})">Sau →</button>`;
                    }
                }

                pagination.innerHTML = paginationHtml;
            } else if (pagination) {
                pagination.innerHTML = '';
            }
        }

        // Update podium (top 3)
        function updatePodium(standings) {
            const podiumContainer = document.getElementById('podium');
            if (!podiumContainer || standings.length === 0) return;

            const medals = ['🥇', '🥈', '🥉'];
            const positions = ['first', 'second', 'third'];
            let html = '';

            for (let i = 0; i < 3 && i < standings.length; i++) {
                const standing = standings[i];
                const athlete = standing.athlete || standing;
                const matchesPlayed = standing.matches_played || 0;
                const matchesWon = standing.matches_won || 0;
                const winRate = matchesPlayed > 0 ? Math.round((matchesWon / matchesPlayed) * 100) : 0;

                const initials = (athlete.athlete_name || 'N/A')
                    .split(' ')
                    .map(n => n[0])
                    .join('')
                    .toUpperCase()
                    .substring(0, 2);

                html += `
                     <div class="podium-item ${positions[i]}">
                         <div class="podium-medal">${medals[i]}</div>
                         <div class="podium-avatar">${initials}</div>
                         <div class="podium-name">${athlete.athlete_name || 'N/A'}</div>
                         <div class="podium-stats">
                             <div class="podium-stat">
                                 <div class="podium-stat-value">${standing.points || 0}</div>
                                 <div class="podium-stat-label">Điểm</div>
                             </div>
                             <div class="podium-stat">
                                 <div class="podium-stat-value">${matchesWon}</div>
                                 <div class="podium-stat-label">Thắng</div>
                             </div>
                             <div class="podium-stat">
                                 <div class="podium-stat-value">${winRate}%</div>
                                 <div class="podium-stat-label">Tỷ lệ</div>
                             </div>
                         </div>
                     </div>
                 `;
            }

            podiumContainer.innerHTML = html;
        }

        // Store current data for filtering
        let allRankingsData = null;
        let currentCategory = 'all';
        let currentCategoryType = null;

        // Filter ranking by category
        function filterRanking(category) {
            console.log('Filter clicked:', category);

            currentCategory = category;

            // Find the category type for the selected filter
            const filterConfig = rankingFilters.find(f => f.value === category);
            currentCategoryType = filterConfig ? filterConfig.categoryType : null;

            console.log('Category config:', filterConfig);
            console.log('Current category type:', currentCategoryType);

            // Load appropriate endpoint based on tournament selection
            if (currentTournamentFilter) {
                // Load from specific tournament endpoint with category_id
                console.log('Loading by tournament:', currentTournamentFilter);
                loadRankingsByTournament(currentTournamentFilter, 1, currentCategoryType);
            } else {
                // Load from all tournaments endpoint with category_type filter
                console.log('Loading all rankings with category filter');
                loadAllRankings(1, currentCategoryType);
            }
        }

        // Update loadAllRankings to accept category parameter
        async function loadAllRankings(page = 1, categoryType = null) {
            try {
                let url = `/homeyard/tournaments/rankings-all?page=${page}&per_page=10`;

                // Add category type filter if provided
                if (categoryType) {
                    url += `&category_type=${categoryType}`;
                }

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    allRankingsData = data;
                    updateRankingsDisplay(data);
                    loadStats(); // Load stats
                }
            } catch (error) {
                console.error('Error loading all rankings:', error);
            }
        }

        // Update loadRankingsByTournament to accept category parameter
        async function loadRankingsByTournament(tournamentId, page = 1, categoryType = null) {
            try {
                let url = `/homeyard/tournaments/${tournamentId}/rankings?page=${page}&per_page=10`;

                // Convert category type to category ID if provided
                if (categoryType && categoryTypeToIdMap[categoryType]) {
                    const categoryId = categoryTypeToIdMap[categoryType];
                    url += `&category_id=${categoryId}`;
                }

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    allRankingsData = data;
                    updateRankingsDisplay(data);
                }
            } catch (error) {
                console.error('Error loading rankings:', error);
            }
        }
    </script>
@endsection
