@extends('layouts.front')

@section('title', 'Trạng thái xác minh OPR')

@section('css')
<style>
    .status-container {
        max-width: 700px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .status-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 2rem;
        text-align: center;
    }

    .status-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .status-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .status-subtitle {
        color: #64748b;
        margin-bottom: 1.5rem;
    }

    .status-pending .status-icon { color: #f59e0b; }
    .status-approved .status-icon { color: #10b981; }
    .status-rejected .status-icon { color: #ef4444; }

    .status-details {
        background: #f8fafc;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-top: 1.5rem;
        text-align: left;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #64748b;
    }

    .detail-value {
        font-weight: 600;
    }

    .verifier-notes {
        margin-top: 1.5rem;
        padding: 1.5rem;
        background: #fef3c7;
        border-radius: 0.5rem;
        text-align: left;
    }

    .verifier-notes h4 {
        margin: 0 0 0.5rem 0;
        color: #92400e;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1.5rem;
    }

    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(135deg, #00D9B5, #0099CC);
        color: white;
    }

    .btn-outline {
        background: white;
        border: 2px solid #e2e8f0;
        color: #1e293b;
    }
</style>
@endsection

@section('content')
<div class="status-container">
    <div class="status-card status-{{ $verificationRequest->status }}">
        @if($verificationRequest->isPending())
            <div class="status-icon">⏳</div>
            <h1 class="status-title">Đang chờ duyệt</h1>
            <p class="status-subtitle">Yêu cầu xác minh của bạn đang được xem xét</p>
        @elseif($verificationRequest->isApproved())
            <div class="status-icon">✅</div>
            <h1 class="status-title">Đã được xác minh</h1>
            <p class="status-subtitle">Chúc mừng! ELO của bạn đã được xác minh chính thức</p>
        @else
            <div class="status-icon">❌</div>
            <h1 class="status-title">Yêu cầu bị từ chối</h1>
            <p class="status-subtitle">Yêu cầu xác minh của bạn không được chấp thuận</p>
        @endif

        <div class="status-details">
            <div class="detail-row">
                <span class="detail-label">Ngày gửi</span>
                <span class="detail-value">{{ $verificationRequest->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Trạng thái</span>
                <span class="detail-value">{{ $verificationRequest->getStatusLabel() }}</span>
            </div>
            @if($verificationRequest->verifier)
            <div class="detail-row">
                <span class="detail-label">Người duyệt</span>
                <span class="detail-value">{{ $verificationRequest->verifier->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Ngày xử lý</span>
                <span class="detail-value">{{ $verificationRequest->verified_at?->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>

        @if($verificationRequest->isRejected() && $verificationRequest->verifier_notes)
        <div class="verifier-notes">
            <h4>Lý do từ chối:</h4>
            <p style="margin: 0;">{{ $verificationRequest->verifier_notes }}</p>
        </div>
        @endif

        <div class="action-buttons">
            <a href="{{ route('ocr.profile', $user) }}" class="btn-action btn-outline">
                Xem hồ sơ OPR
            </a>
            @if($verificationRequest->isRejected())
                <a href="{{ route('opr-verification.create') }}" class="btn-action btn-primary">
                    Gửi yêu cầu mới
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
