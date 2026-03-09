<!-- Add Team Button -->
@if(in_array($league->status, ['draft', 'registration']))
    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
        <button onclick="openTeamModal()" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Thêm Đội
        </button>
    </div>
@endif

@if($league->teams->count() > 0)
    <div style="display: flex; flex-direction: column; gap: 15px;">
        @foreach($league->teams as $team)
            <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                <!-- Team Header -->
                <div style="padding: 15px 20px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; cursor: pointer; flex-wrap: wrap; gap: 10px;" onclick="toggleTeamRoster({{ $team->id }})">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        @if($team->logo)
                            <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                        @else
                            <div style="width: 40px; height: 40px; background: #dbeafe; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #1e40af; font-weight: 700;">{{ strtoupper(substr($team->name, 0, 2)) }}</div>
                        @endif
                        <div>
                            <div style="font-weight: 600; color: #1e293b;">{{ $team->name }}</div>
                            <div style="font-size: 0.85rem; color: #6b7280;">
                                {{ $team->players->count() }} VĐV
                                @if($team->captain)
                                    &middot; Đội trưởng: {{ $team->captain->name ?? '-' }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($team->status === 'active')
                            <span style="background-color: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem;">Hoạt động</span>
                        @elseif($team->status === 'inactive')
                            <span style="background-color: #f3f4f6; color: #4b5563; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem;">Không hoạt động</span>
                        @elseif($team->status === 'disqualified')
                            <span style="background-color: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem;">Loại</span>
                        @endif
                        @if(in_array($league->status, ['draft', 'registration']))
                            <form method="POST" action="{{ route('homeyard.leagues.teams.destroy', [$league, $team]) }}" style="display: inline;" onclick="event.stopPropagation();">
                                @csrf @method('DELETE')
                                <button type="submit" style="background: #fee2e2; color: #991b1b; border: none; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; cursor: pointer;" onclick="return confirm('Xóa đội này?')">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                </button>
                            </form>
                        @endif
                        <svg id="team-icon-{{ $team->id }}" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                </div>

                <!-- Team Roster (collapsible) -->
                <div id="team-roster-{{ $team->id }}" style="display: none; border-top: 1px solid #e2e8f0;">
                    @if($team->players->count() > 0)
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #fafafa;">
                                    @if($league->competition_format === 'mlp')
                                        <th style="padding: 10px 10px 10px 20px; text-align: center; font-weight: 600; color: #475569; font-size: 0.85rem; width: 40px;">#</th>
                                    @endif
                                    <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.85rem;">Tên</th>
                                    <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.85rem;">Giới Tính</th>
                                    <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.85rem;">Vị Trí</th>
                                    <th style="padding: 10px 20px; text-align: center; font-weight: 600; color: #475569; font-size: 0.85rem;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody id="player-list-{{ $team->id }}">
                                @foreach($team->players->sortBy('order') as $player)
                                    <tr style="border-top: 1px solid #f1f5f9;{{ $league->competition_format === 'mlp' ? ' cursor: grab;' : '' }}" data-player-id="{{ $player->id }}">
                                        @if($league->competition_format === 'mlp')
                                            <td class="drag-handle" style="padding: 10px 10px 10px 20px; text-align: center; color: #9ca3af; cursor: grab;">
                                                <svg width="10" height="16" viewBox="0 0 10 16" fill="currentColor"><circle cx="2" cy="2" r="1.5"/><circle cx="8" cy="2" r="1.5"/><circle cx="2" cy="8" r="1.5"/><circle cx="8" cy="8" r="1.5"/><circle cx="2" cy="14" r="1.5"/><circle cx="8" cy="14" r="1.5"/></svg>
                                            </td>
                                        @endif
                                        <td style="padding: 10px 20px; font-size: 0.9rem;">{{ $player->user->name ?? 'N/A' }}</td>
                                        <td style="padding: 10px 20px; font-size: 0.9rem;">{{ $player->gender === 'male' ? 'Nam' : 'Nữ' }}</td>
                                        <td style="padding: 10px 20px; font-size: 0.9rem; color: #6b7280;">{{ $player->position ?? '-' }}</td>
                                        <td style="padding: 10px 20px; text-align: center;">
                                            @if(in_array($league->status, ['draft', 'registration']))
                                                <form method="POST" action="{{ route('homeyard.leagues.teams.players.destroy', [$league, $team, $player]) }}" style="display: inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" style="background: #fee2e2; color: #991b1b; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;" onclick="return confirm('Xóa VĐV này khỏi đội?')">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                    </button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="padding: 15px 20px; color: #9ca3af; font-size: 0.9rem; margin: 0;">Chưa có VĐV nào trong đội</p>
                    @endif

                    @if(in_array($league->status, ['draft', 'registration']))
                        <div style="padding: 10px 20px; border-top: 1px solid #f1f5f9;">
                            <button onclick="openPlayerModal({{ $team->id }}, @js($team->name))" style="background: #e0f2fe; color: #0369a1; border: 1px dashed #0369a1; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Thêm VĐV
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <div style="text-align: center; padding: 40px 20px;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 15px;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        <h4 style="color: #9ca3af; margin: 10px 0;">Chưa có đội nào</h4>
        @if(in_array($league->status, ['draft', 'registration']))
            <p style="color: #9ca3af;">Bấm "Thêm Đội" để bắt đầu thêm đội vào league</p>
        @endif
    </div>
@endif

<!-- Add Team Modal -->
<div id="teamModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; width: 90%; max-width: 500px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: #1e293b; margin: 0;">Thêm Đội Mới</h3>
            <button onclick="closeTeamModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af;">&#x2715;</button>
        </div>
        <form method="POST" action="{{ route('homeyard.leagues.teams.store', $league) }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tên Đội *</label>
                <input type="text" name="name" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Logo Đội</label>
                <input type="file" name="logo" accept="image/*" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeTeamModal()" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Hủy</button>
                <button type="submit" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Thêm Đội</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Player Modal (2 tabs: Pool + Search) -->
<div id="playerModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; width: 90%; max-width: 550px; margin: auto; max-height: 80vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #1e293b; margin: 0;">Thêm VĐV vào <span id="playerModalTeamName"></span></h3>
            <button onclick="closePlayerModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af;">&#x2715;</button>
        </div>

        <!-- Tab Buttons -->
        <div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 15px;">
            <button class="player-modal-tab active" id="pmTabPool" onclick="switchPlayerTab('pool', this)" style="padding:10px 20px; border:none; background:none; color:var(--primary-color); font-weight:600; cursor:pointer; border-bottom:2px solid var(--primary-color); margin-bottom:-2px; font-size:0.9rem;">VĐV đã duyệt</button>
            <button class="player-modal-tab" id="pmTabSearch" onclick="switchPlayerTab('search', this)" style="padding:10px 20px; border:none; background:none; color:#6b7280; font-weight:600; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; font-size:0.9rem;">Tìm user</button>
        </div>

        <!-- Tab 1: Pool -->
        <div id="player-tab-pool">
            <div id="poolLoading" style="text-align:center; padding:20px; color:#9ca3af;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="animation: spin 1s linear infinite; display:inline-block; vertical-align:middle;"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Đang tải...</div>
            <div id="poolList"></div>
            <div id="poolEmpty" style="display:none; text-align:center; padding:20px; color:#9ca3af;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:8px; display:block; margin-left:auto; margin-right:auto;"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z"/></svg>
                Không có VĐV nào 
            </div>
        </div>

        <!-- Tab 2: Search (existing form) -->
        <div id="player-tab-search" style="display:none;">
            <form id="playerForm" method="POST" action="">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tìm Người Dùng *</label>
                    <input type="text" id="playerSearchInput" placeholder="Nhập số điện thoại..." autocomplete="off" inputmode="tel"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
                    <div id="playerSearchResults" style="border: 1px solid #e2e8f0; border-radius: 6px; max-height: 200px; overflow-y: auto; display: none; margin-top: 4px;"></div>
                    <input type="hidden" name="user_id" id="selectedUserId" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Giới Tính *</label>
                        <select name="gender" required style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Vị Trí</label>
                        <input type="text" name="position" placeholder="VD: Công, Thủ" style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closePlayerModal()" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Hủy</button>
                    <button type="submit" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Thêm VĐV</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>
<script>
function toggleTeamRoster(teamId) {
    var roster = document.getElementById('team-roster-' + teamId);
    var icon = document.getElementById('team-icon-' + teamId);
    if (roster.style.display === 'none') {
        roster.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        roster.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function openTeamModal() {
    var modal = document.getElementById('teamModal');
    modal.style.display = 'flex';
}
function closeTeamModal() {
    var modal = document.getElementById('teamModal');
    modal.style.display = 'none';
}

var currentTeamId = null;
var poolUrl = '{{ url("homeyard/leagues/" . $league->slug . "/registrations/pool") }}';
var addGroupBaseUrl = '{{ url("homeyard/leagues/" . $league->slug) }}/teams/__TEAM_ID__/add-group/__REG_ID__';
var poolCsrfToken = document.querySelector('meta[name="csrf-token"]').content;

function switchPlayerTab(tab, btn) {
    document.getElementById('player-tab-pool').style.display = tab === 'pool' ? 'block' : 'none';
    document.getElementById('player-tab-search').style.display = tab === 'search' ? 'block' : 'none';
    var poolBtn = document.getElementById('pmTabPool');
    var searchBtn = document.getElementById('pmTabSearch');
    poolBtn.style.color = tab === 'pool' ? 'var(--primary-color)' : '#6b7280';
    poolBtn.style.borderBottomColor = tab === 'pool' ? 'var(--primary-color)' : 'transparent';
    searchBtn.style.color = tab === 'search' ? 'var(--primary-color)' : '#6b7280';
    searchBtn.style.borderBottomColor = tab === 'search' ? 'var(--primary-color)' : 'transparent';
}

function openPlayerModal(teamId, teamName) {
    currentTeamId = teamId;
    document.getElementById('playerModalTeamName').textContent = teamName;
    document.getElementById('playerForm').action = '{{ url("homeyard/leagues/" . $league->slug) }}/teams/' + teamId + '/players';
    document.getElementById('selectedUserId').value = '';
    document.getElementById('playerSearchInput').value = '';
    document.getElementById('playerSearchResults').style.display = 'none';
    switchPlayerTab('pool');
    fetchPool();
    var modal = document.getElementById('playerModal');
    modal.style.display = 'flex';
}
function closePlayerModal() {
    var modal = document.getElementById('playerModal');
    modal.style.display = 'none';
}

function fetchPool() {
    document.getElementById('poolLoading').style.display = 'block';
    document.getElementById('poolList').innerHTML = '';
    document.getElementById('poolEmpty').style.display = 'none';

    fetch(poolUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': poolCsrfToken } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('poolLoading').style.display = 'none';
        if (!data || data.length === 0) {
            document.getElementById('poolEmpty').style.display = 'block';
            return;
        }
        renderPoolGroups(data);
    })
    .catch(function() {
        document.getElementById('poolLoading').style.display = 'none';
        document.getElementById('poolList').innerHTML = '<p style="color:#ef4444; text-align:center;">Lỗi tải pool</p>';
    });
}

function renderPoolGroups(groups) {
    var container = document.getElementById('poolList');
    container.innerHTML = '';
    groups.forEach(function(group) {
        var card = document.createElement('div');
        card.style.cssText = 'border:1px solid #e2e8f0; border-radius:8px; margin-bottom:10px; overflow:hidden;';

        var header = document.createElement('div');
        header.style.cssText = 'padding:10px 14px; background:#f0fdf4; display:flex; justify-content:space-between; align-items:center;';
        var headerLabel = document.createElement('span');
        headerLabel.style.cssText = 'font-weight:600; color:#15803d; font-size:0.85rem;';
        headerLabel.textContent = 'Nhóm #' + group.id + ' - ' + group.players.length + ' VĐV';
        header.appendChild(headerLabel);

        if (group.players.length > 1) {
            var addGroupBtn = document.createElement('button');
            addGroupBtn.style.cssText = 'padding:4px 12px; background:#22c55e; color:white; border:none; border-radius:6px; cursor:pointer; font-size:0.8rem; font-weight:600;';
            addGroupBtn.textContent = 'Thêm cả nhóm';
            addGroupBtn.onclick = (function(gid) { return function() { addGroupToTeam(gid); }; })(group.id);
            header.appendChild(addGroupBtn);
        }
        card.appendChild(header);

        group.players.forEach(function(p) {
            var row = document.createElement('div');
            row.style.cssText = 'padding:8px 14px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; font-size:0.85rem;';
            var info = document.createElement('span');
            info.style.color = '#475569';
            info.textContent = p.name + ' - ' + p.phone + ' - ' + (p.gender === 'male' ? 'Nam' : 'Nữ');
            var addBtn = document.createElement('button');
            addBtn.style.cssText = 'padding:3px 10px; background:#e0f2fe; color:#0369a1; border:1px solid #0369a1; border-radius:4px; cursor:pointer; font-size:0.8rem;';
            addBtn.textContent = 'Thêm';
            addBtn.onclick = (function(uid, g) { return function() { addPlayerFromPool(uid, g); }; })(p.user_id, p.gender);
            row.appendChild(info);
            row.appendChild(addBtn);
            card.appendChild(row);
        });
        container.appendChild(card);
    });
}

function addGroupToTeam(registrationId) {
    var url = addGroupBaseUrl.replace('__TEAM_ID__', currentTeamId).replace('__REG_ID__', registrationId);
    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': poolCsrfToken, 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { toastr.success(data.message); fetchPool(); setTimeout(function() { location.reload(); }, 500); }
        else { toastr.error(data.message || 'Có lỗi xảy ra.'); }
    })
    .catch(function() { toastr.error('Lỗi kết nối.'); });
}

function addPlayerFromPool(userId, gender) {
    var url = '{{ url("homeyard/leagues/" . $league->slug) }}/teams/' + currentTeamId + '/players';
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': poolCsrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ user_id: userId, gender: gender })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { toastr.success(data.message); fetchPool(); setTimeout(function() { location.reload(); }, 500); }
        else { toastr.error(data.message || 'Có lỗi xảy ra.'); }
    })
    .catch(function() { toastr.error('Lỗi kết nối.'); });
}

// Close modals on backdrop click
['teamModal', 'playerModal'].forEach(function(id) {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});

// MLP: Drag-drop player ordering
@if($league->competition_format === 'mlp')
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Sortable === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js';
        script.onload = initPlayerSortable;
        document.head.appendChild(script);
    } else {
        initPlayerSortable();
    }
});

function initPlayerSortable() {
    @foreach($league->teams as $team)
    (function() {
        var el = document.getElementById('player-list-{{ $team->id }}');
        if (!el) return;
        new Sortable(el, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                var ids = [];
                el.querySelectorAll('tr[data-player-id]').forEach(function(row) {
                    ids.push(parseInt(row.dataset.playerId));
                });
                fetch('{{ url("homeyard/leagues/" . $league->slug . "/teams/" . $team->id . "/players/order") }}', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ player_ids: ids })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) toastr.success('Thứ tự VĐV đã được cập nhật.');
                })
                .catch(function() { toastr.error('Lỗi cập nhật thứ tự.'); });
            }
        });
    })();
    @endforeach
}
@endif

// User search by phone for player
var searchTimeout;
document.getElementById('playerSearchInput').addEventListener('input', function() {
    var phone = this.value.trim().replace(/[^\d+]/g, '');
    clearTimeout(searchTimeout);
    if (phone.length < 9) {
        document.getElementById('playerSearchResults').style.display = 'none';
        document.getElementById('selectedUserId').value = '';
        return;
    }
    searchTimeout = setTimeout(function() {
        fetch('{{ route("homeyard.leagues.teams.search-user", $league) }}?phone=' + encodeURIComponent(phone), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(function(r) { return r.json(); })
        .then(function(users) {
            var results = document.getElementById('playerSearchResults');
            results.innerHTML = '';
            if (users.length === 0) {
                results.innerHTML = '<div style="padding: 10px; color: #9ca3af;">Không tìm thấy người dùng với SĐT này</div>';
            } else {
                users.forEach(function(user) {
                    var div = document.createElement('div');
                    div.style.cssText = 'padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;';
                    var strong = document.createElement('strong');
                    strong.textContent = user.name;
                    var span = document.createElement('span');
                    span.style.cssText = 'color: #6b7280; font-size: 0.85rem; margin-left: 6px;';
                    span.textContent = user.phone || '';
                    div.appendChild(strong);
                    div.appendChild(span);
                    div.onmouseover = function() { this.style.background = '#f0f9ff'; };
                    div.onmouseout = function() { this.style.background = 'none'; };
                    div.onclick = function() {
                        document.getElementById('selectedUserId').value = user.id;
                        document.getElementById('playerSearchInput').value = user.name + ' (' + (user.phone || '') + ')';
                        results.style.display = 'none';
                    };
                    results.appendChild(div);
                });
            }
            results.style.display = 'block';
        })
        .catch(function() {});
    }, 300);
});
</script>
