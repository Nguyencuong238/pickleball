@extends('layouts.front')

@section('css')
    <style>
        .quiz-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 40px 0;
            margin-top: 100px;
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .quiz-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .quiz-header p {
            font-size: 1.1rem;
            color: #666;
        }

        .category-filter {
            margin-bottom: 30px;
        }

        .category-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .category-btn {
            padding: 10px 20px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #333;
        }

        .category-btn:hover {
            border-color: #0aa289;
            color: #0aa289;
        }

        .category-btn.active {
            background: #0aa289;
            color: white;
            border-color: #0aa289;
        }

        .quiz-main {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .quiz-settings {
            margin-bottom: 30px;
        }

        .setting-group {
            margin-bottom: 20px;
        }

        .setting-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .setting-group select,
        .setting-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .setting-group select:focus,
        .setting-group input:focus {
            outline: none;
            border-color: #0aa289;
            box-shadow: 0 0 5px rgba(10, 162, 137, 0.3);
        }

        .quiz-questions {
            display: none;
            margin-bottom: 30px;
        }

        .quiz-questions.active {
            display: block;
        }

        .question-item {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            border-left: 4px solid #0aa289;
        }

        .question-item .question-text {
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            font-size: 1.05rem;
        }

        .question-item .question-number {
            display: inline-block;
            background: #0aa289;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            text-align: center;
            line-height: 20px;
            font-size: 0.9rem;
            margin-right: 10px;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .option {
            display: flex;
            align-items: center;
            padding: 12px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option:hover {
            border-color: #0aa289;
            background: #f0f9f7;
        }

        .option input[type="radio"] {
            margin-right: 12px;
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        .option input[type="radio"]:checked + .option-label {
            color: #0aa289;
            font-weight: 600;
        }

        .option.selected {
            border-color: #0aa289;
            background: #f0f9f7;
        }

        .option-label {
            flex: 1;
            cursor: pointer;
            color: #333;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-start,
        .btn-submit,
        .btn-reset {
            padding: 12px 40px;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-start,
        .btn-submit {
            background: #0aa289;
            color: white;
        }

        .btn-start:hover,
        .btn-submit:hover {
            background: #088270;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(10, 162, 137, 0.3);
        }

        .btn-reset {
            background: #e0e0e0;
            color: #333;
        }

        .btn-reset:hover {
            background: #d0d0d0;
        }

        .btn-start:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .results {
            display: none;
        }

        .results.show {
            display: block;
        }

        .score-card {
            text-align: center;
            padding: 40px;
            background: linear-gradient(135deg, #0aa289 0%, #088270 100%);
            color: white;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .score-card .score-number {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .score-card .score-text {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .score-card .score-detail {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .result-item {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            border-left: 4px solid #ddd;
        }

        .result-item.correct {
            border-left-color: #4caf50;
            background: #f1f8f4;
        }

        .result-item.incorrect {
            border-left-color: #f44336;
            background: #fef3f2;
        }

        .result-question {
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .result-answer {
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .result-answer.user-answer {
            color: #333;
        }

        .result-answer.correct-answer {
            color: #4caf50;
            font-weight: 500;
        }

        .result-answer.incorrect-answer {
            color: #f44336;
            font-weight: 500;
        }

        .result-explanation {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            color: #666;
            border-left: 3px solid #ffc107;
        }

        .loading {
            text-align: center;
            padding: 40px;
            display: none;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0aa289;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        @media (max-width: 768px) {
            .quiz-header h1 {
                font-size: 1.8rem;
            }

            .quiz-main {
                padding: 20px;
            }

            .category-buttons {
                flex-direction: column;
            }

            .category-btn {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-start,
            .btn-submit,
            .btn-reset {
                width: 100%;
            }

            .score-card .score-number {
                font-size: 2.5rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="quiz-container">
        <div class="container">
            <!-- Header -->
            <div class="quiz-header">
                <h1>Quiz Pickleball</h1>
                <p>Kiểm tra kiến thức của bạn về pickleball</p>
            </div>

            <!-- Alert Messages -->
            <div class="alert alert-error" id="errorAlert"></div>

            <!-- Category Filter -->
            <div class="category-filter">
                <div class="category-buttons">
                    <button class="category-btn active" data-category="all">Tất cả</button>
                    @foreach($categories as $category)
                        <button class="category-btn" data-category="{{ $category }}">{{ $category }}</button>
                    @endforeach
                </div>
            </div>

            <!-- Quiz Main Section -->
            <div class="quiz-main">
                <!-- Settings (Hiển thị khi chưa bắt đầu) -->
                <div id="quizSettings">
                    <h2 style="margin-bottom: 20px; color: #333;">Cài đặt bài quiz</h2>
                    <div class="quiz-settings">
                        <div class="setting-group">
                            <label for="questionCount">Số câu hỏi:</label>
                            <input type="number" id="questionCount" min="5" max="50" value="10">
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button class="btn-start" id="btnStart">Bắt đầu làm bài</button>
                    </div>
                </div>

                <!-- Loading -->
                <div class="loading" id="loadingContent">
                    <div class="spinner"></div>
                    <p>Đang tải câu hỏi...</p>
                </div>

                <!-- Questions Form -->
                <form id="quizForm" class="quiz-questions">
                    <div id="questionsContainer"></div>
                    <div class="action-buttons">
                        <button type="submit" class="btn-submit">Nộp bài</button>
                        <button type="reset" class="btn-reset">Làm lại</button>
                    </div>
                </form>

                <!-- Results -->
                <div class="results" id="resultsContainer">
                    <div class="score-card">
                        <div class="score-number" id="scoreNumber">0%</div>
                        <div class="score-text" id="scoreMessage">Kết quả của bạn</div>
                        <div class="score-detail" id="scoreDetail">0/0 câu đúng</div>
                    </div>
                    <div id="resultsDetails"></div>
                    <div class="action-buttons">
                        <button class="btn-start" id="btnRetry">Làm lại</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const categoryFilter = document.querySelectorAll('.category-btn');
        const btnStart = document.getElementById('btnStart');
        const btnRetry = document.getElementById('btnRetry');
        const quizForm = document.getElementById('quizForm');
        const quizSettings = document.getElementById('quizSettings');
        const quizQuestions = document.querySelector('.quiz-questions');
        const questionsContainer = document.getElementById('questionsContainer');
        const resultsContainer = document.getElementById('resultsContainer');
        const loadingContent = document.getElementById('loadingContent');
        const questionCountInput = document.getElementById('questionCount');
        const errorAlert = document.getElementById('errorAlert');

        let selectedCategory = 'all';
        let quizData = [];

        // Category filter
        categoryFilter.forEach(btn => {
            btn.addEventListener('click', function() {
                categoryFilter.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedCategory = this.getAttribute('data-category');
            });
        });

        // Start Quiz
        btnStart.addEventListener('click', async function() {
            const questionCount = questionCountInput.value;

            if (!questionCount || questionCount < 5) {
                showError('Vui lòng chọn số câu hỏi từ 5 trở lên');
                return;
            }

            loadingContent.classList.add('show');
            quizSettings.style.display = 'none';

            try {
                const response = await fetch(`{{ route('quiz.questions') }}?category=${selectedCategory}&limit=${questionCount}`);
                const data = await response.json();

                if (!data.success || data.data.length === 0) {
                    showError('Không có câu hỏi nào. Vui lòng chọn danh mục khác.');
                    resetQuiz();
                    return;
                }

                quizData = data.data;
                renderQuestions(quizData);
                loadingContent.classList.remove('show');
                quizQuestions.classList.add('active');
            } catch (error) {
                console.error('Error:', error);
                showError('Lỗi khi tải câu hỏi. Vui lòng thử lại.');
                resetQuiz();
            }
        });

        // Render Questions
        function renderQuestions(questions) {
            questionsContainer.innerHTML = '';

            questions.forEach((question, index) => {
                const questionHTML = `
                    <div class="question-item">
                        <div class="question-text">
                            <span class="question-number">${index + 1}</span>
                            ${question.question}
                        </div>
                        <div class="options">
                            ${Object.keys(question.options).map((key) => `
                                <label class="option">
                                    <input type="radio" name="answer_${question.id}" value="${key}">
                                    <span class="option-label">${question.options[key]}</span>
                                </label>
                            `).join('')}
                        </div>
                    </div>
                `;
                questionsContainer.insertAdjacentHTML('beforeend', questionHTML);
            });

            // Add radio button change listeners
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const option = this.closest('.option');
                    const otherOptions = option.parentElement.querySelectorAll('.option');
                    otherOptions.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                });
            });
        }

        // Submit Quiz
        quizForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const answers = {};
            const formData = new FormData(quizForm);

            for (let [key, value] of formData.entries()) {
                const quizId = key.replace('answer_', '');
                answers[quizId] = value;
            }

            if (Object.keys(answers).length === 0) {
                showError('Vui lòng trả lời ít nhất một câu hỏi');
                return;
            }

            loadingContent.classList.add('show');
            quizQuestions.classList.remove('active');

            try {
                const response = await fetch('{{ route('quiz.submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ answers })
                });

                const data = await response.json();

                if (!data.success) {
                    showError(data.message || 'Lỗi khi nộp bài');
                    quizQuestions.classList.add('active');
                    loadingContent.classList.remove('show');
                    return;
                }

                displayResults(data);
                loadingContent.classList.remove('show');
                resultsContainer.classList.add('show');
            } catch (error) {
                console.error('Error:', error);
                showError('Lỗi khi nộp bài. Vui lòng thử lại.');
                quizQuestions.classList.add('active');
                loadingContent.classList.remove('show');
            }
        });

        // Display Results
        function displayResults(data) {
            document.getElementById('scoreNumber').textContent = data.score + '%';
            document.getElementById('scoreDetail').textContent = `${data.correctCount}/${data.totalCount} câu đúng`;

            const scoreMessage = data.score >= 80 ? '🎉 Tuyệt vời!' :
                                 data.score >= 60 ? '👍 Tốt lắm!' :
                                 data.score >= 40 ? '📚 Còn cần cố gắng' : '💪 Tiếp tục nỗ lực';

            document.getElementById('scoreMessage').textContent = scoreMessage;

            const resultsDetailsHTML = data.results.map((result) => `
                <div class="result-item ${result.isCorrect ? 'correct' : 'incorrect'}">
                    <div class="result-question">${result.question}</div>
                    <div class="result-answer user-answer">
                        <strong>Câu trả lời của bạn:</strong> ${result.options[result.userAnswer] || 'Không trả lời'}
                    </div>
                    ${!result.isCorrect ? `
                        <div class="result-answer correct-answer">
                            <strong>Câu trả lời đúng:</strong> ${result.options[result.correctAnswer]}
                        </div>
                    ` : ''}
                    ${result.explanation ? `
                        <div class="result-explanation">
                            <strong>Giải thích:</strong> ${result.explanation}
                        </div>
                    ` : ''}
                </div>
            `).join('');

            document.getElementById('resultsDetails').innerHTML = resultsDetailsHTML;
        }

        // Retry Quiz
        btnRetry.addEventListener('click', function() {
            resetQuiz();
        });

        // Reset Quiz
        function resetQuiz() {
            quizForm.reset();
            questionsContainer.innerHTML = '';
            resultsContainer.classList.remove('show');
            quizQuestions.classList.remove('active');
            quizSettings.style.display = 'block';
            errorAlert.classList.remove('show');
            quizData = [];
            questionCountInput.value = '10';
        }

        // Show Error
        function showError(message) {
            errorAlert.textContent = message;
            errorAlert.classList.add('show');
            setTimeout(() => {
                errorAlert.classList.remove('show');
            }, 5000);
        }

        // CSRF Token
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.querySelector('meta[name="csrf-token"]')) {
                const meta = document.createElement('meta');
                meta.setAttribute('name', 'csrf-token');
                meta.setAttribute('content', document.querySelector('input[name="_token"]')?.value || '');
                document.head.appendChild(meta);
            }
        });
    </script>
@endsection
