@php
    $activeTeamsCount = $league->teams->where('status', 'active')->count();
@endphp

@if($league->rounds->count() > 0)
    {{-- Nút Tạo Lại Lịch khi đang ở trạng thái đăng ký --}}
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

                        {{-- Nút chỉnh sửa thông tin vòng đấu --}}
                        @if(in_array($league->status, ['draft', 'registration', 'active']))
                            <button type="button" onclick="toggleRoundEdit({{ $round->id }})"
                                style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; color: #475569;">
                                <i class="fas fa-pen"></i> Sửa
                            </button>
                        @endif
                    </div>

                    {{-- Form chỉnh sửa vòng đấu (ẩn mặc định) --}}
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
                    <div style="padding: 12px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
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
                                <button onclick='openScoreModal({{ $match->id }}, @json($match->homeTeam->name ?? "TBD"), @json($match->awayTeam->name ?? "TBD"), {{ $match->home_score ?? 0 }}, {{ $match->away_score ?? 0 }})'
                                    style="background: #3b82f6; color: white; border: none; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">
                                    <i class="fas fa-edit"></i> Điểm
                                </button>
                            @endif
                        </div>
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

<!-- Score Entry Modal -->
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

<script>
function toggleRoundEdit(roundId) {
    var el = document.getElementById('roundEdit' + roundId);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

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

    fetch('{{ url("homeyard/leagues/" . $league->slug) }}/matches/' + matchId + '/score', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
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
    .catch(function() {
        toastr.error('Có lỗi xảy ra.');
    });
}
</script>
