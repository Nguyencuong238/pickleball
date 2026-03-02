{{-- Competition panel JS: AJAX for teams, matches, standings --}}
{{-- Variables: $club, $activity, $isManagement --}}
<script>
(function() {
    var clubSlug = '{{ $club->slug }}';
    var activityId = '{{ $activity->id }}';
    var baseUrl = '/clubs/' + clubSlug + '/activities/' + activityId + '/competition';
    var isManagement = {{ $isManagement ? 'true' : 'false' }};
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var headers = {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    // XSS-safe text escaping
    function esc(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    // Disable button during AJAX to prevent double-click
    function withLock(btn, fn) {
        if (btn.disabled) return;
        btn.disabled = true;
        fn().finally(function() { btn.disabled = false; });
    }

    window.addTeam = function() {
        var nameInput = document.getElementById('new-team-name');
        var name = nameInput.value.trim();
        if (!name) return;
        var btn = document.querySelector('.btn-add-team');
        withLock(btn, function() {
            return fetch(baseUrl + '/teams', {
                method: 'POST', headers: headers,
                body: JSON.stringify({ name: name }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) { nameInput.value = ''; location.reload(); }
                else { alert(data.message || 'Có lỗi xảy ra'); }
            })
            .catch(function() { alert('Không thể kết nối'); });
        });
    };

    window.removeTeam = function(teamId) {
        if (!confirm('Xóa đội này?')) return;
        fetch(baseUrl + '/teams/' + teamId, { method: 'DELETE', headers: headers })
        .then(function(r) { return r.json(); })
        .then(function(data) { if (data.success) location.reload(); })
        .catch(function() { alert('Không thể kết nối'); });
    };

    var formatLabels = {
        'round_robin': 'Vòng tròn',
        'pool_play': 'Chia bảng',
        'single_elimination': 'Loại trực tiếp',
    };

    window.generateSchedule = function() {
        var format = document.getElementById('schedule-format').value;
        var label = formatLabels[format] || format;
        if (!confirm('Tạo lịch thi đấu theo thể thức: ' + label + '?')) return;
        var btn = document.querySelector('.btn-generate');
        withLock(btn, function() {
            return fetch(baseUrl + '/generate-schedule', {
                method: 'POST', headers: headers,
                body: JSON.stringify({ format: format }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) { alert(data.message); location.reload(); }
                else { alert(data.message || 'Có lỗi xảy ra'); }
            })
            .catch(function() { alert('Không thể kết nối'); });
        });
    };

    window.saveScore = function(matchId, btn) {
        var homeInput = document.getElementById('home-score-' + matchId);
        var awayInput = document.getElementById('away-score-' + matchId);
        if (!homeInput || !awayInput) return;
        withLock(btn, function() {
            return fetch(baseUrl + '/matches/' + matchId + '/score', {
                method: 'PUT', headers: headers,
                body: JSON.stringify({
                    home_score: parseInt(homeInput.value) || 0,
                    away_score: parseInt(awayInput.value) || 0,
                }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) location.reload();
                else alert(data.message || 'Có lỗi xảy ra');
            })
            .catch(function() { alert('Không thể kết nối'); });
        });
    };

    // Load matches
    fetch(baseUrl + '/matches', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var container = document.getElementById('matches-list');
        var matches = data.matches || [];
        if (matches.length === 0) {
            container.innerHTML = '<p class="comp-empty">Chưa có trận đấu nào. Thêm đội và tạo lịch thi đấu.</p>';
            return;
        }
        var html = '';
        var currentRound = 0;
        matches.forEach(function(m) {
            if (m.round_number !== currentRound) {
                currentRound = m.round_number;
                html += '<div class="match-round">Vòng ' + esc(String(currentRound)) + '</div>';
            }
            var homeName = esc(m.home_team ? m.home_team.name : 'TBD');
            var awayName = esc(m.away_team ? m.away_team.name : 'BYE');
            html += '<div class="match-item">';
            html += '<span class="match-teams">' + homeName + ' vs ' + awayName + '</span>';
            if (m.status === 'completed') {
                html += '<span class="match-score">' + esc(String(m.home_score)) + ' - ' + esc(String(m.away_score)) + '</span>';
            } else if (isManagement && m.away_team) {
                html += '<input type="number" min="0" class="match-score-input" id="home-score-' + m.id + '" value="0">';
                html += '<span> - </span>';
                html += '<input type="number" min="0" class="match-score-input" id="away-score-' + m.id + '" value="0">';
                html += '<button class="btn-save-score" onclick="saveScore(' + m.id + ', this)">✅</button>';
            } else {
                html += '<span class="match-score">- : -</span>';
            }
            html += '</div>';
        });
        container.innerHTML = html;
    })
    .catch(function() {
        document.getElementById('matches-list').innerHTML = '<p class="comp-empty">Không thể tải lịch thi đấu</p>';
    });

    // Load teams independently
    fetch(baseUrl + '/teams', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var teams = data.teams || [];
        var teamList = document.getElementById('team-list');
        if (!teamList) return;
        if (teams.length === 0) {
            teamList.innerHTML = '<p class="comp-empty">Chưa có đội nào</p>';
            return;
        }
        var teamHtml = '';
        teams.forEach(function(t) {
            teamHtml += '<div class="team-item">';
            teamHtml += '<span class="team-name">' + esc(t.name) + '</span>';
            if (isManagement) {
                teamHtml += '<button class="btn-comp btn-remove-team" onclick="removeTeam(' + t.id + ')">X</button>';
            }
            teamHtml += '</div>';
        });
        teamList.innerHTML = teamHtml;
    })
    .catch(function() {
        var el = document.getElementById('team-list');
        if (el) el.innerHTML = '<p class="comp-empty">Không thể tải danh sách đội</p>';
    });

    // Load standings
    fetch(baseUrl + '/standings', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var container = document.getElementById('standings-table');
        var standings = data.standings || [];
        if (standings.length === 0) {
            container.innerHTML = '<p class="comp-empty">Chưa có dữ liệu xếp hạng</p>';
            return;
        }
        var html = '<table class="standings-table">';
        html += '<thead><tr><th>#</th><th>Đội</th><th>Trận</th><th>T</th><th>B</th><th>H</th><th>Điểm</th></tr></thead>';
        html += '<tbody>';
        standings.forEach(function(s, i) {
            html += '<tr>';
            html += '<td>' + esc(String(s.rank || (i + 1))) + '</td>';
            html += '<td><strong>' + esc(s.team ? s.team.name : 'N/A') + '</strong></td>';
            html += '<td>' + esc(String(s.played)) + '</td>';
            html += '<td>' + esc(String(s.wins)) + '</td>';
            html += '<td>' + esc(String(s.losses)) + '</td>';
            html += '<td>' + esc(String(s.draws)) + '</td>';
            html += '<td><strong>' + esc(String(s.points)) + '</strong></td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    })
    .catch(function() {
        document.getElementById('standings-table').innerHTML = '<p class="comp-empty">Không thể tải bảng xếp hạng</p>';
    });
})();
</script>
