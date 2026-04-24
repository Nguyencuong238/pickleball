@php
    $athletes = $tournament->athletes()->with('user:id,opr_level')
        ->get(['tournament_athletes.id', 'tournament_athletes.tournament_id', 'tournament_athletes.user_id', 'tournament_athletes.athlete_name', 'tournament_athletes.email', 'tournament_athletes.phone'])
        ->unique(function ($a) {
            $email = strtolower(trim((string) $a->email));
            $phone = preg_replace('/\D/', '', (string) $a->phone);
            $name = strtolower(trim((string) $a->athlete_name));
            return $email . '|' . $phone . '|' . $name;
        })
        ->values();
    $athleteCount = $athletes->count();
    $remaining = $tournament->max_participants - $athleteCount;
@endphp
<div class="content-card">
    <h2 class="content-title">Danh sách vận động viên</h2>
    <div class="participants-stats">
        <div class="participant-stat">
            <div class="stat-number">{{ $athleteCount }}/{{ $tournament->max_participants }}</div>
            <div class="stat-label">Đã đăng ký</div>
        </div>
        <div class="participant-stat">
            <div class="stat-number">{{ max(0, $remaining) }}</div>
            <div class="stat-label">Còn lại</div>
        </div>
    </div>
    @if ($athletes->count() > 0)
        <div style="margin-top: 2rem; overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên VĐV</th>
                        <th>Điện thoại</th>
                        <th>Điểm trình độ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($athletes as $index => $athlete)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $athlete->athlete_name }}</td>
                            <td>{{ $athlete->phone ? 'xxxx' . substr($athlete->phone, -4) : '--' }}</td>
                            <td>{{ $athlete->user->opr_level ?? '--' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted">Danh sách VĐV sẽ được công bố sau khi đóng đăng ký</p>
    @endif
</div>
