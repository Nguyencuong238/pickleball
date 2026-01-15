@extends('layouts.front')

@section('title', 'Các Yêu Cầu Của Tôi')

@section('css')
<style>
    .submissions-header {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .submission-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 1rem;
    }
    .submission-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .submission-card.pending {
        border-left: 4px solid #ffc107;
    }
    .submission-card.approved {
        border-left: 4px solid #198754;
    }
    .submission-card.rejected {
        border-left: 4px solid #dc3545;
    }
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .proof-info {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .admin-notes {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.75rem;
        margin-top: 0.5rem;
        font-size: 0.9rem;
    }
    .submission-date {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="container py-4 page-content">
    {{-- Header --}}
    <div class="submissions-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h3 mb-1">Các Yêu Cầu Của Tôi</h1>
                <p class="text-muted mb-0">Theo dõi trạng thái các yêu cầu kiếm điểm</p>
            </div>
            <a href="{{ route('user.points.index') }}" class="btn btn-outline-primary btn-sm">← Quay lại Kiếm Điểm</a>
        </div>
    </div>

    {{-- Submissions List --}}
    @forelse($submissions as $submission)
        @php
            $statusClass = match($submission->status) {
                'pending' => 'pending',
                'approved' => 'approved',
                'rejected' => 'rejected',
                default => '',
            };
        @endphp
        <div class="card submission-card {{ $statusClass }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h6 class="card-title mb-0">{{ $submission->pointTask->name ?? 'N/A' }}</h6>
                            @if($submission->isPending())
                                <span class="status-badge bg-warning text-dark">⏳ Đang chờ</span>
                            @elseif($submission->isApproved())
                                <span class="status-badge bg-success text-white">✅ Đã duyệt</span>
                            @else
                                <span class="status-badge bg-danger text-white">❌ Từ chối</span>
                            @endif
                        </div>

                        <p class="submission-date mb-2">
                            Gửi ngày: {{ $submission->created_at->format('d/m/Y H:i') }}
                            @if($submission->reviewed_at)
                                <span class="mx-1">|</span>
                                Duyệt ngày: {{ $submission->reviewed_at->format('d/m/Y H:i') }}
                            @endif
                        </p>

                        {{-- Proof Info --}}
                        @if($submission->proof_data)
                            <div class="proof-info">
                                @if(isset($submission->proof_data['paths']))
                                    🖼️ {{ count($submission->proof_data['paths']) }} hình ảnh đính kèm
                                @elseif(isset($submission->proof_data['url']))
                                    🔗 URL: {{ e(Str::limit($submission->proof_data['url'], 50)) }}
                                @elseif(isset($submission->proof_data['qr_data']))
                                    📱 Mã QR đã gửi
                                @endif
                            </div>
                        @endif

                        {{-- Admin Notes --}}
                        @if($submission->admin_notes)
                            <div class="admin-notes">
                                <strong>Ghi chú từ Admin:</strong> {{ $submission->admin_notes }}
                            </div>
                        @endif
                    </div>

                    <div class="text-end ms-3">
                        @if($submission->isApproved())
                            <div class="text-success fw-bold">+{{ $submission->points_awarded ?? $submission->pointTask->points ?? 0 }}</div>
                            <small class="text-muted">điểm</small>
                        @elseif($submission->isPending())
                            <div class="text-warning fw-bold">+{{ $submission->pointTask->points ?? 0 }}</div>
                            <small class="text-muted">đang chờ</small>
                        @else
                            <div class="text-danger fw-bold">0</div>
                            <small class="text-muted">từ chối</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-3">📭 Bạn chưa gửi yêu cầu nào.</p>
                <a href="{{ route('user.points.index') }}" class="btn btn-primary">Bắt đầu kiếm điểm</a>
            </div>
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($submissions->hasPages())
        <div class="mt-3">
            {{ $submissions->links() }}
        </div>
    @endif

    {{-- Legend --}}
    <div class="card mt-4 bg-light">
        <div class="card-body">
            <h6 class="mb-3">Ghi chú trạng thái:</h6>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <span class="status-badge bg-warning text-dark me-2">⏳ Đang chờ</span>
                    <small class="text-muted">Yêu cầu đang được xem xét</small>
                </div>
                <div class="col-md-4 mb-2">
                    <span class="status-badge bg-success text-white me-2">✅ Đã duyệt</span>
                    <small class="text-muted">Đã nhận điểm</small>
                </div>
                <div class="col-md-4 mb-2">
                    <span class="status-badge bg-danger text-white me-2">❌ Từ chối</span>
                    <small class="text-muted">Yêu cầu không được chấp nhận</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
