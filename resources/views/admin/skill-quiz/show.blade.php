@extends('layouts.app')

@section('title', 'Chi tiết Skill Quiz')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h1>Chi tiết Quiz: {{ $attempt->user->name }}</h1>
        <a href="{{ route('admin.skill-quiz.index') }}" class="btn btn-secondary">
            ⬅️ Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Left Column: Summary, Flags, Actions --}}
        <div class="col-md-4">
            {{-- Summary --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">ℹ️ Thông tin chung</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">User:</td>
                            <td>
                                <a href="{{ route('admin.oprs.users.detail', $attempt->user) }}">
                                    <strong>{{ $attempt->user->name }}</strong>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>{{ $attempt->user->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trạng thái:</td>
                            <td>
                                @if($attempt->status === 'completed')
                                    <span class="badge bg-success">Hoàn thành</span>
                                @elseif($attempt->status === 'in_progress')
                                    <span class="badge bg-warning text-dark">Đang làm</span>
                                @else
                                    <span class="badge bg-secondary">Bỏ dở</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bắt đầu:</td>
                            <td>{{ $attempt->started_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kết thúc:</td>
                            <td>{{ $attempt->completed_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Thời gian:</td>
                            <td>
                                @if($attempt->duration_seconds)
                                    {{ gmdate('i:s', $attempt->duration_seconds) }}
                                    @if($attempt->duration_seconds < 180)
                                        <span class="badge bg-danger ms-1">Quá nhanh</span>
                                    @elseif($attempt->duration_seconds > 900)
                                        <span class="badge bg-warning text-dark ms-1">Quá lâu</span>
                                    @else
                                        <span class="badge bg-success ms-1">Bình thường</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><hr class="my-2"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Điểm %:</td>
                            <td><strong>{{ number_format($attempt->quiz_percent ?? 0, 1) }}%</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">ELO tính:</td>
                            <td>{{ $attempt->calculated_elo ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">ELO cuối:</td>
                            <td>
                                <strong class="fs-4 text-primary">{{ $attempt->final_elo ?? '-' }}</strong>
                                @if($attempt->is_provisional)
                                    <br><small class="text-muted">(Tạm tính)</small>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Flags --}}
            @if(count($attempt->flags ?? []) > 0)
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">⚠️ Cảnh báo ({{ count($attempt->flags) }})</h5>
                    </div>
                    <div class="card-body">
                        @foreach($attempt->flags as $flag)
                            <div class="alert alert-{{ ($flag['type'] ?? '') === 'ADMIN_ADJUSTMENT' ? 'info' : 'warning' }} mb-2">
                                <strong>{{ $flag['type'] ?? 'Unknown' }}</strong>
                                <p class="mb-1">{{ $flag['message'] ?? '' }}</p>
                                <small>
                                    Điều chỉnh:
                                    <strong class="{{ ($flag['adjustment'] ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($flag['adjustment'] ?? 0) > 0 ? '+' : '' }}{{ $flag['adjustment'] ?? 0 }} ELO
                                    </strong>
                                </small>
                                @if(isset($flag['reviewed']) && $flag['reviewed'])
                                    <br><small class="text-success">✅ Đã xem xét</small>
                                @endif
                            </div>
                        @endforeach

                        @php
                            $hasUnreviewed = collect($attempt->flags)->contains(fn($f) => !isset($f['reviewed']) || !$f['reviewed']);
                        @endphp
                        @if($hasUnreviewed)
                            <form action="{{ route('admin.skill-quiz.mark-reviewed', $attempt) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                    ✅ Đánh dấu đã xem xét
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="card mb-4 border-success">
                    <div class="card-body text-center text-success">
                        <strong>✅ Không có cảnh báo</strong>
                    </div>
                </div>
            @endif

            {{-- ELO Adjustment --}}
            @if($attempt->status === 'completed')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">✏️ Điều chỉnh ELO</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.skill-quiz.adjust-elo', $attempt) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">ELO mới</label>
                                <input type="number" name="new_elo" class="form-control"
                                    value="{{ $attempt->final_elo }}" min="100" max="2000" required>
                                <small class="text-muted">Giới hạn: 100 - 2000</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lý do <span class="text-danger">*</span></label>
                                <input type="text" name="reason" class="form-control" required
                                    placeholder="Nhập lý do điều chỉnh...">
                            </div>
                            <button type="submit" class="btn btn-warning w-100"
                                onclick="return confirm('Bạn có chắc muốn điều chỉnh ELO?')">
                                Cập nhật ELO
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column: Domain Scores & Answers --}}
        <div class="col-md-8">
            {{-- Domain Scores --}}
            @if($attempt->domain_scores && count($attempt->domain_scores) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">📊 Điểm theo lĩnh vực</h5>
                    </div>
                    <div class="card-body">
                        @foreach($attempt->domain_scores as $domain => $score)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ ucfirst(str_replace('_', ' ', $domain)) }}</span>
                                    <strong class="{{ $score >= 70 ? 'text-success' : ($score >= 50 ? 'text-info' : 'text-warning') }}">
                                        {{ number_format($score, 1) }}%
                                    </strong>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar {{ $score >= 70 ? 'bg-success' : ($score >= 50 ? 'bg-info' : 'bg-warning') }}"
                                         style="width: {{ $score }}%">
                                        {{ number_format($score, 1) }}%
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Answers by Domain --}}
            @if($answersByDomain && $answersByDomain->isNotEmpty())
                @foreach($answersByDomain as $domainKey => $answers)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                {{ $answers->first()->question->domain->name_vi ?? ucfirst(str_replace('_', ' ', $domainKey)) }}
                            </h6>
                            @php
                                $domainTotal = $answers->sum('answer_value');
                                $domainMax = $answers->count() * 3;
                                $domainPercent = $domainMax > 0 ? ($domainTotal / $domainMax) * 100 : 0;
                            @endphp
                            <span class="badge {{ $domainPercent >= 70 ? 'bg-success' : ($domainPercent >= 50 ? 'bg-info' : 'bg-warning') }}">
                                {{ $domainTotal }}/{{ $domainMax }} ({{ number_format($domainPercent, 0) }}%)
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Câu hỏi</th>
                                        <th style="width: 80px;" class="text-center">Trả lời</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($answers->sortBy('question.order_in_domain') as $answer)
                                        <tr>
                                            <td>{{ $answer->question->order_in_domain }}</td>
                                            <td>{{ Str::limit($answer->question->question_vi, 80) }}</td>
                                            <td class="text-center">
                                                @php
                                                    $value = $answer->answer_value;
                                                    $bgClass = match($value) {
                                                        0 => 'bg-danger',
                                                        1 => 'bg-warning text-dark',
                                                        2 => 'bg-info',
                                                        3 => 'bg-success',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $bgClass }}" style="font-size: 1rem; min-width: 30px;">
                                                    {{ $value }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        Chưa có câu trả lời nào
                    </div>
                </div>
            @endif

            {{-- User's Quiz History --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">📜 Lịch sử quiz của user</h5>
                </div>
                <div class="card-body">
                    @php
                        $history = \App\Models\SkillQuizAttempt::where('user_id', $attempt->user_id)
                            ->where('status', 'completed')
                            ->orderByDesc('completed_at')
                            ->limit(5)
                            ->get();
                    @endphp
                    @if($history->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Điểm %</th>
                                        <th>ELO</th>
                                        <th>Flags</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $h)
                                        <tr class="{{ $h->id === $attempt->id ? 'table-primary' : '' }}">
                                            <td>{{ $h->completed_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ number_format($h->quiz_percent, 1) }}%</td>
                                            <td>{{ $h->final_elo }}</td>
                                            <td>
                                                @if(count($h->flags ?? []) > 0)
                                                    <span class="badge bg-danger">{{ count($h->flags) }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($h->id !== $attempt->id)
                                                    <a href="{{ route('admin.skill-quiz.show', $h) }}" class="btn btn-sm btn-outline-secondary">
                                                        Xem
                                                    </a>
                                                @else
                                                    <span class="badge bg-primary">Hiện tại</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Chưa có lịch sử quiz</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
