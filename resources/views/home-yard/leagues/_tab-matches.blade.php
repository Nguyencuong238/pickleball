@php
    $activeTeamsCount = $league->teams->where('status', 'active')->count();
    $isMlp = $league->competition_format === 'mlp';
@endphp

@if($league->rounds->count() > 0)
    {{-- Regenerate Schedule button --}}
    @if($league->status === 'registration' && $activeTeamsCount >= 2)
        <div style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
            <form method="POST" action="{{ route('homeyard.leagues.schedule.generate', $league) }}">
                @csrf
                <button type="submit" style="background: #8b5cf6; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem;"
                    onclick="return confirm('Tạo lại lịch thi đấu round-robin cho {{ $activeTeamsCount }} đội? Lịch hiện tại sẽ bị thay thế.')">
                    <i class="fas fa-sync-alt"></i> Tạo Lại Lịch Thi Đấu
                </button>
            </form>
        </div>
    @endif

    <div style="display: flex; flex-direction: column; gap: 20px;">
        @foreach($league->rounds->sortBy('round_number') as $round)
            <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                <div style="padding: 12px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <span style="font-weight: 600; color: #1e293b;">
                                <i class="fas fa-flag"></i> Vòng {{ $round->round_number }}
                                @if($round->name) - {{ $round->name }} @endif
                            </span>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 4px; font-size: 0.85rem; color: #6b7280;">
                                @if($round->scheduled_date)
                                    <span><i class="fas fa-calendar"></i> {{ $round->scheduled_date->format('d/m/Y') }}</span>
                                @endif
                                @if($round->scheduled_time)
                                    <span><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($round->scheduled_time)->format('H:i') }}</span>
                                @endif
                                @if($round->venue)
                                    <span><i class="fas fa-map-marker-alt"></i> {{ $round->venue }}</span>
                                @endif
                            </div>
                        </div>

                        @if(in_array($league->status, ['draft', 'registration', 'active']))
                            <button type="button" onclick="toggleRoundEdit({{ $round->id }})"
                                style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; color: #475569;">
                                <i class="fas fa-pen"></i> Sửa
                            </button>
                        @endif
                    </div>

                    <div id="roundEdit{{ $round->id }}" style="display: none; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                        <form method="POST" action="{{ route('homeyard.leagues.rounds.update', [$league, $round]) }}"
                            style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
                            @csrf
                            @method('PUT')
                            <div>
                                <label style="display: block; font-size: 0.8rem; color: #475569; margin-bottom: 4px;">Ngày</label>
                                <input type="date" name="scheduled_date" value="{{ $round->scheduled_date ? $round->scheduled_date->format('Y-m-d') : '' }}"
                                    style="padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.85rem;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.8rem; color: #475569; margin-bottom: 4px;">Giờ</label>
                                <input type="time" name="scheduled_time" value="{{ $round->scheduled_time ? \Carbon\Carbon::parse($round->scheduled_time)->format('H:i') : '' }}"
                                    style="padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.85rem;">
                            </div>
                            <div style="flex: 1; min-width: 150px;">
                                <label style="display: block; font-size: 0.8rem; color: #475569; margin-bottom: 4px;">Địa điểm</label>
                                <input type="text" name="venue" value="{{ $round->venue ?? '' }}" placeholder="Nhập địa điểm..."
                                    style="width: 100%; padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.85rem;">
                            </div>
                            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; font-weight: 600;">
                                <i class="fas fa-save"></i> Lưu
                            </button>
                            <button type="button" onclick="toggleRoundEdit({{ $round->id }})"
                                style="background: #e2e8f0; color: #475569; border: none; padding: 6px 14px; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">
                                Hủy
                            </button>
                        </form>
                    </div>
                </div>

                @forelse($round->matches as $match)
                    <div style="padding: 12px 20px; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <!-- Home Team -->
                            <div style="flex: 1; min-width: 120px; text-align: right; font-weight: 500; color: #1e293b;">
                                {{ $match->homeTeam->name ?? 'TBD' }}
                            </div>

                            <!-- Score -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                @if($match->status === 'completed')
                                    <span style="background: #15803d; color: white; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 0.95rem;">
                                        {{ $match->home_score }} - {{ $match->away_score }}
                                    </span>
                                @elseif($match->status === 'in_progress')
                                    <span style="background: #f59e0b; color: white; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 0.95rem;">
                                        {{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}
                                    </span>
                                @else
                                    <span style="background: #e2e8f0; color: #6b7280; padding: 4px 12px; border-radius: 6px; font-weight: 600; font-size: 0.95rem;">
                                        vs
                                    </span>
                                @endif
                            </div>

                            <!-- Away Team -->
                            <div style="flex: 1; min-width: 120px; text-align: left; font-weight: 500; color: #1e293b;">
                                {{ $match->awayTeam->name ?? 'TBD' }}
                            </div>

                            <!-- Match Status + Action -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                @switch($match->status)
                                    @case('scheduled')
                                        <span style="background-color: #f3f4f6; color: #4b5563; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Chưa đấu</span>
                                        @break
                                    @case('in_progress')
                                        <span style="background-color: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Đang đấu</span>
                                        @break
                                    @case('completed')
                                        <span style="background-color: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Hoàn thành</span>
                                        @break
                                    @case('cancelled')
                                        <span style="background-color: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Đã hủy</span>
                                        @break
                                @endswitch

                                @if(in_array($league->status, ['active']) && $match->status !== 'cancelled')
                                    @if($isMlp)
                                        <button onclick='openMlpScoreModal(@json($match->id), @json($match->homeTeam->name ?? "TBD"), @json($match->awayTeam->name ?? "TBD"), @json($match->games))'
                                            style="background: #3b82f6; color: white; border: none; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">
                                            <i class="fas fa-edit"></i> Điểm
                                        </button>
                                    @else
                                        <button onclick='openScoreModal({{ $match->id }}, @json($match->homeTeam->name ?? "TBD"), @json($match->awayTeam->name ?? "TBD"), {{ $match->home_score ?? 0 }}, {{ $match->away_score ?? 0 }})'
                                            style="background: #3b82f6; color: white; border: none; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">
                                            <i class="fas fa-edit"></i> Điểm
                                        </button>
                                    @endif
                                @endif

                                {{-- MLP: toggle sub-game details --}}
                                @if($isMlp && $match->games->count() > 0)
                                    <button onclick="toggleMlpDetails({{ $match->id }})" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; color: #475569;">
                                        <span id="mlp-icon-{{ $match->id }}"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,4 6,8 10,4"/></svg></span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- MLP: Collapsible sub-game results --}}
                        @if($isMlp && $match->games->count() > 0)
                            <div id="mlp-details-{{ $match->id }}" style="display: none; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                                <div style="font-size: 0.85rem; color: #6b7280;">
                                    @foreach($match->games->sortBy('game_number') as $game)
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f8fafc; flex-wrap: wrap; gap: 4px;">
                                            <span style="color: #9ca3af; min-width: 60px;">Game {{ $game->game_number }}</span>
                                            <span style="flex: 1; text-align: right; color: #1e293b; font-weight: 500;">
                                                {{ optional(optional($game->homePlayer1)->user)->name ?? '?' }} + {{ optional(optional($game->homePlayer2)->user)->name ?? '?' }}
                                            </span>
                                            <span style="padding: 2px 8px; border-radius: 4px; font-weight: 700; min-width: 50px; text-align: center;
                                                {{ $game->status === 'completed' ? 'background: #f0fdf4; color: #15803d;' : 'background: #f3f4f6; color: #9ca3af;' }}">
                                                {{ $game->status === 'completed' ? $game->home_score . ' - ' . $game->away_score : '- - -' }}
                                            </span>
                                            <span style="flex: 1; text-align: left; color: #1e293b; font-weight: 500;">
                                                {{ optional(optional($game->awayPlayer1)->user)->name ?? '?' }} + {{ optional(optional($game->awayPlayer2)->user)->name ?? '?' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p style="padding: 15px 20px; color: #9ca3af; margin: 0;">Không có trận đấu trong vòng này</p>
                @endforelse
            </div>
        @endforeach
    </div>
@else
    <div style="text-align: center; padding: 40px 20px;">
        <i class="fas fa-calendar-alt" style="font-size: 2.5rem; color: #d1d5db; margin-bottom: 15px;"></i>
        <h4 style="color: #9ca3af; margin: 10px 0;">Chưa có lịch thi đấu</h4>
        @if($activeTeamsCount >= 2 && in_array($league->status, ['registration', 'active']))
            <p style="color: #6b7280; margin-bottom: 15px;">Bạn có {{ $activeTeamsCount }} đội đang hoạt động. Tạo lịch thi đấu ngay!</p>
            <form method="POST" action="{{ route('homeyard.leagues.schedule.generate', $league) }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;"
                    onclick="return confirm('Tạo lịch thi đấu round-robin cho {{ $activeTeamsCount }} đội?')">
                    <i class="fas fa-calendar-plus"></i> Tạo Lịch Thi Đấu
                </button>
            </form>
        @else
            <p style="color: #9ca3af;">Cần ít nhất 2 đội và league ở trạng thái đăng ký/hoạt động để tạo lịch thi đấu</p>
        @endif
    </div>
@endif

<!-- Traditional Score Entry Modal -->
<div id="scoreModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; width: 90%; max-width: 450px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: #1e293b; margin: 0;">Nhập Điểm Trận Đấu</h3>
            <button onclick="closeScoreModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af;">&#x2715;</button>
        </div>

        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 25px;">
            <div style="text-align: center; flex: 1;">
                <div style="font-weight: 600; color: #1e293b; margin-bottom: 10px;" id="scoreHomeTeam"></div>
                <input type="number" id="scoreHomeInput" min="0" value="0"
                    style="width: 80px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1.5rem; font-weight: 700; text-align: center;">
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #9ca3af;">-</div>
            <div style="text-align: center; flex: 1;">
                <div style="font-weight: 600; color: #1e293b; margin-bottom: 10px;" id="scoreAwayTeam"></div>
                <input type="number" id="scoreAwayInput" min="0" value="0"
                    style="width: 80px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1.5rem; font-weight: 700; text-align: center;">
            </div>
        </div>

        <input type="hidden" id="scoreMatchId">

        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" onclick="closeScoreModal()" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Hủy</button>
            <button type="button" onclick="submitScore()" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Lưu Điểm</button>
        </div>
    </div>
</div>

<!-- MLP Score Entry Modal -->
<div id="mlpScoreModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center; overflow-y: auto; padding: 20px 0;">
    <div style="background: white; border-radius: 15px; padding: 30px; width: 95%; max-width: 700px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: #1e293b; margin: 0;" id="mlpModalTitle">Nhập Điểm Các Game</h3>
            <button onclick="closeMlpScoreModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af;">&#x2715;</button>
        </div>

        <div id="mlpGamesContainer"></div>

        <!-- Running Total -->
        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 15px; padding-top: 15px; border-top: 2px solid #e2e8f0;">
            <span style="font-weight: 700; color: #1e293b;">Tổng:</span>
            <span id="mlpTotalHome" style="font-size: 1.3rem; font-weight: 700; color: #3b82f6;">0</span>
            <span style="font-weight: 700; color: #9ca3af;">-</span>
            <span id="mlpTotalAway" style="font-size: 1.3rem; font-weight: 700; color: #ef4444;">0</span>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" onclick="closeMlpScoreModal()" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Hủy</button>
            <button type="button" onclick="submitMlpScores()" id="mlpSubmitBtn" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Lưu Tất Cả</button>
        </div>
    </div>
</div>

<script>
var leagueUrl = '{{ url("homeyard/leagues/" . $league->slug) }}';
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function toggleRoundEdit(roundId) {
    var el = document.getElementById('roundEdit' + roundId);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

// === Traditional Score Modal ===
function openScoreModal(matchId, homeTeam, awayTeam, homeScore, awayScore) {
    document.getElementById('scoreMatchId').value = matchId;
    document.getElementById('scoreHomeTeam').textContent = homeTeam;
    document.getElementById('scoreAwayTeam').textContent = awayTeam;
    document.getElementById('scoreHomeInput').value = homeScore;
    document.getElementById('scoreAwayInput').value = awayScore;
    document.getElementById('scoreModal').style.display = 'flex';
}

function closeScoreModal() {
    document.getElementById('scoreModal').style.display = 'none';
}

document.getElementById('scoreModal').addEventListener('click', function(e) {
    if (e.target === this) closeScoreModal();
});

function submitScore() {
    var matchId = document.getElementById('scoreMatchId').value;
    var homeScore = document.getElementById('scoreHomeInput').value;
    var awayScore = document.getElementById('scoreAwayInput').value;

    fetch(leagueUrl + '/matches/' + matchId + '/score', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ home_score: parseInt(homeScore), away_score: parseInt(awayScore) })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            toastr.success(data.message);
            closeScoreModal();
            window.location.reload();
        } else {
            toastr.error(data.message || 'Có lỗi xảy ra.');
        }
    })
    .catch(function() { toastr.error('Có lỗi xảy ra.'); });
}

// === MLP Score Modal ===
var mlpCurrentMatchId = null;

function openMlpScoreModal(matchId, homeTeam, awayTeam, games) {
    mlpCurrentMatchId = matchId;
    document.getElementById('mlpModalTitle').textContent = 'Nhập Điểm - ' + homeTeam + ' vs ' + awayTeam;
    var container = document.getElementById('mlpGamesContainer');
    container.innerHTML = '';

    games.sort(function(a, b) { return a.game_number - b.game_number; });

    games.forEach(function(game) {
        var hp1 = (game.home_player1 && game.home_player1.user) ? game.home_player1.user.name : '?';
        var hp2 = (game.home_player2 && game.home_player2.user) ? game.home_player2.user.name : '?';
        var ap1 = (game.away_player1 && game.away_player1.user) ? game.away_player1.user.name : '?';
        var ap2 = (game.away_player2 && game.away_player2.user) ? game.away_player2.user.name : '?';

        var row = document.createElement('div');
        row.className = 'mlp-game-row';
        row.dataset.gameId = game.id;
        row.dataset.matchId = matchId;
        row.style.cssText = 'display: flex; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;';

        row.innerHTML =
            '<span style="color: #9ca3af; font-size: 0.8rem; min-width: 50px;">Game ' + game.game_number + '</span>' +
            '<span style="flex: 1; text-align: right; font-size: 0.85rem; font-weight: 500; color: #1e293b; min-width: 100px;">' + hp1 + ' + ' + hp2 + '</span>' +
            '<input type="number" class="mlp-home-score" min="0" value="' + (game.home_score || 0) + '" oninput="updateMlpTotal()" ' +
                'style="width: 60px; padding: 8px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 1.1rem; font-weight: 700; text-align: center;">' +
            '<span style="font-weight: 700; color: #9ca3af;">-</span>' +
            '<input type="number" class="mlp-away-score" min="0" value="' + (game.away_score || 0) + '" oninput="updateMlpTotal()" ' +
                'style="width: 60px; padding: 8px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 1.1rem; font-weight: 700; text-align: center;">' +
            '<span style="flex: 1; text-align: left; font-size: 0.85rem; font-weight: 500; color: #1e293b; min-width: 100px;">' + ap1 + ' + ' + ap2 + '</span>';

        container.appendChild(row);
    });

    updateMlpTotal();
    document.getElementById('mlpScoreModal').style.display = 'flex';
}

function closeMlpScoreModal() {
    document.getElementById('mlpScoreModal').style.display = 'none';
}

document.getElementById('mlpScoreModal').addEventListener('click', function(e) {
    if (e.target === this) closeMlpScoreModal();
});

function updateMlpTotal() {
    var totalHome = 0, totalAway = 0;
    document.querySelectorAll('.mlp-game-row').forEach(function(row) {
        totalHome += parseInt(row.querySelector('.mlp-home-score').value) || 0;
        totalAway += parseInt(row.querySelector('.mlp-away-score').value) || 0;
    });
    document.getElementById('mlpTotalHome').textContent = totalHome;
    document.getElementById('mlpTotalAway').textContent = totalAway;
}

function submitMlpScores() {
    var rows = document.querySelectorAll('.mlp-game-row');
    var promises = [];
    var btn = document.getElementById('mlpSubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Đang lưu...';

    rows.forEach(function(row) {
        var gameId = row.dataset.gameId;
        var matchId = row.dataset.matchId;
        var home = parseInt(row.querySelector('.mlp-home-score').value) || 0;
        var away = parseInt(row.querySelector('.mlp-away-score').value) || 0;

        promises.push(
            fetch(leagueUrl + '/matches/' + matchId + '/games/' + gameId + '/score', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ home_score: home, away_score: away })
            }).then(function(r) { return r.json(); })
        );
    });

    Promise.all(promises).then(function(results) {
        var allSuccess = results.every(function(r) { return r.success; });
        if (allSuccess) {
            toastr.success('Cập nhật điểm thành công.');
            closeMlpScoreModal();
            window.location.reload();
        } else {
            toastr.error('Có lỗi xảy ra khi lưu điểm.');
            btn.disabled = false;
            btn.textContent = 'Lưu Tất Cả';
        }
    }).catch(function() {
        toastr.error('Có lỗi xảy ra.');
        btn.disabled = false;
        btn.textContent = 'Lưu Tất Cả';
    });
}

// === MLP Sub-game Details Toggle ===
function toggleMlpDetails(matchId) {
    var el = document.getElementById('mlp-details-' + matchId);
    var icon = document.getElementById('mlp-icon-' + matchId);
    if (el.style.display === 'none') {
        el.style.display = 'block';
        icon.innerHTML = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,8 6,4 10,8"/></svg>';
    } else {
        el.style.display = 'none';
        icon.innerHTML = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,4 6,8 10,4"/></svg>';
    }
}
</script>
