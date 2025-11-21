@extends('layouts.front')

@section('content')
<style>
    @media (min-width: 768px) {
        .page-header {
            margin-top: 80px;
        }
    }

    .steps-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
        position: relative;
    }

    .steps-container::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e2e8f0;
        z-index: 0;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f3f4f6;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: 700;
        color: #9ca3af;
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: var(--primary-color);
    }

    .step.completed .step-number {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }

    .step-title {
        font-size: 0.875rem;
        color: #9ca3af;
        margin: 0;
        font-weight: 600;
    }

    .step.active .step-title {
        color: var(--primary-color);
    }

    .step.completed .step-title {
        color: #10b981;
    }

    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
    }

    .step-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-prev, .btn-next {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-prev {
        background: #f3f4f6;
        color: #1e293b;
    }

    .btn-prev:hover:not(:disabled) {
        background: #e2e8f0;
    }

    .btn-next {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .btn-next:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-prev:disabled, .btn-next:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 80px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        <a href="{{ route('homeyard.dashboard') }}" style="color: rgba(255, 255, 255, 0.9); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
        <h1 style="color: white; font-size: clamp(1.75rem, 5vw, 2.5rem); font-weight: 700; margin: 0; line-height: 1.2; word-break: break-word;">Cấu Hình Giải Đấu: {{ $tournament->name }}</h1>
    </div>
</div>

<div style="background: #f9fafb; padding: 50px 20px; min-height: 60vh;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        <!-- Steps Navigation -->
        <div class="steps-container">
            <div class="step completed" data-step="2">
                <div class="step-number">2</div>
                <p class="step-title">Cấu Trúc Giải</p>
            </div>
            <div class="step active" data-step="3">
                <div class="step-number">3</div>
                <p class="step-title">Nội Dung Thi Đấu</p>
            </div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <p class="step-title">Sân Thi Đấu</p>
            </div>
        </div>

        <!-- Form Container -->
        <div style="background: white; border-radius: 15px; padding: clamp(20px, 5vw, 30px); box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            @if($errors->any())
                <div style="background: #fee2e2; color: #991b1b; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #991b1b;">
                    <strong>Lỗi Xác Thực:</strong>
                    <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="tournamentForm" method="POST" action="{{ route('homeyard.tournaments.update', $tournament) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Step 2: Cấu Trúc Giải (Tournament Structure) -->
                <div class="step-content" data-step="2">
                    <h3 style="color: #1e293b; margin-bottom: 20px; font-size: 1.25rem;">Cấu Trúc Giải Đấu</h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hình Thức Thi Đấu *</label>
                            <select name="competition_format" class="form-control" required
                                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                                <option value="">-- Chọn --</option>
                                <option value="single" {{ (isset($tournament) && $tournament->competition_format === 'single') || old('competition_format') === 'single' ? 'selected' : '' }}>Đơn</option>
                                <option value="double" {{ (isset($tournament) && $tournament->competition_format === 'double') || old('competition_format') === 'double' ? 'selected' : '' }}>Đôi</option>
                                <option value="mixed" {{ (isset($tournament) && $tournament->competition_format === 'mixed') || old('competition_format') === 'mixed' ? 'selected' : '' }}>Hỗn Hợp</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hạng Giải *</label>
                            <select name="tournament_rank" class="form-control" required
                                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                                <option value="">-- Chọn --</option>
                                <option value="beginner" {{ (isset($tournament) && $tournament->tournament_rank === 'beginner') || old('tournament_rank') === 'beginner' ? 'selected' : '' }}>Sơ Cấp</option>
                                <option value="intermediate" {{ (isset($tournament) && $tournament->tournament_rank === 'intermediate') || old('tournament_rank') === 'intermediate' ? 'selected' : '' }}>Trung Cấp</option>
                                <option value="advanced" {{ (isset($tournament) && $tournament->tournament_rank === 'advanced') || old('tournament_rank') === 'advanced' ? 'selected' : '' }}>Cao Cấp</option>
                                <option value="professional" {{ (isset($tournament) && $tournament->tournament_rank === 'professional') || old('tournament_rank') === 'professional' ? 'selected' : '' }}>Chuyên Nghiệp</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Số VĐV Tối Đa *</label>
                            <input type="number" name="max_participants" class="form-control" value="{{ $tournament->max_participants ?? old('max_participants', 32) }}" min="1" required
                                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Lịch Thi Đấu Chi Tiết</label>
                        <textarea name="event_timeline" class="form-control" rows="5"
                            style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->event_timeline ?? old('event_timeline') }}</textarea>
                    </div>
                </div>

                <!-- Step 3: Nội Dung Thi Đấu (Competition Content) -->
                <div class="step-content active" data-step="3">
                    <h3 style="color: #1e293b; margin-bottom: 20px; font-size: 1.25rem;">Nội Dung Thi Đấu</h3>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Quy Định Thi Đấu Chi Tiết *</label>
                        <textarea name="competition_rules" class="form-control" rows="5" required
                            style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->competition_rules ?? old('competition_rules') }}</textarea>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Quyền Lợi Khi Tham Gia</label>
                        <textarea name="registration_benefits" class="form-control" rows="4"
                            style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->registration_benefits ?? old('registration_benefits') }}</textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Giải Thưởng (VNĐ)</label>
                            <input type="number" name="prizes" class="form-control" value="{{ $tournament->prizes ?? old('prizes') }}" step="0.01" min="0"
                                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Lệ Phí Đăng Ký (VNĐ)</label>
                            <input type="number" name="price" class="form-control" value="{{ $tournament->price ?? old('price', 0) }}" step="0.01" min="0"
                                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mô Tả Giải Đấu</label>
                        <textarea name="description" class="form-control" rows="4"
                            style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $tournament->description ?? old('description') }}</textarea>
                    </div>
                </div>

                <!-- Step 4: Sân Thi Đấu (Courts) -->
                <div class="step-content" data-step="4">
                    <h3 style="color: #1e293b; margin-bottom: 20px; font-size: 1.25rem;">Sân Thi Đấu</h3>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Địa Điểm Thi Đấu *</label>
                        <input type="text" name="location" class="form-control" value="{{ $tournament->location ?? old('location') }}" required
                            style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ngày Bắt Đầu *</label>
                            <input type="date" name="start_date" class="form-control" value="{{ isset($tournament) ? $tournament->start_date->format('Y-m-d') : old('start_date') }}" required
                                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ngày Kết Thúc</label>
                            <input type="date" name="end_date" class="form-control" value="{{ isset($tournament) && $tournament->end_date ? $tournament->end_date->format('Y-m-d') : old('end_date') }}"
                                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hạn Đăng Ký</label>
                            <input type="datetime-local" name="registration_deadline" class="form-control" value="{{ isset($tournament) && $tournament->registration_deadline ? $tournament->registration_deadline->format('Y-m-d\TH:i') : old('registration_deadline') }}"
                                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <p style="color: #6b7280; margin-bottom: 15px;">Vui lòng <a href="{{ route('homeyard.courts') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">cấu hình sân thi đấu</a> trong bước này.</p>
                    </div>
                </div>

                <!-- Step Actions -->
                <div class="step-actions">
                    <button type="button" class="btn-prev" id="prevBtn" onclick="previousStep()" style="display: none;">← Quay Lại</button>
                    <div></div>
                    <button type="button" class="btn-next" id="nextBtn" onclick="nextStep()">Tiếp Theo →</button>
                    <button type="submit" class="btn-next" id="submitBtn" style="display: none; background: #10b981;">💾 Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentStep = 3; // Start at step 3

    const steps = [2, 3, 4];

    function showStep(stepNumber) {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));

        // Show current step
        document.querySelector(`.step-content[data-step="${stepNumber}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${stepNumber}"]`).classList.add('active');

        // Update button visibility
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        if (currentStep === steps[0]) {
            prevBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'block';
        }

        if (currentStep === steps[steps.length - 1]) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'block';
        } else {
            nextBtn.style.display = 'block';
            submitBtn.style.display = 'none';
        }

        // Update step indicators
        steps.forEach((step, index) => {
            const stepEl = document.querySelector(`.step[data-step="${step}"]`);
            if (step < currentStep) {
                stepEl.classList.add('completed');
                stepEl.classList.remove('active');
            } else if (step === currentStep) {
                stepEl.classList.add('active');
                stepEl.classList.remove('completed');
            } else {
                stepEl.classList.remove('completed', 'active');
            }
        });
    }

    function nextStep() {
        const currentIndex = steps.indexOf(currentStep);
        if (currentIndex < steps.length - 1) {
            currentStep = steps[currentIndex + 1];
            showStep(currentStep);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function previousStep() {
        const currentIndex = steps.indexOf(currentStep);
        if (currentIndex > 0) {
            currentStep = steps[currentIndex - 1];
            showStep(currentStep);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        showStep(currentStep);
    });
</script>
@endsection
