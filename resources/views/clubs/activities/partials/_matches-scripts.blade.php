{{-- Matches panel JS: AJAX for rounds, scores, standings, custom matches --}}
{{-- Variables: $club, $activity, $isManagement --}}
<script>
(function() {
    var clubSlug = '{{ $club->slug }}';
    var activityId = '{{ $activity->id }}';
    var baseUrl = '/clubs/' + clubSlug + '/activities/' + activityId + '/matches';
    var isManagement = {{ $isManagement ? 'true' : 'false' }};
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var headers = {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
    var roundsLoaded = false;

    function esc(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    function withLock(btn, fn) {
        if (!btn || btn.disabled) return;
        btn.disabled = true;
        fn().finally(function() { btn.disabled = false; });
    }

    // --- Load Rounds ---
    window.loadMatchRounds = function() {
        fetch(baseUrl + '/rounds', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var rounds = data.rounds || [];
            var container = document.getElementById('matches-rounds-container');
            var emptyState = document.getElementById('matches-empty-state');

            if (rounds.length === 0) {
                container.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            container.style.display = 'block';
            emptyState.style.display = 'none';
            container.innerHTML = renderRounds(rounds);
            roundsLoaded = true;
            populateRoundSelect(rounds);
            loadStandings();
        })
        .catch(function() {
            var container = document.getElementById('matches-rounds-container');
            container.style.display = 'block';
            container.innerHTML = '<p class="matches-empty">Không thể tải dữ liệu trận đấu.</p>';
        });
    };

    function renderRounds(rounds) {
        var html = '';
        rounds.forEach(function(round) {
            html += '<div class="round-section">';
            html += '<div class="round-header">';
            html += '<span class="round-title">Vòng ' + esc(String(round.round_number)) + '</span>';
            html += '<span class="round-status round-status-' + round.status + '">' + statusLabel(round.status) + '</span>';
            if (isManagement && round.status !== 'completed') {
                html += ' <button class="btn-matches btn-matches-secondary" style="padding:4px 10px;font-size:0.75rem;" '
                    + 'onclick="completeRound(' + round.id + ', this)">✓ Hoàn thành</button>';
            }
            html += '</div>';
            round.matches.forEach(function(m) {
                html += renderMatch(m, round.status);
            });
            html += '</div>';
        });
        return html;
    }

    function statusLabel(s) {
        return { 'pending': 'Chờ', 'in_progress': 'Đang diễn ra', 'completed': 'Hoàn thành' }[s] || s;
    }

    function renderMatch(m, roundStatus) {
        var p = m.players;
        var html = '<div class="match-card" data-match-id="' + m.id + '">';

        // Court badge
        if (m.court_number) {
            html += '<span class="match-court">Sân ' + m.court_number + '</span>';
        }

        // Team names
        html += '<span class="match-teams">';
        if (m.match_type === 'doubles') {
            html += esc(p.player1 ? p.player1.name : 'BYE') + ' & ' + esc(p.player2 ? p.player2.name : '');
            html += '<span class="team-separator">vs</span>';
            html += esc(p.player3 ? p.player3.name : 'BYE') + ' & ' + esc(p.player4 ? p.player4.name : '');
        } else {
            html += esc(p.player1 ? p.player1.name : 'BYE');
            html += '<span class="team-separator">vs</span>';
            html += esc(p.player3 ? p.player3.name : 'BYE');
        }
        html += '</span>';

        // Score display or input
        if (m.status === 'completed') {
            html += '<span class="match-score-display">' + m.team1_score + '<span class="score-separator">-</span>' + m.team2_score + '</span>';
            if (isManagement && roundStatus !== 'completed') {
                html += ' <button class="btn-edit-score" onclick="enableScoreEdit(' + m.id + ', ' + m.team1_score + ', ' + m.team2_score + ')">Sửa</button>';
            }
        } else if (isManagement && roundStatus !== 'completed') {
            html += '<span class="score-inputs" id="score-inputs-' + m.id + '">';
            html += '<input type="number" class="score-input" id="t1-score-' + m.id + '" min="0" max="99" value="0">';
            html += '<span class="score-separator">-</span>';
            html += '<input type="number" class="score-input" id="t2-score-' + m.id + '" min="0" max="99" value="0">';
            html += ' <button class="btn-save-score" onclick="saveMatchScore(' + m.id + ', this)">Lưu</button>';
            html += '</span>';
        } else {
            html += '<span class="match-score-display">- <span class="score-separator">-</span> -</span>';
        }

        html += '</div>';
        return html;
    }

    // --- Score Save ---
    window.saveMatchScore = function(matchId, btn) {
        var t1 = document.getElementById('t1-score-' + matchId);
        var t2 = document.getElementById('t2-score-' + matchId);
        if (!t1 || !t2) return;

        withLock(btn, function() {
            return fetch(baseUrl + '/' + matchId + '/score', {
                method: 'PUT', headers: headers,
                body: JSON.stringify({
                    team1_score: parseInt(t1.value) || 0,
                    team2_score: parseInt(t2.value) || 0,
                }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) { loadMatchRounds(); }
                else { alert(data.message || 'Có lỗi xảy ra'); }
            })
            .catch(function() { alert('Không thể kết nối'); });
        });
    };

    window.enableScoreEdit = function(matchId, t1Val, t2Val) {
        var parent = document.querySelector('.match-card[data-match-id="' + matchId + '"]');
        if (!parent) return;
        var scoreEl = parent.querySelector('.match-score-display');
        var editBtn = parent.querySelector('.btn-edit-score');
        if (scoreEl) scoreEl.remove();
        if (editBtn) editBtn.remove();

        var span = document.createElement('span');
        span.className = 'score-inputs';
        span.id = 'score-inputs-' + matchId;
        span.innerHTML = '<input type="number" class="score-input" id="t1-score-' + matchId + '" min="0" max="99" value="' + t1Val + '">'
            + '<span class="score-separator">-</span>'
            + '<input type="number" class="score-input" id="t2-score-' + matchId + '" min="0" max="99" value="' + t2Val + '">'
            + ' <button class="btn-save-score" onclick="saveMatchScore(' + matchId + ', this)">Lưu</button>';
        parent.appendChild(span);
    };

    // --- Complete Round ---
    window.completeRound = function(roundId, btn) {
        if (!confirm('Hoàn thành vòng đấu này? Điểm số sẽ bị khoá.')) return;
        withLock(btn, function() {
            return fetch(baseUrl + '/rounds/' + roundId + '/complete', {
                method: 'PUT', headers: headers,
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) { loadMatchRounds(); }
                else { alert(data.message || 'Có lỗi xảy ra'); }
            })
            .catch(function() { alert('Không thể kết nối'); });
        });
    };

    // --- Generate Matches ---
    window.openGenerateModal = function() {
        document.getElementById('generate-modal-overlay').classList.add('active');
    };
    window.closeGenerateModal = function() {
        document.getElementById('generate-modal-overlay').classList.remove('active');
    };
    window.generateMatches = function() {
        var format = document.getElementById('match-format').value;
        var courtCount = parseInt(document.getElementById('court-count').value) || 2;

        var hasRounds = roundsLoaded;
        if (hasRounds && !confirm('Tạo lại sẽ xoá tất cả trận đấu hiện tại. Tiếp tục?')) return;

        var btn = document.getElementById('btn-generate');
        withLock(btn, function() {
            return fetch(baseUrl + '/generate', {
                method: 'POST', headers: headers,
                body: JSON.stringify({ format: format, court_count: courtCount }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    closeGenerateModal();
                    loadMatchRounds();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(function() { alert('Không thể kết nối'); });
        });
    };

    // --- Standings ---
    function loadStandings() {
        fetch(baseUrl + '/standings', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var standings = data.standings || [];
            var section = document.getElementById('standings-section');
            var container = document.getElementById('matches-standings-table');
            if (standings.length === 0) {
                section.style.display = 'none';
                return;
            }
            section.style.display = 'block';
            var html = '<table class="matches-standings-table">';
            html += '<thead><tr><th>#</th><th>Tên</th><th>Trận</th><th>Thắng</th><th>Thua</th><th>+/-</th><th>Hiệu số</th></tr></thead>';
            html += '<tbody>';
            standings.forEach(function(s) {
                var rankClass = s.rank <= 3 ? ' class="rank-' + s.rank + '"' : '';
                html += '<tr' + rankClass + '>';
                html += '<td>' + esc(String(s.rank)) + '</td>';
                html += '<td><strong>' + esc(s.user.name) + '</strong></td>';
                html += '<td>' + s.matches_played + '</td>';
                html += '<td>' + s.wins + '</td>';
                html += '<td>' + s.losses + '</td>';
                html += '<td>' + s.points_scored + '/' + s.points_against + '</td>';
                html += '<td><strong>' + (s.point_differential >= 0 ? '+' : '') + s.point_differential + '</strong></td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        })
        .catch(function() {});
    }

    // --- Custom Match ---
    window.openCustomModal = function() {
        populatePlayerSelects();
        document.getElementById('custom-modal-overlay').classList.add('active');
    };
    window.closeCustomModal = function() {
        document.getElementById('custom-modal-overlay').classList.remove('active');
    };

    window.onMatchTypeChange = function() {
        var type = document.getElementById('custom-match-type').value;
        var grid = document.getElementById('player-grid');
        if (type === 'singles') {
            grid.classList.add('singles-mode');
        } else {
            grid.classList.remove('singles-mode');
        }
    };

    function populatePlayerSelects() {
        var dataEl = document.getElementById('confirmed-participants-data');
        if (!dataEl) return;
        var participants = JSON.parse(dataEl.dataset.participants || '[]');
        var selects = document.querySelectorAll('.player-select');
        selects.forEach(function(sel) {
            sel.innerHTML = '<option value="">-- Chọn --</option>';
            participants.forEach(function(p) {
                sel.innerHTML += '<option value="' + p.id + '">' + esc(p.name) + '</option>';
            });
        });
    }

    function populateRoundSelect(rounds) {
        var sel = document.getElementById('custom-round');
        if (!sel) return;
        sel.innerHTML = '<option value="">Vòng mới</option>';
        rounds.forEach(function(r) {
            if (r.status !== 'completed') {
                sel.innerHTML += '<option value="' + r.id + '">Vòng ' + r.round_number + '</option>';
            }
        });
    }

    window.submitCustomMatch = function() {
        var matchType = document.getElementById('custom-match-type').value;
        var body = {
            match_type: matchType,
            player1_id: parseInt(document.getElementById('custom-player1').value) || null,
            player3_id: parseInt(document.getElementById('custom-player3').value) || null,
            court_number: parseInt(document.getElementById('custom-court').value) || null,
            round_id: parseInt(document.getElementById('custom-round').value) || null,
        };

        if (matchType === 'doubles') {
            body.player2_id = parseInt(document.getElementById('custom-player2').value) || null;
            body.player4_id = parseInt(document.getElementById('custom-player4').value) || null;
        }

        if (!body.player1_id || !body.player3_id) {
            alert('Vui lòng chọn người chơi');
            return;
        }

        var btn = document.getElementById('btn-custom-submit');
        withLock(btn, function() {
            return fetch(baseUrl + '/custom', {
                method: 'POST', headers: headers,
                body: JSON.stringify(body),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    closeCustomModal();
                    loadMatchRounds();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(function() { alert('Không thể kết nối'); });
        });
    };

    // --- Reset ---
    window.resetMatches = function(btn) {
        if (!confirm('Xoá TẤT CẢ trận đấu và bảng xếp hạng? Không thể hoàn tác!')) return;
        withLock(btn, function() {
            return fetch(baseUrl + '/rounds', {
                method: 'DELETE', headers: headers,
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) { loadMatchRounds(); }
                else { alert(data.message || 'Có lỗi xảy ra'); }
            })
            .catch(function() { alert('Không thể kết nối'); });
        });
    };

    // --- Tab load trigger ---
    document.querySelectorAll('[data-tab="matches"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!roundsLoaded) loadMatchRounds();
        });
    });
    if (window.location.hash === '#matches') {
        loadMatchRounds();
    }
})();
</script>
