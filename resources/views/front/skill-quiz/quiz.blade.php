@extends('layouts.front')

@section('title', 'Đánh giá trình độ')

@section('css')
<style>
.quiz-wrapper {
    margin-top: 80px;
    min-height: calc(100vh - 80px);
    background: #f5f7fa;
    padding-bottom: 100px;
}
.quiz-timer {
    position: fixed;
    top: 90px;
    right: 20px;
    background: white;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    text-align: center;
    min-width: 120px;
}
.quiz-timer.warning {
    background: #fef3c7;
    border: 2px solid #f59e0b;
}
.quiz-timer.danger {
    background: #fee2e2;
    border: 2px solid #ef4444;
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}
.timer-label {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 5px;
}
.timer-display {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
}
.quiz-timer.warning .timer-display { color: #d97706; }
.quiz-timer.danger .timer-display { color: #dc2626; }

.quiz-progress {
    position: fixed;
    top: 90px;
    left: 20px;
    background: white;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    text-align: center;
    min-width: 120px;
}
.progress-label {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 5px;
}
.progress-display {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0aa289;
}
.progress-bar-mini {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    margin-top: 8px;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    background: #0aa289;
    transition: width 0.3s;
}

.questions-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 60px 20px 20px;
}

.question-card {
    background: white;
    border-radius: 16px;
    padding: 25px 30px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: all 0.2s;
}
.question-card.answered {
    border-left: 4px solid #0aa289;
}
.question-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}
.question-number {
    font-size: 0.85rem;
    color: #64748b;
}
.domain-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    background: #0aa289;
    color: white;
    font-size: 0.75rem;
    font-weight: 500;
}
.question-text {
    font-size: 1.05rem;
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 20px;
    line-height: 1.5;
}
.answer-options {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.answer-option {
    padding: 15px 10px;
    text-align: center;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}
.answer-option:hover {
    border-color: #0aa289;
    background: #f0fdf9;
}
.answer-option.selected {
    border-color: #0aa289;
    background: #0aa289;
    color: white;
}
.answer-option.selected .answer-value { color: white; }
.answer-option.selected .answer-label { color: rgba(255,255,255,0.9); }
.answer-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
}
.answer-label {
    font-size: 0.7rem;
    color: #64748b;
    line-height: 1.2;
}

.submit-section {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 15px 20px;
    box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    z-index: 1000;
}
.submit-info {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 10px;
}
.btn-submit {
    display: inline-block;
    background: #0aa289;
    color: white;
    padding: 14px 50px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-submit:hover:not(:disabled) {
    background: #088270;
    transform: translateY(-2px);
}
.btn-submit:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    transform: none;
}
.btn-submit.loading {
    pointer-events: none;
}

/* Domain Filter */
.domain-filter {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 25px;
    padding: 15px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.domain-filter-btn {
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #475569;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.domain-filter-btn:hover {
    border-color: #0aa289;
    color: #0aa289;
}
.domain-filter-btn.active {
    background: #0aa289;
    border-color: #0aa289;
    color: white;
}

@media (max-width: 768px) {
    .quiz-timer,
    .quiz-progress {
        position: fixed;
        top: auto;
        bottom: 80px;
        left: 10px;
        right: auto;
        padding: 10px 15px;
        min-width: auto;
    }
    .quiz-timer {
        left: auto;
        right: 10px;
    }
    .timer-display,
    .progress-display {
        font-size: 1.25rem;
    }
    .answer-options {
        grid-template-columns: repeat(2, 1fr);
    }
    .questions-container {
        padding: 20px 15px;
    }
    .question-card {
        padding: 20px;
    }
    .submit-section {
        padding-bottom: 20px;
    }
}
</style>
@endsection

@section('content')
<div class="quiz-wrapper">
    {{-- Timer --}}
    <div class="quiz-timer" id="quizTimer">
        <div class="timer-label">Thời gian còn lại</div>
        <div class="timer-display" id="timerDisplay">20:00</div>
    </div>

    {{-- Progress --}}
    <div class="quiz-progress">
        <div class="progress-label">Tiến độ</div>
        <div class="progress-display">
            <span id="answeredCount">0</span>/{{ $totalQuestions }}
        </div>
        <div class="progress-bar-mini">
            <div class="progress-bar-fill" id="progressBar" style="width: 0%"></div>
        </div>
    </div>

    <div class="questions-container">
        {{-- Domain Filter --}}
        <div class="domain-filter">
            <button class="domain-filter-btn active" data-domain="all">Tất cả</button>
            @php
                $uniqueDomains = collect($questions)->unique('domain_key');
            @endphp
            @foreach($uniqueDomains as $q)
                <button class="domain-filter-btn" data-domain="{{ $q['domain_key'] }}">
                    {{ $q['domain_name'] }}
                </button>
            @endforeach
        </div>

        {{-- Questions --}}
        <div id="questionsContainer">
            @foreach($questions as $index => $question)
                <div class="question-card {{ isset($answeredQuestions[$question['id']]) ? 'answered' : '' }}"
                     data-question-id="{{ $question['id'] }}"
                     data-domain="{{ $question['domain_key'] }}">
                    <div class="question-header">
                        <span class="question-number">Câu {{ $index + 1 }}/{{ $totalQuestions }}</span>
                        <span class="domain-badge">{{ $question['domain_name'] }}</span>
                    </div>
                    <div class="question-text">{{ $question['question'] }}</div>
                    <div class="answer-options">
                        @for($v = 0; $v <= 3; $v++)
                            @php
                                $labels = [
                                    0 => 'Chưa làm được',
                                    1 => 'Hiếm khi',
                                    2 => 'Thường xuyên',
                                    3 => 'Ổn định',
                                ];
                                $isSelected = isset($answeredQuestions[$question['id']]) && $answeredQuestions[$question['id']] == $v;
                            @endphp
                            <div class="answer-option {{ $isSelected ? 'selected' : '' }}"
                                 data-value="{{ $v }}"
                                 onclick="selectAnswer({{ $question['id'] }}, {{ $v }}, this)">
                                <div class="answer-value">{{ $v }}</div>
                                <div class="answer-label">{{ $labels[$v] }}</div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Submit Section --}}
    <div class="submit-section">
        <div class="submit-info">
            Cần trả lời ít nhất 30 câu để nộp bài
        </div>
        <button id="btnSubmit" class="btn-submit" disabled onclick="submitQuiz()">
            Nộp bài (<span id="submitCount">0</span>/{{ $totalQuestions }})
        </button>
    </div>
</div>

<script>
const attemptId = @json($attemptId);
const startedAt = new Date(@json($startedAt));
const timeoutSeconds = @json($timeoutSeconds);
const minTimeSeconds = @json($minTimeSeconds);
const totalQuestions = @json($totalQuestions);

let answers = @json($answeredQuestions);
let timerInterval;
let isSubmitting = false;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateProgress();
    startTimer();
    initDomainFilter();
});

function initDomainFilter() {
    const buttons = document.querySelectorAll('.domain-filter-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            buttons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const domain = this.dataset.domain;
            const cards = document.querySelectorAll('.question-card');

            cards.forEach(card => {
                if (domain === 'all' || card.dataset.domain === domain) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

function selectAnswer(questionId, value, element) {
    // Visual feedback
    const card = element.closest('.question-card');
    card.querySelectorAll('.answer-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
    card.classList.add('answered');

    // Store answer
    answers[questionId] = value;
    updateProgress();

    // Send to API
    saveAnswer(questionId, value);
}

async function saveAnswer(questionId, value) {
    try {
        const response = await fetch('/api/skill-quiz/answer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                attempt_id: attemptId,
                question_id: questionId,
                answer_value: value,
                time_spent_seconds: 0
            })
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.data && data.data.auto_submitted) {
                // Quiz timed out
                window.location.href = '/skill-quiz/result/' + attemptId;
                return;
            }
            console.error('Error saving answer:', data.message);
        }
    } catch (error) {
        console.error('Error saving answer:', error);
    }
}

function updateProgress() {
    const count = Object.keys(answers).length;
    document.getElementById('answeredCount').textContent = count;
    document.getElementById('submitCount').textContent = count;

    const percent = (count / totalQuestions) * 100;
    document.getElementById('progressBar').style.width = percent + '%';

    document.getElementById('btnSubmit').disabled = count < 30;
}

function startTimer() {
    const updateTimer = () => {
        const now = new Date();
        const elapsed = Math.floor((now - startedAt) / 1000);
        const remaining = Math.max(0, timeoutSeconds - elapsed);

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        document.getElementById('timerDisplay').textContent =
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        const timerEl = document.getElementById('quizTimer');
        timerEl.classList.remove('warning', 'danger');

        if (remaining <= 120) {
            timerEl.classList.add('danger');
        } else if (remaining <= 300) {
            timerEl.classList.add('warning');
        }

        if (remaining <= 0) {
            clearInterval(timerInterval);
            submitQuiz(true);
        }
    };

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
}

async function submitQuiz(isAutoSubmit = false) {
    if (isSubmitting) return;

    const count = Object.keys(answers).length;

    if (!isAutoSubmit && count < 30) {
        alert('Bạn cần trả lời ít nhất 30 câu hỏi');
        return;
    }

    if (!isAutoSubmit) {
        const now = new Date();
        const elapsed = Math.floor((now - startedAt) / 1000);

        if (elapsed < minTimeSeconds) {
            const remaining = Math.ceil((minTimeSeconds - elapsed) / 60);
            if (!confirm(`Bạn đang làm quá nhanh (chưa đủ 3 phút). Điều này có thể ảnh hưởng đến điểm số. Bạn có muốn tiếp tục nộp bài?`)) {
                return;
            }
        }

        if (!confirm('Bạn có chắc muốn nộp bài? Không thể thay đổi sau khi nộp.')) {
            return;
        }
    }

    isSubmitting = true;
    clearInterval(timerInterval);

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.classList.add('loading');
    btn.innerHTML = 'Đang xử lý...';

    try {
        const response = await fetch('/api/skill-quiz/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ attempt_id: attemptId })
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = '/skill-quiz/result/' + attemptId;
        } else {
            alert(data.message || 'Lỗi khi nộp bài. Vui lòng thử lại.');
            isSubmitting = false;
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.innerHTML = `Nộp bài (<span id="submitCount">${count}</span>/${totalQuestions})`;
            startTimer();
        }
    } catch (error) {
        console.error('Submit error:', error);
        alert('Lỗi kết nối. Vui lòng thử lại.');
        isSubmitting = false;
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.innerHTML = `Nop bai (<span id="submitCount">${count}</span>/${totalQuestions})`;
        startTimer();
    }
}

// Warn before leaving page
window.addEventListener('beforeunload', function(e) {
    if (!isSubmitting && Object.keys(answers).length > 0) {
        e.preventDefault();
        e.returnValue = 'Bạn có câu trả lời chưa được lưu. Bạn có chắc muốn rời khỏi trang?';
        return e.returnValue;
    }
});
</script>
@endsection
