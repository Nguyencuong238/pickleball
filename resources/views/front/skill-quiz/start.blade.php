@extends('layouts.front')

@section('title', 'Hướng dẫn làm bài - Skill Quiz')

@section('css')
<style>
.skill-quiz-start {
    margin-top: 100px;
    min-height: calc(100vh - 200px);
}
.instruction-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}
.instruction-header {
    background: #f8fafc;
    padding: 30px 40px;
    border-bottom: 1px solid #e2e8f0;
}
.instruction-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.instruction-body {
    padding: 30px 40px;
}
.answer-scale {
    background: #f8fafc;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
}
.answer-scale h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #1e293b;
}
.scale-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}
.scale-item {
    text-align: center;
    padding: 15px 10px;
    background: white;
    border-radius: 10px;
}
.scale-value {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
}
.scale-value.scale-0 { background: #ef4444; }
.scale-value.scale-1 { background: #f59e0b; }
.scale-value.scale-2 { background: #3b82f6; }
.scale-value.scale-3 { background: #22c55e; }
.scale-label {
    font-size: 0.8rem;
    color: #475569;
    line-height: 1.3;
}
.domain-section {
    margin-bottom: 30px;
}
.domain-section h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #1e293b;
}
.domain-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.domain-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: #f8fafc;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #475569;
}
.domain-number {
    width: 24px;
    height: 24px;
    background: #0aa289;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}
.warning-box {
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}
.warning-box h5 {
    color: #92400e;
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0 0 10px 0;
}
.warning-box ul {
    margin: 0;
    padding-left: 20px;
    color: #78350f;
}
.warning-box li {
    margin-bottom: 8px;
    font-size: 0.9rem;
}
.warning-box li:last-child {
    margin-bottom: 0;
}
.time-info {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.time-item {
    background: #f1f5f9;
    padding: 15px 25px;
    border-radius: 10px;
    text-align: center;
}
.time-item .time-label {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 5px;
}
.time-item .time-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
}
.start-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}
.btn-primary-quiz {
    display: inline-block;
    background: #0aa289;
    color: white;
    padding: 16px 50px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1.1rem;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.btn-primary-quiz:hover {
    background: #088270;
    color: white;
    transform: translateY(-2px);
}
.btn-primary-quiz:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    transform: none;
}
.btn-secondary-quiz {
    display: inline-block;
    background: white;
    color: #475569;
    padding: 16px 30px;
    border-radius: 10px;
    font-weight: 500;
    font-size: 1rem;
    text-decoration: none;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}
.btn-secondary-quiz:hover {
    background: #f8fafc;
    color: #1e293b;
}
@media (max-width: 768px) {
    .scale-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .domain-list {
        grid-template-columns: 1fr;
    }
    .instruction-body,
    .instruction-header {
        padding: 20px;
    }
}
</style>
@endsection

@section('content')
<div class="skill-quiz-start py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="instruction-card">
                    <div class="instruction-header">
                        <h2>📖 Hướng dẫn làm bài đánh giá</h2>
                    </div>

                    <div class="instruction-body">
                        {{-- Answer Scale --}}
                        <div class="answer-scale">
                            <h5>Thang điểm trả lời:</h5>
                            <div class="scale-grid">
                                <div class="scale-item">
                                    <div class="scale-value scale-0">0</div>
                                    <div class="scale-label">Chưa làm được</div>
                                </div>
                                <div class="scale-item">
                                    <div class="scale-value scale-1">1</div>
                                    <div class="scale-label">Làm được hiếm khi</div>
                                </div>
                                <div class="scale-item">
                                    <div class="scale-value scale-2">2</div>
                                    <div class="scale-label">Khá thường xuyên</div>
                                </div>
                                <div class="scale-item">
                                    <div class="scale-value scale-3">3</div>
                                    <div class="scale-label">Ổn định trong thi đấu</div>
                                </div>
                            </div>
                        </div>

                        {{-- Domains --}}
                        <div class="domain-section">
                            <h5>6 lĩnh vực đánh giá:</h5>
                            <ul class="domain-list">
                                <li>
                                    <span class="domain-number">1</span>
                                    Luật & Vị trí (Rules)
                                </li>
                                <li>
                                    <span class="domain-number">2</span>
                                    Độ ổn định (Consistency)
                                </li>
                                <li>
                                    <span class="domain-number">3</span>
                                    Giao bóng & Trả giao (Serve)
                                </li>
                                <li>
                                    <span class="domain-number">4</span>
                                    Dink & Chơi lưới (Net Play)
                                </li>
                                <li>
                                    <span class="domain-number">5</span>
                                    Reset & Phòng thủ (Defense)
                                </li>
                                <li>
                                    <span class="domain-number">6</span>
                                    Chiến thuật & Phối hợp (Tactics)
                                </li>
                            </ul>
                        </div>

                        {{-- Time Info --}}
                        <div class="time-info">
                            <div class="time-item">
                                <div class="time-label">Thời gian tối thiểu</div>
                                <div class="time-value">{{ floor($minTimeSeconds / 60) }} phút</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">Thời gian tối đa</div>
                                <div class="time-value">{{ floor($timeoutSeconds / 60) }} phút</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">Khuyến nghị</div>
                                <div class="time-value">8-10 phút</div>
                            </div>
                        </div>

                        {{-- Warning --}}
                        <div class="warning-box">
                            <h5>⚠️ Lưu ý quan trọng:</h5>
                            <ul>
                                <li>Trả lời <strong>trung thực</strong> để có kết quả chính xác nhất</li>
                                <li>Hoàn thành quá nhanh (&lt;3 phút) sẽ bị <strong>trừ điểm</strong></li>
                                <li>Sau {{ floor($timeoutSeconds / 60) }} phút sẽ tự động nộp bài</li>
                                <li>Không thể quay lại sau khi nộp bài</li>
                                <li>Cần trả lời ít nhất 30/36 câu để nộp bài</li>
                            </ul>
                        </div>

                        {{-- Actions --}}
                        <div class="start-actions">
                            <a href="{{ route('skill-quiz.index') }}" class="btn-secondary-quiz">
                                Quay lại
                            </a>
                            <a href="{{ route('skill-quiz.quiz') }}" class="btn-primary-quiz" id="btnStartQuiz">
                                ▶️ Bắt đầu làm bài
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
