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
                            <a href="overview.html" class="breadcrumb-link">Trang chủ</a>
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
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterRanking('all')">🏆 Tổng Hợp</button>
                    <button class="filter-btn" onclick="filterRanking('singles')">👤 Đơn Nam</button>
                    <button class="filter-btn" onclick="filterRanking('women')">👤 Đơn Nữ</button>
                    <button class="filter-btn" onclick="filterRanking('doubles')">👥 Đôi Nam</button>
                    <button class="filter-btn" onclick="filterRanking('women-doubles')">👥 Đôi Nữ</button>
                    <button class="filter-btn" onclick="filterRanking('mixed')">👫 Đôi Nam Nữ</button>
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
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }

        // Mobile menu toggle
        if (window.innerWidth <= 1024) {
            toggleSidebar();
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('mainContent').classList.add('sidebar-collapsed');
            }
        });

        // Load tournaments for filter
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
                    const select = document.getElementById('tournamentFilter');

                    if (data.tournaments && Array.isArray(data.tournaments)) {
                        data.tournaments.forEach(tournament => {
                            const option = document.createElement('option');
                            option.value = tournament.id;
                            option.textContent = tournament.name;
                            select.appendChild(option);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading tournaments:', error);
            }
        }

        // Filter by tournament
        let currentTournamentFilter = null; // Store current tournament filter
        
        document.addEventListener('DOMContentLoaded', () => {
             loadTournaments();
             loadAllRankings(1); // Load all rankings on page load with page 1
             loadStats(); // Load stats on page load

             const tournamentFilter = document.getElementById('tournamentFilter');
             if (tournamentFilter) {
                 tournamentFilter.addEventListener('change', function() {
                     const tournamentId = this.value;
                     currentTournamentFilter = tournamentId || null; // Store filter
                     if (tournamentId) {
                         // Load rankings for selected tournament (reset to page 1)
                         loadRankingsByTournament(tournamentId, 1);
                         loadStats(tournamentId); // Load stats for this tournament
                     } else {
                         // Load all rankings
                         loadAllRankings(1);
                         loadStats(); // Load stats for all
                     }
                 });
             }
         });

        // Load rankings by tournament
         async function loadRankingsByTournament(tournamentId, page = 1) {
             try {
                 const response = await fetch(`/homeyard/tournaments/${tournamentId}/rankings?page=${page}&per_page=10`, {
                     method: 'GET',
                     headers: {
                         'Accept': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest'
                     }
                 });

                 if (response.ok) {
                     const data = await response.json();
                     updateRankingsDisplay(data);
                 }
             } catch (error) {
                 console.error('Error loading rankings:', error);
             }
         }

        // Load all rankings
         async function loadAllRankings(page = 1) {
             try {
                 const response = await fetch(`/homeyard/tournaments/rankings-all?page=${page}&per_page=10`, {
                     method: 'GET',
                     headers: {
                         'Accept': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest'
                     }
                 });

                 if (response.ok) {
                     const data = await response.json();
                     updateRankingsDisplay(data);
                     loadStats(); // Load stats
                 }
             } catch (error) {
                 console.error('Error loading all rankings:', error);
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
                        paginationHtml += `<button class="pagination-btn" onclick="loadRankingsByTournament('${currentTournamentFilter}', ${currentPage - 1})">← Trước</button>`;
                    } else {
                        paginationHtml += `<button class="pagination-btn" onclick="loadAllRankings(${currentPage - 1})">← Trước</button>`;
                    }
                }
                
                // Page buttons
                for (let i = startPage; i <= endPage; i++) {
                    if (i === currentPage) {
                        paginationHtml += `<button class="pagination-btn active">${i}</button>`;
                    } else {
                        if (currentTournamentFilter) {
                            paginationHtml += `<button class="pagination-btn" onclick="loadRankingsByTournament('${currentTournamentFilter}', ${i})">${i}</button>`;
                        } else {
                            paginationHtml += `<button class="pagination-btn" onclick="loadAllRankings(${i})">${i}</button>`;
                        }
                    }
                }
                
                // Next button
                if (currentPage < data.last_page) {
                    if (currentTournamentFilter) {
                        paginationHtml += `<button class="pagination-btn" onclick="loadRankingsByTournament('${currentTournamentFilter}', ${currentPage + 1})">Sau →</button>`;
                    } else {
                        paginationHtml += `<button class="pagination-btn" onclick="loadAllRankings(${currentPage + 1})">Sau →</button>`;
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

        // Filter ranking by category
        function filterRanking(category) {
            // Remove active class from all buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            event.target.classList.add('active');

            // In a real app, this would filter the ranking data
            console.log('Filtering by:', category);
        }
    </script>
@endsection
