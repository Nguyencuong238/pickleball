@extends('layouts.front')

@section('title', 'Đánh giá trình độ Pickleball')

@section('css')
<style>
.skill-quiz-container {
    margin-top: 100px;
    min-height: calc(100vh - 200px);
}
.quiz-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}
.quiz-header {
    background: linear-gradient(135deg, #0aa289 0%, #088270 100%);
    color: white;
    padding: 40px;
    text-align: center;
}
.quiz-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 10px;
}
.quiz-header p {
    opacity: 0.9;
    margin: 0;
}
.quiz-body {
    padding: 30px 40px;
}
.stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}
.stat-card .stat-label {
    color: #64748b;
    font-size: 0.85rem;
    margin-bottom: 8px;
}
.stat-card .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}
.stat-card .stat-sub {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 4px;
}
.eligibility-banner {
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
}
.eligibility-banner.allowed {
    background: #dcfce7;
    border: 1px solid #86efac;
}
.eligibility-banner.not-allowed {
    background: #fef3c7;
    border: 1px solid #fcd34d;
}
.eligibility-banner.in-progress {
    background: #dbeafe;
    border: 1px solid #93c5fd;
}
.eligibility-banner h4 {
    margin: 0 0 5px 0;
    font-size: 1rem;
    font-weight: 600;
}
.eligibility-banner p {
    margin: 0;
    font-size: 0.9rem;
    color: #475569;
}
.info-section {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #e2e8f0;
}
.info-section h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #1e293b;
}
.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.info-list li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
    color: #475569;
    font-size: 0.9rem;
}
.info-list li::before {
    content: "•";
    color: #0aa289;
    font-weight: bold;
    flex-shrink: 0;
}
.btn-start-quiz {
    display: inline-block;
    background: #0aa289;
    color: white;
    padding: 14px 40px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.btn-start-quiz:hover {
    background: #088270;
    color: white;
    transform: translateY(-2px);
}
.btn-start-quiz:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    transform: none;
}
.btn-continue-quiz {
    background: #f59e0b;
}
.btn-continue-quiz:hover {
    background: #d97706;
}
.history-section {
    margin-top: 40px;
}
.history-section h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #1e293b;
}
.history-table {
    width: 100%;
    border-collapse: collapse;
}
.history-table th,
.history-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.history-table th {
    background: #f8fafc;
    font-weight: 600;
    font-size: 0.85rem;
    color: #475569;
}
.history-table td {
    font-size: 0.9rem;
    color: #1e293b;
}
.badge-flag {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #fef3c7;
    color: #92400e;
}
/* Guest mode: domain overview */
.domain-overview {
    margin-top: 25px;
}
.domain-overview h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #1e293b;
}
.domain-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.domain-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
    border-radius: 8px;
    padding: 14px 18px;
    border: 1px solid #e2e8f0;
}
.domain-item .domain-name {
    font-weight: 500;
    color: #1e293b;
}
.domain-item .domain-count {
    font-size: 0.85rem;
    color: #64748b;
    background: #e2e8f0;
    padding: 4px 10px;
    border-radius: 12px;
}
@media (max-width: 768px) {
    .stat-grid {
        grid-template-columns: 1fr;
    }
    .quiz-body {
        padding: 20px;
    }
    .quiz-header {
        padding: 30px 20px;
    }
}
</style>
@endsection

@section('content')
<div class="skill-quiz-container py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="quiz-card">
                    <div class="quiz-header">
                        <h1>📊 Đánh giá trình độ Pickleball</h1>
                        <p>Trả lời 36 câu hỏi để xác định ELO và trình độ của bạn</p>
                    </div>

                    <div class="quiz-body">
                        @if($isGuest)
                            {{-- Guest Mode: Show preview --}}
                            <div class="eligibility-banner allowed">
                                <h4>Làm bài đánh giá để xác định trình độ của bạn</h4>
                                <p>Trả lời 36 câu hỏi tự đánh giá để biết ELO và trình độ OPR</p>
                            </div>

                            <div class="text-center mb-4">
                                <a href="{{ route('login', ['redirect' => route('skill-quiz.index')]) }}" class="btn-start-quiz">
                                    Đăng nhập để làm bài
                                </a>
                            </div>

                            {{-- Domain Overview --}}
                            <div class="domain-overview">
                                <h5>Nội dung bài đánh giá</h5>
                                <div class="domain-list">
                                    @foreach($domains as $domain)
                                        <div class="domain-item">
                                            <span class="domain-name">{{ $domain->name_vi }}</span>
                                            <span class="domain-count">{{ $domain->activeQuestions->count() }} câu hỏi</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Quiz Info --}}
                            <div class="info-section">
                                <h5>Thông tin về bài đánh giá</h5>
                                <ul class="info-list">
                                    <li>36 câu hỏi chia thành 6 lĩnh vực kỹ năng</li>
                                    <li>Thời gian khuyến nghị: 8-10 phút (tối đa 20 phút)</li>
                                    <li>Tự đánh giá theo thang điểm 0-3</li>
                                    <li>Kết quả sẽ cập nhật ELO và trình độ OPR của bạn</li>
                                </ul>
                            </div>
                        @else
                            {{-- Authenticated Mode: Current behavior --}}
                            {{-- Current Status --}}
                            <div class="stat-grid">
                                <div class="stat-card">
                                    <div class="stat-label">ELO hiện tại</div>
                                    <div class="stat-value">{{ $user->elo_rating ?? 1000 }}</div>
                                    @if($user->elo_is_provisional)
                                        <div class="stat-sub">Tạm tính</div>
                                    @endif
                                </div>
                                <div class="stat-card">
                                    <div class="stat-label">Số lần làm quiz</div>
                                    <div class="stat-value">{{ $user->skill_quiz_count ?? 0 }}</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-label">Trình độ</div>
                                    <div class="stat-value">{{ $user->opr_level ?? '1.0' }}</div>
                                    <div class="stat-sub">OPR Level</div>
                                </div>
                            </div>

                            @if($user->last_skill_quiz_at)
                                <div class="text-center mb-3">
                                    <small class="text-muted">
                                        Lần cuối làm quiz: {{ $user->last_skill_quiz_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            @endif

                            {{-- Eligibility Banner --}}
                            @if($inProgress)
                                <div class="eligibility-banner in-progress">
                                    <h4>Bạn có phiên quiz đang diễn ra</h4>
                                    <p>Bắt đầu lúc: {{ $inProgress->started_at->format('H:i d/m/Y') }}</p>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('skill-quiz.quiz') }}" class="btn-start-quiz btn-continue-quiz">
                                        Tiếp tục làm bài
                                    </a>
                                </div>
                            @elseif($eligibility['allowed'])
                                <div class="eligibility-banner allowed">
                                    <h4>Bạn đủ điều kiện làm quiz!</h4>
                                    <p>
                                        @if($eligibility['reason'] === 'calibrated')
                                            Bạn đã thi đấu 20+ trận, có thể làm lại quiz bất kỳ lúc nào.
                                        @elseif($user->skill_quiz_count === 0)
                                            Đây là lần đầu bạn làm quiz đánh giá trình độ.
                                        @else
                                            Bạn có thể làm lại quiz để cập nhật trình độ.
                                        @endif
                                    </p>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('skill-quiz.start') }}" class="btn-start-quiz">
                                        Bắt đầu đánh giá
                                    </a>
                                </div>
                            @else
                                <div class="eligibility-banner not-allowed">
                                    <h4>Chưa thể làm quiz</h4>
                                    <p>
                                        @if($eligibility['reason'] === 'cooldown')
                                            Bạn có thể làm lại sau {{ $eligibility['days_remaining'] }} ngày.
                                            <br>
                                            <small>Ngày cho phép: {{ $eligibility['next_allowed_at']->format('d/m/Y') }}</small>
                                        @endif
                                    </p>
                                </div>
                                <div class="text-center">
                                    <button class="btn-start-quiz" disabled>
                                        Chưa thể làm quiz
                                    </button>
                                </div>
                            @endif

                            {{-- Quiz Info --}}
                            <div class="info-section">
                                <h5>Thông tin về bài đánh giá</h5>
                                <ul class="info-list">
                                    <li>36 câu hỏi chia thành 6 lĩnh vực kỹ năng</li>
                                    <li>Thời gian khuyến nghị: 8-10 phút (tối đa 20 phút)</li>
                                    <li>Tự đánh giá theo thang điểm 0-3</li>
                                    <li>Kết quả sẽ cập nhật ELO và trình độ OPR của bạn</li>
                                    <li>Làm quiz quá nhanh (&lt;3 phút) sẽ bị trừ điểm</li>
                                </ul>
                            </div>

                            {{-- History Section --}}
                            @if(count($history) > 0)
                                <div class="history-section">
                                    <h5>Lịch sử làm quiz</h5>
                                    <div class="table-responsive">
                                        <table class="history-table">
                                            <thead>
                                                <tr>
                                                    <th>Ngày</th>
                                                    <th>Thời gian</th>
                                                    <th>Điểm</th>
                                                    <th>ELO</th>
                                                    <th>Level</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($history as $item)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($item['completed_at'])->format('d/m/Y') }}</td>
                                                        <td>{{ floor($item['duration_seconds'] / 60) }}:{{ str_pad($item['duration_seconds'] % 60, 2, '0', STR_PAD_LEFT) }}</td>
                                                        <td>{{ number_format($item['quiz_percent'], 1) }}%</td>
                                                        <td>{{ $item['final_elo'] }}</td>
                                                        <td>
                                                            {{ $item['skill_level'] }}
                                                            @if($item['has_flags'])
                                                                <span class="badge-flag">🚩</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
