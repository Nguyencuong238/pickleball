@extends('layouts.front')

@section('title', 'Nộp Thử Thách - ' . $challengeInfo['name'])

@section('css')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        padding: 3rem 0;
        color: white;
        margin-top: 100px;
    }

    .page-header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .page-breadcrumb {
        font-size: 0.875rem;
        opacity: 0.8;
    }

    .page-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .page-breadcrumb a:hover {
        text-decoration: underline;
    }

    .submit-section {
        padding: 2rem 0;
    }

    .submit-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .submit-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 2rem;
    }

    .challenge-header {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .challenge-icon {
        font-size: 2rem;
    }

    .challenge-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.25rem 0;
    }

    .challenge-desc {
        color: #64748b;
        margin: 0;
    }

    .info-box {
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-box.requirements {
        background: #dbeafe;
    }

    .info-box.reward {
        background: #dcfce7;
    }

    .info-box h3 {
        font-size: 0.875rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
    }

    .info-box.requirements h3 {
        color: #1e40af;
    }

    .info-box.reward h3 {
        color: #166534;
    }

    .info-box p {
        margin: 0;
    }

    .info-box.requirements p {
        color: #1d4ed8;
    }

    .info-box.reward p {
        color: #15803d;
    }

    .info-box .hint {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .info-box.requirements .hint {
        color: #3b82f6;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-input.error {
        border-color: #ef4444;
    }

    .form-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        resize: vertical;
        min-height: 80px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-error {
        font-size: 0.875rem;
        color: #ef4444;
        margin-top: 0.25rem;
    }

    .submit-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .submit-btn:hover {
        background: linear-gradient(90deg, #2563eb, #1e40af);
    }

    .submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .submit-card {
            padding: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <div>
                <p class="page-breadcrumb">
                    <a href="{{ route('ocr.index') }}">OCR</a> /
                    <a href="{{ route('ocr.challenges.index') }}">Trung Tâm Thử Thách</a> /
                    Nộp Bài
                </p>
                <h1 class="page-title">Nộp Thử Thách</h1>
            </div>
            <a href="{{ route('ocr.challenges.index') }}" class="btn btn-outline" style="color: white">
                ← Quay Lại Thử Thách
            </a>
        </div>
    </div>
</section>

<section class="submit-section">
    <div class="container">
        <div class="submit-container">
            <div class="submit-card">
                <div class="challenge-header">
                    <span class="challenge-icon">{{ $challengeInfo['icon'] ?? '⭐' }}</span>
                    <div>
                        <h1 class="challenge-title">{{ $challengeInfo['name'] }}</h1>
                        <p class="challenge-desc">{{ $challengeInfo['description'] }}</p>
                    </div>
                </div>

                {{-- Requirements --}}
                <div class="info-box requirements">
                    <h3>Yêu Cầu</h3>
                    @switch($challengeType)
                        @case('dinking_rally')
                            <p>Rally liên tục 20 lần không lỗi để vượt qua</p>
                            <p class="hint">Nhập số lần rally liên tiếp bạn đã đạt được</p>
                            @break
                        @case('drop_shot')
                            <p>Đạt 5/10 drop shot vào vùng kitchen để vượt qua</p>
                            <p class="hint">Nhập số drop shot thành công (trên 10 lần)</p>
                            @break
                        @case('serve_accuracy')
                            <p>Đạt 7/10 serve vào vùng mục tiêu để vượt qua</p>
                            <p class="hint">Nhập số serve thành công (trên 10 lần)</p>
                            @break
                        @case('monthly_test')
                            <p>Đạt điểm 70+ để vượt qua bài test kỹ thuật hàng tháng</p>
                            <p class="hint">Nhập điểm test của bạn (0-100)</p>
                            @break
                    @endswitch
                </div>

                {{-- Reward --}}
                <div class="info-box reward">
                    <h3>Phần Thưởng</h3>
                    <p>
                        @if(is_array($challengeInfo['points']))
                            {{ $challengeInfo['points']['min'] }} - {{ $challengeInfo['points']['max'] }} Điểm Thử Thách
                        @else
                            +{{ $challengeInfo['points'] }} Điểm Thử Thách
                        @endif
                    </p>
                </div>

                {{-- Submit Form --}}
                <form action="{{ route('ocr.challenges.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="challenge_type" value="{{ $challengeType }}">

                    <div class="form-group">
                        <label for="score" class="form-label">Điểm Của Bạn</label>
                        <input type="number"
                               id="score"
                               name="score"
                               min="0"
                               max="100"
                               required
                               class="form-input @error('score') error @enderror"
                               placeholder="Nhập điểm của bạn"
                               value="{{ old('score') }}">
                        @error('score')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Ghi Chú (không bắt buộc)</label>
                        <textarea id="notes"
                                  name="notes"
                                  class="form-textarea"
                                  placeholder="Ghi chú thêm...">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        Nộp Thử Thách
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
