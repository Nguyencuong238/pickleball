<!-- Add Team Button -->
@if(in_array($league->status, ['draft', 'registration']))
    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
        <button onclick="openTeamModal()" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
            <i class="fas fa-plus"></i> Thêm Đội
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
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                        <i class="fas fa-chevron-down" id="team-icon-{{ $team->id }}" style="color: #9ca3af; transition: transform 0.2s;"></i>
                    </div>
                </div>

                <!-- Team Roster (collapsible) -->
                <div id="team-roster-{{ $team->id }}" style="display: none; border-top: 1px solid #e2e8f0;">
                    @if($team->players->count() > 0)
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #fafafa;">
                                    <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.85rem;">Tên</th>
                                    <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.85rem;">Giới Tính</th>
                                    <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.85rem;">Vị Trí</th>
                                    <th style="padding: 10px 20px; text-align: center; font-weight: 600; color: #475569; font-size: 0.85rem;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($team->players as $player)
                                    <tr style="border-top: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 20px; font-size: 0.9rem;">{{ $player->user->name ?? 'N/A' }}</td>
                                        <td style="padding: 10px 20px; font-size: 0.9rem;">{{ $player->gender === 'male' ? 'Nam' : 'Nữ' }}</td>
                                        <td style="padding: 10px 20px; font-size: 0.9rem; color: #6b7280;">{{ $player->position ?? '-' }}</td>
                                        <td style="padding: 10px 20px; text-align: center;">
                                            @if(in_array($league->status, ['draft', 'registration']))
                                                <form method="POST" action="{{ route('homeyard.leagues.teams.players.destroy', [$league, $team, $player]) }}" style="display: inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" style="background: #fee2e2; color: #991b1b; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;" onclick="return confirm('Xóa VĐV này khỏi đội?')">
                                                        <i class="fas fa-times"></i>
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
                            <button onclick="openPlayerModal({{ $team->id }}, @json($team->name))" style="background: #e0f2fe; color: #0369a1; border: 1px dashed #0369a1; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                                <i class="fas fa-plus"></i> Thêm VĐV
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <div style="text-align: center; padding: 40px 20px;">
        <i class="fas fa-users" style="font-size: 2.5rem; color: #d1d5db; margin-bottom: 15px;"></i>
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

<!-- Add Player Modal -->
<div id="playerModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; width: 90%; max-width: 500px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: #1e293b; margin: 0;">Thêm VĐV vào <span id="playerModalTeamName"></span></h3>
            <button onclick="closePlayerModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af;">&#x2715;</button>
        </div>
        <form id="playerForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tìm Người Dùng *</label>
                <input type="text" id="playerSearchInput" placeholder="Nhập tên hoặc email..." autocomplete="off"
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

function openPlayerModal(teamId, teamName) {
    document.getElementById('playerModalTeamName').textContent = teamName;
    document.getElementById('playerForm').action = '{{ url("homeyard/leagues/" . $league->slug) }}/teams/' + teamId + '/players';
    document.getElementById('selectedUserId').value = '';
    document.getElementById('playerSearchInput').value = '';
    document.getElementById('playerSearchResults').style.display = 'none';
    var modal = document.getElementById('playerModal');
    modal.style.display = 'flex';
}
function closePlayerModal() {
    var modal = document.getElementById('playerModal');
    modal.style.display = 'none';
}

// Close modals on backdrop click
['teamModal', 'playerModal'].forEach(function(id) {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});

// User search for player
var searchTimeout;
document.getElementById('playerSearchInput').addEventListener('input', function() {
    var query = this.value.trim();
    clearTimeout(searchTimeout);
    if (query.length < 2) {
        document.getElementById('playerSearchResults').style.display = 'none';
        return;
    }
    searchTimeout = setTimeout(function() {
        fetch('{{ route("ocr.search-users") }}?q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var results = document.getElementById('playerSearchResults');
            results.innerHTML = '';
            var users = data.data || data;
            if (users.length === 0) {
                results.innerHTML = '<div style="padding: 10px; color: #9ca3af;">Không tìm thấy</div>';
            } else {
                users.forEach(function(user) {
                    var div = document.createElement('div');
                    div.style.cssText = 'padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;';
                    var strong = document.createElement('strong');
                    strong.textContent = user.name;
                    var span = document.createElement('span');
                    span.style.cssText = 'color: #6b7280; font-size: 0.85rem; margin-left: 6px;';
                    span.textContent = user.email || '';
                    div.appendChild(strong);
                    div.appendChild(span);
                    div.onmouseover = function() { this.style.background = '#f0f9ff'; };
                    div.onmouseout = function() { this.style.background = 'none'; };
                    div.onclick = function() {
                        document.getElementById('selectedUserId').value = user.id;
                        document.getElementById('playerSearchInput').value = user.name;
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
