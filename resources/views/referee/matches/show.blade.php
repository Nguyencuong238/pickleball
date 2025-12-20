<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Điều Hành Trận Đấu - onePickleball</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="{{ asset('css/referee.css') }}">

    <style>
        .completed-results {
            width: 50%;
            margin-top: 10px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="bg-pattern"></div>

        <div class="app-container">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="btn-back" @click="goBack">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M15 18l-6-6 6-6"/>
                        </svg>
                        Quay lại
                    </button>
                    <div class="logo">
                        <div class="logo-icon">🏓</div>
                        <span class="logo-text">onePickleball</span>
                    </div>
                </div>

                <div class="header-center">
                    <div class="match-timer-box">
                        <div class="timer-label">Thời gian</div>
                        <div class="timer-value">@{{ timerDisplay }}</div>
                    </div>
                    <div class="match-info">
                        <div class="status-badge">
                            <span class="status-dot" :class="{ live: status === 'playing' }"></span>
                            <span class="status-text">@{{ statusText }}</span>
                        </div>
                        <span class="game-badge">Game <strong>@{{ currentGame }}</strong> / @{{ totalGames }}</span>
                        <div class="game-score-display">
                            <span class="game-score-item left">@{{ teams.left.gamesWon }}</span>
                            <span class="game-score-separator">-</span>
                            <span class="game-score-item right">@{{ teams.right.gamesWon }}</span>
                        </div>
                    </div>
                </div>

                <div class="header-right">
                    <div class="game-mode-switch">
                        <button
                            class="mode-btn"
                            :class="{ active: gameMode === 'singles' }"
                            :disabled="true"
                            v-if="gameMode === 'singles'"
                        >
                            Đơn
                        </button>
                        <button
                            class="mode-btn"
                            :class="{ active: gameMode === 'doubles' }"
                            :disabled="true"
                            v-if="gameMode === 'doubles'"
                        >
                            Đôi
                        </button>
                    </div>
                    <div class="referee-info-header">
                        <div class="referee-avatar-sm">👨‍⚖️</div>
                        <div class="referee-details">
                            <div class="referee-name-sm">@{{ MATCH_DATA.referee.name }}</div>
                            <div class="referee-role">Trọng tài - @{{ MATCH_DATA.referee.level }}</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Completed Match Results -->
            <div v-if="isMatchCompleted" class="completed-results" style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-card); border-radius: var(--radius-lg); border: 2px solid var(--success);">
                <h3 style="color: var(--success); margin-bottom: 1rem; text-align: center;">🏆 TRẬN ĐẤU ĐÃ KẾT THÚC</h3>
                <div style="text-align: center; margin-bottom: 1rem;">
                    <div style="margin-bottom: 16px;">
                        <div class="tournament-name">@{{ MATCH_DATA.tournament.name }}</div>
                    <div class="tournament-round"><span>@{{ MATCH_DATA.round.name }}</span> - @{{ gameMode === 'singles' ? 'Đơn' : 'Đôi' }}</div>
                    </div>
                    <div style="font-size: 1.5rem; color: var(--text-white); margin-bottom: 0.5rem;">
                        Người thắng: <strong style="color: var(--accent);">@{{ matchWinnerName }}</strong>
                    </div>
                    <div style="font-size: 1.25rem; color: var(--text-light);">
                        Tỉ số: @{{ MATCH_DATA.setScores?.map(s => s.athlete1 + '-' + s.athlete2).join(', ') || 'N/A' }}
                    </div>
                    
                </div>
            </div>
            <!-- Main Content -->
            <div class="main-content" v-else>
                <!-- Left Panel - Scoreboard -->
                <div class="scoreboard-panel">
                    <!-- Score Call Display -->
                    <div class="score-call-bar">
                        <span class="score-call-label">Score Call:</span>
                        <span class="score-call-value">@{{ scoreCall }}</span>
                    </div>

                    <!-- Court Info Bar -->
                    <div class="court-info-bar">
                        <div class="court-display">
                            <div class="court-icon-lg">🏟️</div>
                            <div class="court-text">
                                <span class="court-label">Sân thi đấu</span>
                                <span class="court-number">@{{ MATCH_DATA.court.number }}</span>
                            </div>
                        </div>
                        <div class="court-divider"></div>
                        <div class="tournament-info">
                            <div class="tournament-name">@{{ MATCH_DATA.tournament.name }}</div>
                            <div class="tournament-round"><span>@{{ MATCH_DATA.round.name }}</span> - @{{ gameMode === 'singles' ? 'Đơn' : 'Đôi' }}</div>
                        </div>
                    </div>

                    <!-- Main Scoreboard -->
                    <div class="scoreboard-main">
                        <!-- Team Left (Blue) -->
                        <div class="team-card blue" :class="{ serving: serving.team === 'left' }">
                            <div class="serving-indicator" v-if="serving.team === 'left'">
                                <span>🎾</span> Đang giao bóng
                            </div>

                            <div class="team-header">
                                <div class="team-avatar">🔵</div>
                                <div class="team-info">
                                    <div class="team-name">@{{ teams.left.name }}</div>
                                </div>
                            </div>

                            <div class="team-score-section">
                                <div class="team-score">@{{ teams.left.score }}</div>
                                <div class="score-label">Điểm số</div>
                                <div class="server-number-display" v-if="serving.team === 'left' && gameMode === 'doubles'">
                                    <span class="server-label">Server</span>
                                    <span class="server-value">@{{ serving.serverNumber }}</span>
                                </div>
                            </div>

                            <div class="players-section" v-if="gameMode === 'doubles'">
                                <div class="players-title">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    Thành viên
                                </div>
                                <div class="players-grid">
                                    <div
                                        v-for="(player, index) in teams.left.players"
                                        :key="index"
                                        class="player-card"
                                        :class="{ active: serving.team === 'left' && serving.serverIndex === index }"
                                    >
                                        <div class="player-status"></div>
                                        <div class="player-details">
                                            <div class="player-name">@{{ player.name }}</div>
                                            <div class="player-position">Vị trí: @{{ player.courtSide === 'right' ? 'Bên Phải (Chẵn)' : 'Bên Trái (Lẻ)' }}</div>
                                        </div>
                                        <span class="player-tag">Server @{{ index + 1 }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="players-section" v-else>
                                <div class="players-title">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    VĐV
                                </div>
                                <div class="player-card" :class="{ active: serving.team === 'left' }">
                                    <div class="player-status"></div>
                                    <div class="player-details">
                                        <div class="player-name">@{{ teams.left.players[0]?.name }}</div>
                                        <div class="player-position">Vị trí: @{{ servingCourtSide === 'right' ? 'Bên Phải' : 'Bên Trái' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="score-controls" v-if="!isMatchCompleted">
                                <button class="btn-score add" @click="rallyWon('left')" :disabled="status !== 'playing'">
                                    <span>+</span> Thắng rally
                                </button>
                                <button class="btn-score subtract" @click="adjustScore('left', -1)" :disabled="status !== 'playing'">
                                    <span>-</span> Trừ điểm
                                </button>
                            </div>
                        </div>

                        <!-- VS Center -->
                        <div class="vs-center">
                            <div class="vs-line"></div>
                            <div class="vs-badge">VS</div>
                            <button class="vs-switch-btn" @click="switchSides" :disabled="status !== 'playing'">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <polyline points="17 1 21 5 17 9"/>
                                    <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                                    <polyline points="7 23 3 19 7 15"/>
                                    <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                                </svg>
                                Đổi sân
                            </button>
                            <div class="vs-line"></div>
                        </div>

                        <!-- Team Right (Red) -->
                        <div class="team-card red" :class="{ serving: serving.team === 'right' }">
                            <div class="serving-indicator" v-if="serving.team === 'right'">
                                <span>🎾</span> Đang giao bóng
                            </div>

                            <div class="team-header">
                                <div class="team-avatar">🔴</div>
                                <div class="team-info">
                                    <div class="team-name">@{{ teams.right.name }}</div>
                                </div>
                            </div>

                            <div class="team-score-section">
                                <div class="team-score">@{{ teams.right.score }}</div>
                                <div class="score-label">Điểm số</div>
                                <div class="server-number-display" v-if="serving.team === 'right' && gameMode === 'doubles'">
                                    <span class="server-label">Server</span>
                                    <span class="server-value">@{{ serving.serverNumber }}</span>
                                </div>
                            </div>

                            <div class="players-section" v-if="gameMode === 'doubles'">
                                <div class="players-title">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    Thành viên
                                </div>
                                <div class="players-grid">
                                    <div
                                        v-for="(player, index) in teams.right.players"
                                        :key="index"
                                        class="player-card"
                                        :class="{ active: serving.team === 'right' && serving.serverIndex === index }"
                                    >
                                        <div class="player-status"></div>
                                        <div class="player-details">
                                            <div class="player-name">@{{ player.name }}</div>
                                            <div class="player-position">Vị trí: @{{ player.courtSide === 'right' ? 'Bên Phải (Chẵn)' : 'Bên Trái (Lẻ)' }}</div>
                                        </div>
                                        <span class="player-tag">Server @{{ index + 1 }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="players-section" v-else>
                                <div class="players-title">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    VĐV
                                </div>
                                <div class="player-card" :class="{ active: serving.team === 'right' }">
                                    <div class="player-status"></div>
                                    <div class="player-details">
                                        <div class="player-name">@{{ teams.right.players[0]?.name }}</div>
                                        <div class="player-position">Vị trí: @{{ servingCourtSide === 'right' ? 'Bên Phải' : 'Bên Trái' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="score-controls" v-if="!isMatchCompleted">
                                <button class="btn-score add" @click="rallyWon('right')" :disabled="status !== 'playing'">
                                    <span>+</span> Thắng rally
                                </button>
                                <button class="btn-score subtract" @click="adjustScore('right', -1)" :disabled="status !== 'playing'">
                                    <span>-</span> Trừ điểm
                                </button>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- Right Panel - Controls -->
                <div class="control-panel" v-if="!isMatchCompleted">
                    <!-- Game Controls -->
                    <div class="control-card">
                        <div class="control-card-header">
                            <span class="control-card-title">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/>
                                </svg>
                                Điều khiển trận đấu
                            </span>
                        </div>
                        <div class="control-card-body">
                            <div class="game-controls-grid">
                                <button class="btn-game coin" @click="showCoinFlip">
                                    <svg class="icon icon-lg" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10"/>
                                        <path d="M2 12h20"/>
                                    </svg>
                                    Bốc Thăm
                                </button>
                                <button class="btn-game pause" @click="pauseMatch" :disabled="status === 'waiting'">
                                    <svg class="icon icon-lg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                        <rect x="6" y="4" width="4" height="16" rx="1"/>
                                        <rect x="14" y="4" width="4" height="16" rx="1"/>
                                    </svg>
                                    @{{ status === 'paused' ? 'Tiếp tục' : 'Tạm Dừng' }}
                                </button>
                                <button
                                    class="btn-game start"
                                    :class="{ running: status === 'playing' }"
                                    @click="toggleMatch"
                                >
                                    <svg class="icon icon-lg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                        <polygon points="5 3 19 12 5 21 5 3"/>
                                    </svg>
                                    @{{ status === 'waiting' ? 'Bắt Đầu Trận Đấu' : 'Đang Diễn Ra' }}
                                </button>
                                <button class="btn-game end" @click="endGame" :disabled="status === 'waiting'">
                                    <svg class="icon icon-lg" viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <rect x="8" y="8" width="8" height="8" rx="1" fill="currentColor"/>
                                    </svg>
                                    Kết Thúc
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="control-card">
                        <div class="control-card-header">
                            <span class="control-card-title">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                </svg>
                                Thao tác nhanh
                            </span>
                        </div>
                        <div class="control-card-body">
                            <div class="quick-actions-grid">
                                <button class="btn-quick undo" @click="undo" :disabled="history.length === 0">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <path d="M3 7v6h6"/>
                                        <path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>
                                    </svg>
                                    Hoàn Tác
                                </button>
                                <button class="btn-quick fault" @click="recordFault" :disabled="status !== 'playing'">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                    Lỗi Giao
                                </button>
                                <button class="btn-quick" @click="manualSwitchServer" :disabled="status !== 'playing' || gameMode === 'singles'">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <polyline points="17 1 21 5 17 9"/>
                                        <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                                        <polyline points="7 23 3 19 7 15"/>
                                        <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                                    </svg>
                                    Đổi Server
                                </button>
                                <button class="btn-quick" @click="requestTimeout" :disabled="status !== 'playing'">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    Timeout
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- History -->
                    <div class="control-card" style="flex: 1;">
                        <div class="control-card-header">
                            <span class="control-card-title">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                Lịch sử trận đấu
                            </span>
                            <span class="history-clear" @click="clearEventLog">Xóa tất cả</span>
                        </div>
                        <div class="control-card-body">
                            <div class="history-list">
                                <div class="history-item" v-for="(event, index) in eventLog" :key="index">
                                    <span class="history-time">@{{ event.time }}</span>
                                    <span class="history-event">@{{ event.message }}</span>
                                    <span class="history-score">@{{ event.score }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coin Flip Modal -->
        <div class="modal-overlay" v-if="activeModal === 'coinFlip'" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-icon">🎲</div>
                    <div class="modal-title">Bốc Thăm Chia Sân</div>
                    <div class="modal-subtitle">Nhấn vào đồng xu để bốc thăm ngẫu nhiên</div>
                </div>
                <div class="modal-body">
                    <div class="coin-container">
                        <div class="coin-3d">
                            <div
                                class="coin-inner"
                                :class="{ flipping: coinFlipping }"
                                :style="coinStyle"
                                @click="flipCoin"
                            >
                                <div class="coin-side coin-front">🔵</div>
                                <div class="coin-side coin-back">🔴</div>
                            </div>
                        </div>
                        <div class="coin-result-text" v-if="coinResult">@{{ coinResult }}</div>
                        <div class="coin-hint" v-else-if="!coinFlipping">👆 Nhấn vào đồng xu để bốc thăm</div>
                        <div class="coin-hint" v-else>🎲 Đang bốc thăm...</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-modal secondary" @click="closeModal" :disabled="coinFlipping">Đóng</button>
                    <button class="btn-modal primary" @click="confirmCoinFlip" :disabled="!coinResult || coinFlipping">Tiếp Tục</button>
                </div>
            </div>
        </div>

        <!-- Team Assignment Modal -->
        <div class="modal-overlay" v-if="activeModal === 'teamAssign'" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-icon">📍</div>
                    <div class="modal-title">Phân Chia Vị Trí Sân</div>
                    <div class="modal-subtitle">Chọn đội sẽ đứng ở vị trí bên TRÁI</div>
                </div>
                <div class="modal-body">
                    <div class="team-options">
                        <div
                            class="team-option"
                            :class="{ selected: selectedLeftTeam === 'left' }"
                            @click="selectedLeftTeam = 'left'"
                        >
                            <div class="team-option-indicator blue"></div>
                            <div class="team-option-content">
                                <div class="team-option-name">🔵 @{{ teams.left.name }}</div>
                                <div class="team-option-players">@{{ getPlayersString('left') }}</div>
                            </div>
                            <div class="team-option-radio">
                                <svg v-if="selectedLeftTeam === 'left'" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" style="width:16px;height:16px">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>
                        </div>
                        <div
                            class="team-option"
                            :class="{ selected: selectedLeftTeam === 'right' }"
                            @click="selectedLeftTeam = 'right'"
                        >
                            <div class="team-option-indicator red"></div>
                            <div class="team-option-content">
                                <div class="team-option-name">🔴 @{{ teams.right.name }}</div>
                                <div class="team-option-players">@{{ getPlayersString('right') }}</div>
                            </div>
                            <div class="team-option-radio">
                                <svg v-if="selectedLeftTeam === 'right'" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" style="width:16px;height:16px">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-modal secondary" @click="activeModal = 'coinFlip'">Quay Lại</button>
                    <button class="btn-modal primary" @click="confirmTeamAssignment">Xác Nhận</button>
                </div>
            </div>
        </div>

        <!-- Serve Order Modal -->
        <div class="modal-overlay" v-if="activeModal === 'serveOrder'" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-icon">🎾</div>
                    <div class="modal-title">Xác Định Người Giao Bóng</div>
                    <div class="modal-subtitle">@{{ teams[serving.team].name }} được quyền giao bóng trước</div>
                </div>
                <div class="modal-body">
                    <div class="serve-section" v-if="gameMode === 'doubles'">
                        <div class="serve-label">✋ Chọn người giao bóng đầu tiên</div>
                        <div
                            v-for="(player, index) in teams[serving.team].players"
                            :key="index"
                            class="player-option"
                            :class="{ selected: selectedServerIndex === index }"
                            @click="selectedServerIndex = index"
                        >
                            <div class="player-option-radio"></div>
                            <span class="player-option-name">@{{ player.name }}</span>
                        </div>
                    </div>
                    <div class="serve-section" v-else>
                        <div class="serve-label">Người giao bóng</div>
                        <div class="player-option selected">
                            <div class="player-option-radio"></div>
                            <span class="player-option-name">@{{ teams[serving.team].players[0]?.name }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-modal secondary" @click="activeModal = 'teamAssign'">Quay Lại</button>
                    <button class="btn-modal primary" @click="confirmServeOrder">▶️ Bắt Đầu Trận Đấu</button>
                </div>
            </div>
        </div>

        <!-- Timeout Modal -->
        <div class="modal-overlay" v-if="activeModal === 'timeout'" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-icon">⏱️</div>
                    <div class="modal-title">Gọi Timeout</div>
                    <div class="modal-subtitle">Chọn đội muốn gọi timeout (mỗi đội 2 lần/game)</div>
                </div>
                <div class="modal-body">
                    <div class="team-options">
                        <div
                            class="team-option"
                            :class="{ disabled: timeout.leftRemaining <= 0 }"
                            @click="timeout.leftRemaining > 0 && startTimeout('left')"
                        >
                            <div class="team-option-indicator blue"></div>
                            <div class="team-option-content">
                                <div class="team-option-name">🔵 @{{ teams.left.name }}</div>
                                <div class="team-option-players">Còn @{{ timeout.leftRemaining }} timeout</div>
                            </div>
                            <div class="team-option-radio" v-if="timeout.leftRemaining > 0">
                                <span style="font-size: 20px;">⏱️</span>
                            </div>
                            <div class="team-option-radio" v-else>
                                <span style="font-size: 16px; color: var(--danger);">Hết</span>
                            </div>
                        </div>
                        <div
                            class="team-option"
                            :class="{ disabled: timeout.rightRemaining <= 0 }"
                            @click="timeout.rightRemaining > 0 && startTimeout('right')"
                        >
                            <div class="team-option-indicator red"></div>
                            <div class="team-option-content">
                                <div class="team-option-name">🔴 @{{ teams.right.name }}</div>
                                <div class="team-option-players">Còn @{{ timeout.rightRemaining }} timeout</div>
                            </div>
                            <div class="team-option-radio" v-if="timeout.rightRemaining > 0">
                                <span style="font-size: 20px;">⏱️</span>
                            </div>
                            <div class="team-option-radio" v-else>
                                <span style="font-size: 16px; color: var(--danger);">Hết</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-modal secondary" @click="closeModal">Hủy</button>
                </div>
            </div>
        </div>

        <!-- Timeout Countdown Overlay -->
        <div class="modal-overlay" v-if="timeout.active" style="background: rgba(6, 13, 24, 0.98);">
            <div class="modal-content" style="text-align: center;">
                <div class="modal-header">
                    <div class="modal-icon">⏱️</div>
                    <div class="modal-title">TIMEOUT</div>
                    <div class="modal-subtitle">@{{ teams[timeout.team]?.name }}</div>
                </div>
                <div class="modal-body">
                    <div style="font-family: 'Space Mono', monospace; font-size: 80px; font-weight: 700; color: var(--accent); margin: 20px 0;">
                        @{{ timeoutDisplay }}
                    </div>
                    <div style="font-size: 14px; color: var(--text-muted);">
                        Còn @{{ timeout.team === 'left' ? timeout.leftRemaining : timeout.rightRemaining }} timeout
                    </div>
                </div>
                <div class="modal-footer" style="justify-content: center;">
                    <button class="btn-modal primary" @click="endTimeout">▶️ Tiếp Tục Trận Đấu</button>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <div class="toast" :class="{ show: toast.show }">
            <span class="toast-icon">@{{ toast.icon }}</span>
            <span class="toast-text">@{{ toast.message }}</span>
        </div>
    </div>

    <script>
        // Match data from server
        const MATCH_DATA = @json($matchData);

        const API_ENDPOINTS = {
            syncEvents: "{{ route('referee.matches.sync-events', $match) }}",
            endMatch: "{{ route('referee.matches.end', $match) }}",
            getState: "{{ route('referee.matches.state', $match) }}",
            backUrl: "{{ route('referee.matches.index') }}"
        };
        const CSRF_TOKEN = "{{ csrf_token() }}";

        const { createApp, ref, reactive, computed, watch, onMounted, onUnmounted } = Vue

        createApp({
            setup() {
                // ==================== STATE ====================
                const gameMode = ref(MATCH_DATA.gameMode)
                const status = ref(MATCH_DATA.isCompleted ? 'finished' :
                                   MATCH_DATA.status === 'in_progress' ? 'paused' : 'waiting')
                const timer = ref(MATCH_DATA.timerSeconds || 0)
                let timerInterval = null

                const currentGame = ref(MATCH_DATA.currentGame || 1)
                const totalGames = ref(MATCH_DATA.bestOf || 3)
                const winScore = ref(11)

                // Build players array based on game mode
                const buildPlayersArray = (athlete, isDoubles) => {
                    if (isDoubles && athlete.partnerName) {
                        return [
                            { name: athlete.name || 'TBD', courtSide: 'right' },
                            { name: athlete.partnerName, courtSide: 'left' }
                        ]
                    }
                    return [{ name: athlete.name || 'TBD', courtSide: 'right' }]
                }

                const teams = reactive({
                    left: {
                        name: MATCH_DATA.athlete1.pairName || MATCH_DATA.athlete1.name || 'TBD',
                        athleteId: MATCH_DATA.athlete1.id,
                        score: 0,
                        gamesWon: MATCH_DATA.gamesWonAthlete1 || 0,
                        players: buildPlayersArray(MATCH_DATA.athlete1, MATCH_DATA.gameMode === 'doubles')
                    },
                    right: {
                        name: MATCH_DATA.athlete2.pairName || MATCH_DATA.athlete2.name || 'TBD',
                        athleteId: MATCH_DATA.athlete2.id,
                        score: 0,
                        gamesWon: MATCH_DATA.gamesWonAthlete2 || 0,
                        players: buildPlayersArray(MATCH_DATA.athlete2, MATCH_DATA.gameMode === 'doubles')
                    }
                })

                const serving = reactive({
                    team: MATCH_DATA.servingTeam === 'athlete2' ? 'right' : 'left',
                    serverIndex: 0,
                    serverNumber: MATCH_DATA.serverNumber || 2,
                    isFirstServeOfGame: true
                })

                const history = ref([])
                const eventLog = ref([])
                const pendingEvents = ref([])
                const gameScores = ref(MATCH_DATA.gameScores || [])
                const SYNC_THRESHOLD = 10

                const activeModal = ref(null)
                const coinResult = ref('')
                const coinRotation = ref(0)
                const coinFlipping = ref(false)
                const selectedLeftTeam = ref('left')
                const selectedServerIndex = ref(0)

                const timeout = reactive({
                    active: false,
                    team: null,
                    remaining: 60,
                    interval: null,
                    leftRemaining: 2,
                    rightRemaining: 2
                })

                const toast = reactive({
                    show: false,
                    icon: '',
                    message: ''
                })

                const hasSwitchedSidesInDecidingGame = ref(false)

                // ==================== LOCAL STORAGE ====================
                const STORAGE_KEY = `pickleball_match_${MATCH_DATA.id}`

                function saveMatchState() {
                    const now = Date.now()
                    const state = {
                        matchId: MATCH_DATA.id,
                        savedAt: now,
                        gameMode: gameMode.value,
                        status: status.value,
                        timer: timer.value,
                        timerStartedAt: status.value === 'playing' ? (now - timer.value * 1000) : null,
                        currentGame: currentGame.value,
                        totalGames: totalGames.value,
                        winScore: winScore.value,
                        teams: {
                            left: {
                                name: teams.left.name,
                                athleteId: teams.left.athleteId,
                                score: teams.left.score,
                                gamesWon: teams.left.gamesWon,
                                players: JSON.parse(JSON.stringify(teams.left.players))
                            },
                            right: {
                                name: teams.right.name,
                                athleteId: teams.right.athleteId,
                                score: teams.right.score,
                                gamesWon: teams.right.gamesWon,
                                players: JSON.parse(JSON.stringify(teams.right.players))
                            }
                        },
                        serving: { ...serving },
                        timeout: {
                            leftRemaining: timeout.leftRemaining,
                            rightRemaining: timeout.rightRemaining
                        },
                        history: history.value,
                        eventLog: eventLog.value,
                        gameScores: gameScores.value,
                        hasSwitchedSidesInDecidingGame: hasSwitchedSidesInDecidingGame.value
                    }
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
                    } catch (e) {
                        console.error('Failed to save match state:', e)
                    }
                }

                function loadMatchState() {
                    try {
                        const saved = localStorage.getItem(STORAGE_KEY)
                        if (!saved) return false

                        const state = JSON.parse(saved)
                        if (state.matchId !== MATCH_DATA.id) return false

                        // Check if state is too old (24h)
                        const maxAge = 24 * 60 * 60 * 1000
                        if (Date.now() - state.savedAt > maxAge) {
                            clearMatchState()
                            return false
                        }

                        // Restore state
                        gameMode.value = state.gameMode
                        timer.value = state.timer
                        status.value = state.status === 'playing' ? 'paused' : state.status
                        currentGame.value = state.currentGame
                        totalGames.value = state.totalGames
                        winScore.value = state.winScore

                        // Teams
                        Object.assign(teams.left, state.teams.left)
                        Object.assign(teams.right, state.teams.right)

                        // Serving
                        Object.assign(serving, state.serving)

                        // Timeout
                        timeout.leftRemaining = state.timeout.leftRemaining
                        timeout.rightRemaining = state.timeout.rightRemaining

                        // History & Events
                        history.value = state.history || []
                        eventLog.value = state.eventLog || []
                        gameScores.value = state.gameScores || []

                        hasSwitchedSidesInDecidingGame.value = state.hasSwitchedSidesInDecidingGame || false

                        return true
                    } catch (e) {
                        console.error('Failed to load match state:', e)
                        return false
                    }
                }

                function clearMatchState() {
                    try {
                        localStorage.removeItem(STORAGE_KEY)
                    } catch (e) {
                        console.error('Failed to clear match state:', e)
                    }
                }

                // ==================== COMPUTED ====================
                const isMatchCompleted = computed(() => MATCH_DATA.isCompleted || status.value === 'finished')

                const matchWinnerName = computed(() => {
                    if (!MATCH_DATA.isCompleted) return ''
                    const winnerId = MATCH_DATA.setScores?.[0]?.athlete1 > MATCH_DATA.setScores?.[0]?.athlete2 ?
                        MATCH_DATA.athlete1.id : MATCH_DATA.athlete2.id
                    return winnerId === MATCH_DATA.athlete1.id ?
                        (MATCH_DATA.athlete1.pairName || MATCH_DATA.athlete1.name) :
                        (MATCH_DATA.athlete2.pairName || MATCH_DATA.athlete2.name)
                })

                const timerDisplay = computed(() => {
                    const m = Math.floor(timer.value / 60).toString().padStart(2, '0')
                    const s = (timer.value % 60).toString().padStart(2, '0')
                    return `${m}:${s}`
                })

                const statusText = computed(() => {
                    const statusMap = {
                        waiting: 'Chờ bắt đầu',
                        playing: 'Đang thi đấu',
                        paused: 'Tạm dừng',
                        finished: 'Kết thúc'
                    }
                    return statusMap[status.value]
                })

                const scoreCall = computed(() => {
                    const serverScore = teams[serving.team].score
                    const receiverTeam = serving.team === 'left' ? 'right' : 'left'
                    const receiverScore = teams[receiverTeam].score

                    if (gameMode.value === 'singles') {
                        return `${serverScore} - ${receiverScore}`
                    } else {
                        return `${serverScore} - ${receiverScore} - ${serving.serverNumber}`
                    }
                })

                const servingCourtSide = computed(() => {
                    const score = teams[serving.team].score
                    return score % 2 === 0 ? 'right' : 'left'
                })

                const coinStyle = computed(() => ({
                    transform: `rotateY(${coinRotation.value}deg)`
                }))

                const timeoutDisplay = computed(() => {
                    const m = Math.floor(timeout.remaining / 60).toString().padStart(2, '0')
                    const s = (timeout.remaining % 60).toString().padStart(2, '0')
                    return `${m}:${s}`
                })

                const isDecidingGame = computed(() => {
                    const winsNeeded = Math.ceil(totalGames.value / 2)
                    return currentGame.value === totalGames.value &&
                           teams.left.gamesWon === teams.right.gamesWon &&
                           teams.left.gamesWon === winsNeeded - 1
                })

                // ==================== API SYNC ====================
                function recordEvent(type, team, data = {}) {
                    const event = {
                        type,
                        team,
                        data: {
                            ...data,
                            leftScore: teams.left.score,
                            rightScore: teams.right.score,
                            gameNumber: currentGame.value
                        },
                        timer_seconds: timer.value,
                        created_at: new Date().toISOString()
                    }
                    pendingEvents.value.push(event)

                    if (pendingEvents.value.length >= SYNC_THRESHOLD) {
                        syncEventsToServer()
                    }
                }

                async function syncEventsToServer() {
                    if (pendingEvents.value.length === 0) return

                    const eventsToSync = [...pendingEvents.value]
                    pendingEvents.value = []

                    const matchState = {
                        currentGame: currentGame.value,
                        gamesWonAthlete1: teams.left.gamesWon,
                        gamesWonAthlete2: teams.right.gamesWon,
                        gameScores: gameScores.value,
                        servingTeam: serving.team === 'left' ? 'athlete1' : 'athlete2',
                        serverNumber: serving.serverNumber,
                        timerSeconds: timer.value
                    }

                    try {
                        const response = await fetch(API_ENDPOINTS.syncEvents, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN
                            },
                            body: JSON.stringify({ events: eventsToSync, match_state: matchState })
                        })

                        if (!response.ok) {
                            throw new Error('Sync failed')
                        }
                    } catch (error) {
                        console.error('Event sync failed:', error)
                        showToast('❌', 'Lỗi kết nối mạng!')
                        pendingEvents.value = [...eventsToSync, ...pendingEvents.value]
                    }
                }

                async function endMatchAPI() {
                    await syncEventsToServer()

                    const winner = teams.left.gamesWon > teams.right.gamesWon ? 'left' : 'right'
                    const finalScoreParts = gameScores.value.map(g => `${g.athlete1}-${g.athlete2}`)
                    const finalScore = `${teams.left.gamesWon}-${teams.right.gamesWon} (${finalScoreParts.join(', ')})`

                    const finalState = {
                        winner,
                        winnerId: teams[winner].athleteId,
                        gameScores: gameScores.value,
                        finalScore,
                        totalTimer: timer.value,
                        teams: {
                            left: { gamesWon: teams.left.gamesWon, athleteId: teams.left.athleteId },
                            right: { gamesWon: teams.right.gamesWon, athleteId: teams.right.athleteId }
                        }
                    }

                    try {
                        const response = await fetch(API_ENDPOINTS.endMatch, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN
                            },
                            body: JSON.stringify(finalState)
                        })

                        if (response.ok) {
                            clearMatchState()
                            status.value = 'finished'
                            showToast('🏆', 'Trận đấu đã kết thúc!')
                            setTimeout(() => {
                                window.location.href = API_ENDPOINTS.backUrl
                            }, 2000)
                        } else {
                            showToast('❌', 'Lỗi kết thúc trận đấu')
                        }
                    } catch (error) {
                        console.error('End match failed:', error)
                        showToast('❌', 'Lỗi kết nối mạng!')
                    }
                }

                // ==================== METHODS ====================
                function startTimer() {
                    timerInterval = setInterval(() => {
                        timer.value++
                    }, 1000)
                }

                function stopTimer() {
                    if (timerInterval) {
                        clearInterval(timerInterval)
                        timerInterval = null
                    }
                }

                function showToast(icon, message) {
                    toast.icon = icon
                    toast.message = message
                    toast.show = true
                    setTimeout(() => { toast.show = false }, 3000)
                }

                function addEvent(message, shouldSave = true) {
                    const m = Math.floor(timer.value / 60).toString().padStart(2, '0')
                    const s = (timer.value % 60).toString().padStart(2, '0')
                    eventLog.value.unshift({
                        time: `${m}:${s}`,
                        message: message,
                        score: `${teams.left.score} - ${teams.right.score}`
                    })
                    if (eventLog.value.length > 50) eventLog.value.pop()
                    if (shouldSave && status.value !== 'waiting') saveMatchState()
                }

                function clearEventLog() {
                    if (confirm('Xóa toàn bộ lịch sử trận đấu?')) {
                        eventLog.value = []
                        showToast('🗑️', 'Đã xóa lịch sử')
                        saveMatchState()
                    }
                }

                function saveHistory() {
                    history.value.push({
                        leftScore: teams.left.score,
                        rightScore: teams.right.score,
                        servingTeam: serving.team,
                        serverIndex: serving.serverIndex,
                        serverNumber: serving.serverNumber,
                        isFirstServeOfGame: serving.isFirstServeOfGame,
                        leftPlayers: JSON.parse(JSON.stringify(teams.left.players)),
                        rightPlayers: JSON.parse(JSON.stringify(teams.right.players))
                    })
                    if (history.value.length > 20) history.value.shift()
                }

                function undo() {
                    if (history.value.length === 0) {
                        showToast('❌', 'Không có thao tác để hoàn tác')
                        return
                    }
                    const prev = history.value.pop()
                    teams.left.score = prev.leftScore
                    teams.right.score = prev.rightScore
                    serving.team = prev.servingTeam
                    serving.serverIndex = prev.serverIndex
                    serving.serverNumber = prev.serverNumber
                    serving.isFirstServeOfGame = prev.isFirstServeOfGame
                    teams.left.players = prev.leftPlayers
                    teams.right.players = prev.rightPlayers

                    recordEvent('undo', null)
                    addEvent('↩️ Hoàn tác thao tác trước')
                    showToast('↩️', 'Đã hoàn tác')
                }

                function swapPlayerPositions(team) {
                    const players = teams[team].players
                    if (players.length < 2) return
                    const temp = players[0].courtSide
                    players[0].courtSide = players[1].courtSide
                    players[1].courtSide = temp
                }

                function checkDecidingGameSwitch() {
                    if (!isDecidingGame.value) return
                    if (hasSwitchedSidesInDecidingGame.value) return

                    if (teams.left.score === 6 || teams.right.score === 6) {
                        hasSwitchedSidesInDecidingGame.value = true
                        performSideSwitch()
                        addEvent('🔄 Đổi sân ở game quyết định (6 điểm)')
                        showToast('🔄', 'Đổi sân - Game quyết định!')
                    }
                }

                function performSideSwitch() {
                    const tempName = teams.left.name
                    const tempAthleteId = teams.left.athleteId
                    const tempScore = teams.left.score
                    const tempGamesWon = teams.left.gamesWon
                    const tempPlayers = JSON.parse(JSON.stringify(teams.left.players))

                    teams.left.name = teams.right.name
                    teams.left.athleteId = teams.right.athleteId
                    teams.left.score = teams.right.score
                    teams.left.gamesWon = teams.right.gamesWon
                    teams.left.players = JSON.parse(JSON.stringify(teams.right.players))

                    teams.right.name = tempName
                    teams.right.athleteId = tempAthleteId
                    teams.right.score = tempScore
                    teams.right.gamesWon = tempGamesWon
                    teams.right.players = tempPlayers

                    serving.team = serving.team === 'left' ? 'right' : 'left'
                }

                function getPlayerIndexOnSide(team, side) {
                    const players = teams[team].players
                    return players[0]?.courtSide === side ? 0 : 1
                }

                function switchServingTeam() {
                    serving.team = serving.team === 'left' ? 'right' : 'left'
                }

                function handleSideOut() {
                    if (gameMode.value === 'singles') {
                        switchServingTeam()
                        const newServerScore = teams[serving.team].score
                        const newCourtSide = newServerScore % 2 === 0 ? 'right' : 'left'
                        if (teams[serving.team].players[0]) {
                            teams[serving.team].players[0].courtSide = newCourtSide
                        }
                        recordEvent('side_out', serving.team)
                        addEvent('🔄 Side-out!')
                        showToast('🔄', 'Side-out - Đổi quyền giao')
                    } else {
                        if (serving.isFirstServeOfGame) {
                            serving.isFirstServeOfGame = false
                            switchServingTeam()
                            serving.serverNumber = 1
                            serving.serverIndex = getPlayerIndexOnSide(serving.team, 'right')
                            recordEvent('side_out', serving.team)
                            addEvent('🔄 Side-out!')
                            showToast('🔄', 'Side-out - Đổi quyền giao')
                        } else if (serving.serverNumber === 1) {
                            serving.serverNumber = 2
                            serving.serverIndex = serving.serverIndex === 0 ? 1 : 0
                            recordEvent('server_change', serving.team, { serverNumber: 2 })
                            addEvent('2️⃣ Chuyển sang Server 2')
                            showToast('2️⃣', 'Chuyển Server 2')
                        } else {
                            switchServingTeam()
                            serving.serverNumber = 1
                            const side = teams[serving.team].score % 2 === 0 ? 'right' : 'left'
                            serving.serverIndex = getPlayerIndexOnSide(serving.team, side)
                            recordEvent('side_out', serving.team)
                            addEvent('🔄 Side-out!')
                            showToast('🔄', 'Side-out - Đổi quyền giao')
                        }
                    }
                }

                function rallyWon(winningTeam) {
                    if (status.value !== 'playing') {
                        showToast('⚠️', 'Vui lòng bắt đầu trận đấu!')
                        return
                    }

                    saveHistory()

                    if (winningTeam === serving.team) {
                        teams[winningTeam].score++

                        if (gameMode.value === 'doubles') {
                            swapPlayerPositions(winningTeam)
                        }

                        recordEvent('rally_won', winningTeam, { scored: true })
                        addEvent(`🎯 +1 điểm cho ${teams[winningTeam].name}`)

                        checkDecidingGameSwitch()
                        checkGameWin()
                    } else {
                        recordEvent('rally_lost', serving.team)
                        handleSideOut()
                    }
                }

                function adjustScore(team, delta) {
                    if (delta < 0 && teams[team].score <= 0) return
                    saveHistory()
                    teams[team].score += delta
                    recordEvent('score', team, { delta })
                    addEvent(`✏️ ${delta > 0 ? '+' : ''}${delta} điểm cho ${teams[team].name} (điều chỉnh)`)
                }

                function checkGameWin() {
                    const leftScore = teams.left.score
                    const rightScore = teams.right.score

                    if ((leftScore >= winScore.value || rightScore >= winScore.value) &&
                        Math.abs(leftScore - rightScore) >= 2) {
                        endGame()
                    }
                }

                function recordFault() {
                    if (status.value !== 'playing') return
                    saveHistory()
                    recordEvent('fault', serving.team)
                    handleSideOut()
                    addEvent('❌ Lỗi giao bóng')
                }

                function manualSwitchServer() {
                    if (status.value !== 'playing' || gameMode.value === 'singles') return
                    saveHistory()
                    serving.serverNumber = serving.serverNumber === 1 ? 2 : 1
                    serving.serverIndex = serving.serverIndex === 0 ? 1 : 0
                    recordEvent('server_change', serving.team, { serverNumber: serving.serverNumber })
                    addEvent(`🔄 Đổi sang Server ${serving.serverNumber}`)
                    showToast('🔄', `Đã đổi sang Server ${serving.serverNumber}`)
                }

                function requestTimeout() {
                    if (status.value !== 'playing') return
                    if (timeout.active) return
                    activeModal.value = 'timeout'
                }

                function startTimeout(team) {
                    const remaining = team === 'left' ? timeout.leftRemaining : timeout.rightRemaining
                    if (remaining <= 0) {
                        showToast('❌', `${teams[team].name} đã hết timeout!`)
                        return
                    }

                    if (team === 'left') {
                        timeout.leftRemaining--
                    } else {
                        timeout.rightRemaining--
                    }

                    stopTimer()
                    timeout.active = true
                    timeout.team = team
                    timeout.remaining = 60

                    recordEvent('timeout', team)
                    addEvent(`⏱️ ${teams[team].name} gọi timeout (còn ${team === 'left' ? timeout.leftRemaining : timeout.rightRemaining} lần)`)
                    saveMatchState()

                    timeout.interval = setInterval(() => {
                        timeout.remaining--
                        if (timeout.remaining <= 0) endTimeout()
                    }, 1000)

                    closeModal()
                }

                function endTimeout() {
                    if (timeout.interval) {
                        clearInterval(timeout.interval)
                        timeout.interval = null
                    }
                    timeout.active = false
                    timeout.team = null
                    timeout.remaining = 60

                    startTimer()
                    addEvent('▶️ Tiếp tục sau timeout')
                    showToast('▶️', 'Tiếp tục trận đấu')
                    saveMatchState()
                }

                function switchSides() {
                    saveHistory()
                    performSideSwitch()
                    addEvent('🔄 Đổi vị trí sân')
                    showToast('🔄', 'Đã đổi sân cho 2 đội')
                }

                function toggleMatch() {
                    if (status.value === 'waiting') {
                        activeModal.value = 'serveOrder'
                    }
                }

                function startMatch() {
                    status.value = 'playing'

                    if (gameMode.value === 'doubles') {
                        serving.serverNumber = 2
                        serving.isFirstServeOfGame = true
                        serving.serverIndex = selectedServerIndex.value
                    } else {
                        serving.serverIndex = 0
                    }

                    startTimer()
                    recordEvent('match_start', serving.team)
                    addEvent('🎾 Trận đấu bắt đầu!')
                    showToast('🎾', 'Trận đấu đã bắt đầu!')
                    saveMatchState()
                }

                function pauseMatch() {
                    if (status.value === 'waiting') return

                    if (status.value === 'paused') {
                        status.value = 'playing'
                        startTimer()
                        addEvent('▶️ Tiếp tục')
                        showToast('▶️', 'Tiếp tục trận đấu')
                    } else {
                        status.value = 'paused'
                        stopTimer()
                        addEvent('⏸️ Tạm dừng')
                        showToast('⏸️', 'Trận đấu tạm dừng')
                    }
                    saveMatchState()
                }

                function endGame() {
                    if (status.value === 'waiting') return

                    const leftScore = teams.left.score
                    const rightScore = teams.right.score

                    let winner = 'Hòa'
                    if (leftScore > rightScore) {
                        winner = teams.left.name
                        teams.left.gamesWon++
                    } else if (rightScore > leftScore) {
                        winner = teams.right.name
                        teams.right.gamesWon++
                    }

                    // Save game score
                    gameScores.value.push({
                        game: currentGame.value,
                        athlete1: leftScore,
                        athlete2: rightScore
                    })

                    recordEvent('game_end', leftScore > rightScore ? 'left' : 'right', {
                        gameNumber: currentGame.value,
                        leftScore,
                        rightScore
                    })
                    addEvent(`🏆 Game ${currentGame.value}: ${winner} thắng!`)
                    showToast('🏆', `${winner} thắng Game ${currentGame.value}!`)

                    // Sync state to server when game ends (important checkpoint)
                    syncEventsToServer()
                    saveMatchState()

                    const winsNeeded = Math.ceil(totalGames.value / 2)
                    if (teams.left.gamesWon >= winsNeeded || teams.right.gamesWon >= winsNeeded) {
                        const matchWinner = teams.left.gamesWon >= winsNeeded ? teams.left.name : teams.right.name
                        status.value = 'finished'
                        stopTimer()
                        addEvent(`👑 TRẬN ĐẤU KẾT THÚC: ${matchWinner} CHIẾN THẮNG!`)
                        showToast('👑', `${matchWinner} thắng trận đấu!`)
                        endMatchAPI()
                    } else if (currentGame.value < totalGames.value) {
                        currentGame.value++
                        resetScores()
                    }
                }

                function resetScores() {
                    teams.left.score = 0
                    teams.right.score = 0
                    serving.serverNumber = 2
                    serving.isFirstServeOfGame = true
                    serving.serverIndex = 0
                    history.value = []
                    status.value = 'waiting'
                    stopTimer()

                    // Reset player positions
                    if (teams.left.players[0]) teams.left.players[0].courtSide = 'right'
                    if (teams.left.players[1]) teams.left.players[1].courtSide = 'left'
                    if (teams.right.players[0]) teams.right.players[0].courtSide = 'right'
                    if (teams.right.players[1]) teams.right.players[1].courtSide = 'left'

                    // Reset timeout for new game
                    timeout.leftRemaining = 2
                    timeout.rightRemaining = 2
                    timeout.active = false
                    if (timeout.interval) {
                        clearInterval(timeout.interval)
                        timeout.interval = null
                    }

                    hasSwitchedSidesInDecidingGame.value = false
                    saveMatchState()
                }

                // Modal controls
                function showCoinFlip() {
                    activeModal.value = 'coinFlip'
                    coinResult.value = ''
                    coinRotation.value = 0
                    coinFlipping.value = false
                }

                function closeModal() {
                    activeModal.value = null
                }

                function flipCoin() {
                    if (coinFlipping.value) return
                    coinFlipping.value = true
                    coinResult.value = ''

                    const finalRotation = 2880 + (Math.random() > 0.5 ? 180 : 0)
                    coinRotation.value = finalRotation

                    const coinElement = document.querySelector('.coin-inner')
                    if (coinElement) {
                        coinElement.style.setProperty('--final-rotation', finalRotation + 'deg')
                    }

                    setTimeout(() => {
                        coinFlipping.value = false
                        const isBlue = (coinRotation.value / 180) % 2 === 0
                        coinResult.value = isBlue
                            ? `🔵 ${teams.left.name} được chọn sân trước!`
                            : `🔴 ${teams.right.name} được chọn sân trước!`
                        addEvent(`🎲 Bốc thăm: ${coinResult.value}`)
                    }, 1500)
                }

                function confirmCoinFlip() {
                    activeModal.value = 'teamAssign'
                }

                function confirmTeamAssignment() {
                    if (selectedLeftTeam.value === 'right') {
                        performSideSwitch()
                    }
                    addEvent('✅ Đã phân chia vị trí sân')
                    showToast('✅', 'Đã gán đội vào vị trí sân')
                    activeModal.value = 'serveOrder'
                }

                function confirmServeOrder() {
                    if (gameMode.value === 'doubles') {
                        serving.serverIndex = selectedServerIndex.value
                    }
                    closeModal()
                    startMatch()
                }

                function getPlayersString(team) {
                    if (gameMode.value === 'singles') {
                        return teams[team].players[0]?.name || ''
                    }
                    return teams[team].players.map(p => p.name).join(' - ')
                }

                function goBack() {
                    if (status.value !== 'waiting' && status.value !== 'finished') {
                        const choice = confirm('Trận đấu đang diễn ra. Bạn có chắc muốn rời đi?\n\nNhấn OK để rời đi (dữ liệu sẽ được lưu).\nNhấn Cancel để ở lại.')
                        if (!choice) return
                        syncEventsToServer()
                    }
                    window.location.href = API_ENDPOINTS.backUrl
                }

                function handleKeydown(e) {
                    if (status.value !== 'playing') return
                    if (e.key === ' ') {
                        e.preventDefault()
                        pauseMatch()
                    }
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault()
                        rallyWon('left')
                    }
                    if (e.key === 'ArrowRight') {
                        e.preventDefault()
                        rallyWon('right')
                    }
                }

                // Lifecycle
                onMounted(() => {
                    document.addEventListener('keydown', handleKeydown)

                    if (MATCH_DATA.isCompleted) {
                        status.value = 'finished'
                        addEvent('📊 Xem kết quả trận đấu đã hoàn thành', false)
                        return
                    }

                    const restored = loadMatchState()
                    if (restored) {
                        addEvent('🔄 Khôi phục trận đấu từ phiên trước', false)
                        showToast('🔄', 'Đã khôi phục trận đấu')

                        if (status.value === 'paused') {
                            setTimeout(() => {
                                if (confirm('Trận đấu đang tạm dừng. Bạn có muốn tiếp tục không?')) {
                                    status.value = 'playing'
                                    startTimer()
                                    addEvent('▶️ Tiếp tục trận đấu')
                                    saveMatchState()
                                }
                            }, 500)
                        }
                    } else {
                        addEvent('🎾 Sẵn sàng bắt đầu trận đấu', false)
                    }
                })

                onUnmounted(() => {
                    document.removeEventListener('keydown', handleKeydown)
                    stopTimer()
                })

                return {
                    // Constants
                    MATCH_DATA,
                    // State
                    gameMode, status, timer, currentGame, totalGames, winScore,
                    teams, serving, history, eventLog, activeModal,
                    coinResult, coinRotation, coinFlipping,
                    selectedLeftTeam, selectedServerIndex, timeout, toast,
                    // Computed
                    isMatchCompleted, matchWinnerName,
                    timerDisplay, statusText, scoreCall, servingCourtSide,
                    coinStyle, timeoutDisplay,
                    // Methods
                    rallyWon, adjustScore, undo, recordFault,
                    manualSwitchServer, requestTimeout, startTimeout, endTimeout,
                    switchSides, toggleMatch, pauseMatch, endGame,
                    showCoinFlip, closeModal, flipCoin, confirmCoinFlip,
                    confirmTeamAssignment, confirmServeOrder,
                    clearEventLog, getPlayersString, goBack
                }
            }
        }).mount('#app')
    </script>
</body>
</html>
