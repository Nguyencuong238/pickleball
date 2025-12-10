@extends('layouts.referee')

@section('title', 'Chi Tiết Trận Đấu')
@section('header', 'Chi Tiết Trận Đấu')

@section('css')
<style>
    .score-entry-card {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        border: 2px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .score-entry-header {
        background: linear-gradient(135deg, #F59E0B, #D97706);
        color: white;
        padding: 1rem 1.5rem;
    }
    .set-entry {
        background: var(--bg-light);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .set-entry h4 {
        margin-bottom: 1rem;
        color: var(--text-primary);
        font-weight: 600;
    }
    .score-grid {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 1rem;
        align-items: center;
    }
    .score-input-group label {
        display: block;
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }
    .score-input-group input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
    }
    .score-input-group input:focus {
        border-color: var(--primary-color);
        outline: none;
    }
    .vs-text {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-align: center;
    }
    .results-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    .results-table th {
        padding: 0.75rem;
        background: var(--bg-light);
        font-weight: 600;
        text-align: center;
    }
    .results-table td {
        padding: 0.75rem;
        background: var(--bg-white);
        text-align: center;
        border: 1px solid var(--border-color);
    }
    .results-table td.winner {
        background: #D1FAE5;
        color: #065F46;
        font-weight: 700;
    }
</style>
@endsection

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('referee.matches.index') }}" class="btn btn-secondary">
        ⬅️ Quay lại danh sách
    </a>
</div>

<div class="detail-card">
    <div class="detail-card-header">
        <h3>{{ $match->athlete1_name ?? 'TBD' }} vs {{ $match->athlete2_name ?? 'TBD' }}</h3>
    </div>
    <div class="detail-card-body">
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">🏆 Giải đấu</span>
                <span class="detail-value">{{ $match->tournament->name }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">📁 Nội dung</span>
                <span class="detail-value">{{ $match->category->name ?? 'N/A' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">🔄 Vòng đấu</span>
                <span class="detail-value">{{ $match->round->name ?? 'N/A' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">🏟️ Sân đấu</span>
                <span class="detail-value">{{ $match->court->name ?? 'TBA' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">📅 Ngày thi đấu</span>
                <span class="detail-value">{{ $match->match_date ? $match->match_date->format('d/m/Y') : 'TBA' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">⏰ Giờ thi đấu</span>
                <span class="detail-value">{{ $match->match_time ?? 'TBA' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">📌 Trạng thái</span>
                <span class="detail-value">
                    @if($match->status == 'scheduled')
                        <span class="badge badge-scheduled">⏰ Đã lên lịch</span>
                    @elseif($match->status == 'in_progress')
                        <span class="badge badge-in-progress">▶️ Đang diễn ra</span>
                    @elseif($match->status == 'completed')
                        <span class="badge badge-completed">✅ Đã hoàn thành</span>
                    @else
                        <span class="badge badge-scheduled">{{ $match->status }}</span>
                    @endif
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">🎮 Best of</span>
                <span class="detail-value">{{ $match->best_of ?? 3 }} sets</span>
            </div>
        </div>

        {{-- Start Match Button --}}
        @if($match->status == 'scheduled')
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <form method="POST" action="{{ route('referee.matches.start', $match) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #10B981, #059669);">
                        ▶️ Bắt đầu trận đấu
                    </button>
                </form>
            </div>
        @endif

        @if($match->isCompleted())
            <hr style="margin: 1.5rem 0; border-color: var(--border-color);">
            <div class="detail-row">
                <div class="detail-item">
                    <span class="detail-label">🎯 Tỉ số chung cuộc</span>
                    <span class="detail-value" style="font-size: 1.5rem; color: var(--primary-color);">
                        {{ $match->final_score }}
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">🥇 Người thắng</span>
                    <span class="detail-value" style="color: var(--accent-green);">
                        @if($match->winner_id == $match->athlete1_id)
                            {{ $match->athlete1_name }}
                        @elseif($match->winner_id == $match->athlete2_id)
                            {{ $match->athlete2_name }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Score Entry Form --}}
@if(!$match->isCompleted() && $match->status !== 'scheduled')
    <div class="score-entry-card">
        <div class="score-entry-header">
            <h3 style="margin: 0;">✏️ Nhập tỉ số</h3>
        </div>
        <div class="detail-card-body" style="padding: 1.5rem;">
            <form method="POST" action="{{ route('referee.matches.update-score', $match) }}">
                @csrf
                @method('PUT')

                <div id="sets-container">
                    @php
                        $existingSets = $match->set_scores ?? [];
                        if (empty($existingSets)) {
                            $existingSets = [['set' => 1, 'athlete1' => 0, 'athlete2' => 0]];
                        }
                    @endphp

                    @foreach($existingSets as $index => $set)
                        <div class="set-entry" data-set-index="{{ $index }}">
                            <h4>Set {{ $set['set'] ?? ($index + 1) }}</h4>
                            <input type="hidden" name="set_scores[{{ $index }}][set]" value="{{ $set['set'] ?? ($index + 1) }}">
                            <div class="score-grid">
                                <div class="score-input-group">
                                    <label>{{ $match->athlete1_name ?? 'VĐV 1' }}</label>
                                    <input type="number" name="set_scores[{{ $index }}][athlete1]"
                                           value="{{ $set['athlete1'] ?? 0 }}" min="0" required>
                                </div>
                                <div class="vs-text">vs</div>
                                <div class="score-input-group">
                                    <label>{{ $match->athlete2_name ?? 'VĐV 2' }}</label>
                                    <input type="number" name="set_scores[{{ $index }}][athlete2]"
                                           value="{{ $set['athlete2'] ?? 0 }}" min="0" required>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="addSet()">
                        ➕ Thêm set
                    </button>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Trạng thái trận đấu</label>
                    <select name="status" class="form-control" style="padding: 0.75rem 1rem; border: 2px solid var(--border-color); border-radius: var(--radius-md);" required>
                        <option value="in_progress" {{ $match->status == 'in_progress' ? 'selected' : '' }}>Đang thi đấu</option>
                        <option value="completed">Hoàn thành</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    💾 Lưu tỉ số
                </button>
            </form>
        </div>
    </div>

    <script>
        let setCount = {{ count($existingSets) }};

        function addSet() {
            setCount++;
            const container = document.getElementById('sets-container');
            const setHtml = `
                <div class="set-entry" data-set-index="${setCount - 1}">
                    <h4>Set ${setCount}</h4>
                    <input type="hidden" name="set_scores[${setCount - 1}][set]" value="${setCount}">
                    <div class="score-grid">
                        <div class="score-input-group">
                            <label>{{ $match->athlete1_name ?? 'VĐV 1' }}</label>
                            <input type="number" name="set_scores[${setCount - 1}][athlete1]" value="0" min="0" required>
                        </div>
                        <div class="vs-text">vs</div>
                        <div class="score-input-group">
                            <label>{{ $match->athlete2_name ?? 'VĐV 2' }}</label>
                            <input type="number" name="set_scores[${setCount - 1}][athlete2]" value="0" min="0" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', setHtml);
        }
    </script>
@elseif($match->isCompleted())
    {{-- Show completed match scores --}}
    <div class="detail-card">
        <div class="detail-card-header">
            <h3>📊 Kết quả chi tiết</h3>
        </div>
        <div class="detail-card-body">
            @if($match->set_scores && count($match->set_scores) > 0)
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Set</th>
                            <th>{{ $match->athlete1_name ?? 'VĐV 1' }}</th>
                            <th>{{ $match->athlete2_name ?? 'VĐV 2' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($match->set_scores as $set)
                            <tr>
                                <td>Set {{ $set['set'] ?? '-' }}</td>
                                <td class="{{ $set['athlete1'] > $set['athlete2'] ? 'winner' : '' }}">
                                    {{ $set['athlete1'] ?? 0 }}
                                </td>
                                <td class="{{ $set['athlete2'] > $set['athlete1'] ? 'winner' : '' }}">
                                    {{ $set['athlete2'] ?? 0 }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info">
                    ℹ️ Không có dữ liệu chi tiết
                </div>
            @endif
        </div>
    </div>
@else
    {{-- Match not started yet --}}
    <div class="detail-card">
        <div class="detail-card-header">
            <h3>ℹ️ Thông báo</h3>
        </div>
        <div class="detail-card-body">
            <div class="alert alert-info">
                ℹ️ Vui lòng bắt đầu trận đấu trước khi nhập tỉ số
            </div>
        </div>
    </div>
@endif

{{-- Match Notes --}}
@if($match->notes)
    <div class="detail-card">
        <div class="detail-card-header">
            <h3>📝 Ghi chú</h3>
        </div>
        <div class="detail-card-body">
            <p>{{ $match->notes }}</p>
        </div>
    </div>
@endif
@endsection
