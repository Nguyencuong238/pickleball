@extends('layouts.front')

@section('title', 'Kết quả đánh giá trình độ')

@section('css')
<style>
.result-wrapper {
    margin-top: 100px;
    min-height: calc(100vh - 200px);
    background: #f5f7fa;
    padding-bottom: 60px;
}

.result-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin-bottom: 20px;
}

.result-header {
    background: linear-gradient(135deg, #0aa289 0%, #088270 100%);
    color: white;
    padding: 40px;
    text-align: center;
}
.result-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}
.result-header p {
    opacity: 0.9;
    margin: 0;
    font-size: 0.95rem;
}

.result-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border-bottom: 1px solid #e2e8f0;
}
.summary-item {
    padding: 30px 20px;
    text-align: center;
    border-right: 1px solid #e2e8f0;
}
.summary-item:last-child {
    border-right: none;
}
.summary-label {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 8px;
}
.summary-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
}
.summary-sub {
    font-size: 0.8rem;
    color: #94a3b8;
    margin-top: 5px;
}
.summary-value.elo {
    color: #0aa289;
}

.domain-scores {
    padding: 30px 40px;
}
.domain-scores h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #1e293b;
}
.domain-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}
.domain-item {
    background: #f8fafc;
    border-radius: 12px;
    padding: 15px 20px;
}
.domain-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.domain-name {
    font-weight: 500;
    color: #1e293b;
    font-size: 0.9rem;
}
.domain-percent {
    font-weight: 700;
    font-size: 1rem;
}
.domain-percent.high { color: #22c55e; }
.domain-percent.medium { color: #f59e0b; }
.domain-percent.low { color: #ef4444; }
.domain-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}
.domain-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}
.domain-bar-fill.high { background: #22c55e; }
.domain-bar-fill.medium { background: #f59e0b; }
.domain-bar-fill.low { background: #ef4444; }

.flags-section {
    padding: 0 40px 30px;
}
.flag-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 10px;
    margin-bottom: 10px;
}
.flag-icon {
    width: 40px;
    height: 40px;
    background: #f59e0b;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
}
.flag-content {
    flex: 1;
}
.flag-type {
    font-weight: 600;
    font-size: 0.9rem;
    color: #92400e;
    margin-bottom: 3px;
}
.flag-message {
    font-size: 0.85rem;
    color: #78350f;
}
.flag-adjustment {
    font-weight: 700;
    color: #dc2626;
    font-size: 1rem;
}

.recommendations-section {
    padding: 0 40px 30px;
}
.recommendations-section h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #1e293b;
}
.recommendation-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 10px;
    margin-bottom: 10px;
}
.recommendation-item.high {
    background: #fef2f2;
    border-color: #fca5a5;
}
.recommendation-icon {
    width: 32px;
    height: 32px;
    background: #22c55e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.85rem;
    font-weight: bold;
    flex-shrink: 0;
}
.recommendation-item.high .recommendation-icon {
    background: #ef4444;
}
.recommendation-text {
    flex: 1;
}
.recommendation-domain {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e293b;
}
.recommendation-message {
    font-size: 0.85rem;
    color: #475569;
    margin-top: 3px;
}

.result-actions {
    padding: 20px 40px 30px;
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}
.btn-action {
    display: inline-block;
    padding: 14px 30px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.btn-primary-action {
    background: #0aa289;
    color: white;
}
.btn-primary-action:hover {
    background: #088270;
    color: white;
}
.btn-secondary-action {
    background: white;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.btn-secondary-action:hover {
    background: #f8fafc;
    color: #1e293b;
}

.duration-info {
    text-align: center;
    padding: 15px;
    background: #f8fafc;
    margin: 0 40px 20px;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #64748b;
}
.duration-info strong {
    color: #1e293b;
}

.provisional-notice {
    background: #dbeafe;
    border: 1px solid #93c5fd;
    padding: 15px 20px;
    margin: 0 40px 20px;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #1e40af;
}

@media (max-width: 768px) {
    .result-summary {
        grid-template-columns: 1fr;
    }
    .summary-item {
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px;
    }
    .summary-item:last-child {
        border-bottom: none;
    }
    .domain-grid {
        grid-template-columns: 1fr;
    }
    .result-header,
    .domain-scores,
    .flags-section,
    .recommendations-section,
    .result-actions {
        padding-left: 20px;
        padding-right: 20px;
    }
    .duration-info,
    .provisional-notice {
        margin-left: 20px;
        margin-right: 20px;
    }
}
</style>
@endsection

@section('content')
<div class="result-wrapper py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="result-card">
                    <div class="result-header">
                        <h1>🏆 Hoàn thành đánh giá!</h1>
                        <p>Dựa trên câu trả lời của bạn, đây là kết quả trình độ</p>
                    </div>

                    {{-- Summary --}}
                    <div class="result-summary">
                        <div class="summary-item">
                            <div class="summary-label">ELO Rating</div>
                            <div class="summary-value elo">{{ $result['final_elo'] }}</div>
                            @if($result['is_provisional'])
                                <div class="summary-sub">Tạm tính</div>
                            @endif
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Skill Level</div>
                            <div class="summary-value">{{ $result['skill_level'] }}</div>
                            <div class="summary-sub">DUPR tương đương</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Tổng điểm</div>
                            <div class="summary-value">{{ number_format($result['quiz_percent'], 1) }}%</div>
                            <div class="summary-sub">Weighted Score</div>
                        </div>
                    </div>

                    {{-- Duration --}}
                    <div class="duration-info">
                        @php
                            $duration = $result['duration'] ?? $attempt->duration_seconds ?? 0;
                            $minutes = floor($duration / 60);
                            $seconds = $duration % 60;
                        @endphp
                        Thời gian hoàn thành: <strong>{{ $minutes }} phút {{ $seconds }} giây</strong>
                    </div>

                    {{-- Provisional Notice --}}
                    @if($result['is_provisional'])
                        <div class="provisional-notice">
                            ℹ️ ELO tạm tính: Sau khi thi đấu 20 trận OCR, ELO của bạn sẽ được xác nhận chính thức.
                        </div>
                    @endif

                    {{-- Domain Scores --}}
                    <div class="domain-scores">
                        <h3>📊 Điểm theo lĩnh vực</h3>
                        <div class="domain-grid">
                            @php
                                $domainNames = [
                                    'rules' => 'Luật & Vị trí',
                                    'consistency' => 'Độ ổn định',
                                    'serve_return' => 'Giao bóng & Trả giao',
                                    'dink_net' => 'Dink & Chơi lưới',
                                    'reset_defense' => 'Reset & Phòng thủ',
                                    'tactics' => 'Chiến thuật & Phối hợp',
                                ];
                            @endphp
                            @foreach($result['domain_scores'] as $key => $score)
                                @php
                                    $scoreClass = $score >= 70 ? 'high' : ($score >= 50 ? 'medium' : 'low');
                                    $domainLabel = $domainNames[$key] ?? $key;
                                @endphp
                                <div class="domain-item">
                                    <div class="domain-header">
                                        <span class="domain-name">{{ $domainLabel }}</span>
                                        <span class="domain-percent {{ $scoreClass }}">{{ number_format($score, 1) }}%</span>
                                    </div>
                                    <div class="domain-bar">
                                        <div class="domain-bar-fill {{ $scoreClass }}" style="width: {{ $score }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Flags --}}
                    @if(!empty($result['flags']))
                        <div class="flags-section">
                            <h3>⚠️ Cảnh báo</h3>
                            @foreach($result['flags'] as $flag)
                                <div class="flag-item">
                                    <div class="flag-icon">⚠️</div>
                                    <div class="flag-content">
                                        <div class="flag-type">{{ $flag['type'] }}</div>
                                        <div class="flag-message">{{ $flag['message'] }}</div>
                                    </div>
                                    <div class="flag-adjustment">{{ $flag['adjustment'] > 0 ? '+' : '' }}{{ $flag['adjustment'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Recommendations --}}
                    @if(!empty($result['recommendations']))
                        <div class="recommendations-section">
                            <h3>🎯 Đề xuất cải thiện</h3>
                            @foreach($result['recommendations'] as $rec)
                                <div class="recommendation-item {{ $rec['priority'] }}">
                                    <div class="recommendation-icon">{{ $loop->iteration }}</div>
                                    <div class="recommendation-text">
                                        <div class="recommendation-domain">{{ $rec['domain'] }} ({{ number_format($rec['score'], 1) }}%)</div>
                                        <div class="recommendation-message">{{ $rec['message'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="result-actions">
                        <a href="{{ route('skill-quiz.index') }}" class="btn-action btn-secondary-action">
                            Quay lại
                        </a>
                        <a href="{{ route('ocr.profile', $user) }}" class="btn-action btn-primary-action">
                            Xem hồ sơ OPR
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
