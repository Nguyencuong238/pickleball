@extends('layouts.verifier')

@section('title', 'Chi tiết yêu cầu xác minh')
@section('header', 'Chi tiết yêu cầu')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- User Info Card -->
    <div class="detail-card">
        <div class="detail-card-header">
            <h3>Thông tin người dùng</h3>
        </div>
        <div class="detail-card-body">
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem;">
                @if($user->getAvatarUrl())
                    <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}"
                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                @else
                    <div class="user-avatar-lg">{{ $user->getInitials() }}</div>
                @endif
                <div>
                    <h4 style="margin: 0 0 0.5rem 0;">{{ $user->name }}</h4>
                    <div style="color: var(--text-secondary);">{{ $user->email }}</div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-item">
                    <span class="detail-label">Trình độ</span>
                    <span class="detail-value">
                        <span class="badge badge-info">{{ $quizResult['skill_level'] }}</span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Điểm ELO</span>
                    <span class="detail-value">{{ number_format($user->elo_rating ?? 1000) }}</span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-item">
                    <span class="detail-label">Tổng OPRS</span>
                    <span class="detail-value">{{ number_format($user->total_oprs ?? 700, 0) }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Trạng thái ELO</span>
                    <span class="detail-value">
                        @if($user->is_elo_verified)
                            <span class="badge badge-success">Đã xác minh</span>
                        @elseif($user->elo_is_provisional)
                            <span class="badge badge-warning">Tạm thời (từ Quiz)</span>
                        @else
                            <span class="badge badge-info">Từ trận đấu</span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-item">
                    <span class="detail-label">Số trận đấu</span>
                    <span class="detail-value">{{ $user->total_ocr_matches ?? 0 }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tỷ lệ thắng</span>
                    <span class="detail-value">
                        @php
                            $winRate = $user->total_ocr_matches > 0
                                ? round(($user->ocr_wins / $user->total_ocr_matches) * 100)
                                : 0;
                        @endphp
                        {{ $winRate }}% ({{ $user->ocr_wins ?? 0 }}W / {{ $user->ocr_losses ?? 0 }}L)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Results Card -->
    <div class="detail-card">
        <div class="detail-card-header">
            <h3>Kết quả Quiz đánh giá</h3>
        </div>
        <div class="detail-card-body">
            @if($quizResult)
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">ELO từ Quiz</span>
                        <span class="detail-value">{{ number_format($quizResult['final_elo'] ?? 0) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Điểm %</span>
                        <span class="detail-value">{{ number_format($quizResult['quiz_percent'] ?? 0, 1) }}%</span>
                    </div>
                </div>

                @if(!empty($quizResult['domain_scores']))
                <div style="margin-top: 1rem;">
                    <span class="detail-label">Điểm theo Domain:</span>
                    <div style="margin-top: 0.5rem;">
                        @foreach($quizResult['domain_scores'] as $domain => $score)
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span>{{ $quizDomains[$domain] }}</span>
                            <span style="font-weight: 600;">{{ number_format($score, 1) }}%</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($quizResult['flags']))
                <div style="margin-top: 1rem;">
                    <span class="detail-label">Cảnh báo:</span>
                    <div style="margin-top: 0.5rem;">
                        @foreach($quizResult['flags'] as $flag)
                        <span class="badge badge-warning" style="margin-right: 0.5rem; margin-bottom: 0.5rem;">
                            {{ $flag }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            @else
                <div class="alert alert-info">
                    Không có kết quả quiz.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Request Details Card -->
<div class="detail-card">
    <div class="detail-card-header">
        <h3>Chi tiết yêu cầu xác minh</h3>
    </div>
    <div class="detail-card-body">
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Trạng thái</span>
                <span class="detail-value">
                    <span class="badge {{ $verificationRequest->getStatusBadgeClass() }}">
                        {{ $verificationRequest->getStatusLabel() }}
                    </span>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Ngày gửi</span>
                <span class="detail-value">{{ $verificationRequest->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        @if($verificationRequest->notes)
        <div style="margin-top: 1rem;">
            <span class="detail-label">Ghi chú từ người dùng:</span>
            <div style="margin-top: 0.5rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                {{ $verificationRequest->notes }}
            </div>
        </div>
        @endif

        <!-- Media Gallery -->
        @php
            $images = $verificationRequest->getMedia('verification_images');
            $videos = $verificationRequest->getMedia('verification_videos');
        @endphp

        @if($images->count() > 0)
        <div style="margin-top: 1.5rem;">
            <span class="detail-label">Hình ảnh đính kèm ({{ $images->count() }}):</span>
            <div class="media-gallery" style="margin-top: 0.5rem;">
                @foreach($images as $image)
                <div class="media-item">
                    <a href="{{ $image->getUrl() }}" target="_blank">
                        <img src="{{ $image->getUrl() }}" alt="Evidence">
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($videos->count() > 0)
        <div style="margin-top: 1.5rem;">
            <span class="detail-label">Video đính kèm ({{ $videos->count() }}):</span>
            <div class="media-gallery" style="margin-top: 0.5rem;">
                @foreach($videos as $video)
                <div class="media-item">
                    <video controls>
                        <source src="{{ $video->getUrl() }}" type="{{ $video->mime_type }}">
                    </video>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- External Links -->
        @if(!empty($verificationRequest->links))
        <div style="margin-top: 1.5rem;">
            <span class="detail-label">Liên kết đính kèm:</span>
            <ul class="links-list" style="margin-top: 0.5rem;">
                @foreach($verificationRequest->links as $link)
                <li>
                    <span class="badge badge-info" style="margin-right: 0.5rem;">
                        {{ ucfirst($link['type'] ?? 'link') }}
                    </span>
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener">
                        {{ $link['url'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($verificationRequest->verifier)
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
            <div class="detail-row">
                <div class="detail-item">
                    <span class="detail-label">Người duyệt</span>
                    <span class="detail-value">{{ $verificationRequest->verifier->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Ngày duyệt</span>
                    <span class="detail-value">{{ $verificationRequest->verified_at?->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            @if($verificationRequest->verifier_notes)
            <div style="margin-top: 1rem;">
                <span class="detail-label">Ghi chú từ người duyệt:</span>
                <div style="margin-top: 0.5rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                    {{ $verificationRequest->verifier_notes }}
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Action Buttons (only for pending requests) -->
        @if($verificationRequest->isPending())
        <div class="action-buttons">
            <form action="{{ route('verifier.requests.approve', $verificationRequest) }}" method="POST"
                  style="display: inline;" onsubmit="return confirm('Bạn chắc chắn muốn duyệt yêu cầu này?')">
                @csrf
                <input type="hidden" name="notes" id="approve-notes">
                <button type="submit" class="btn-approve">
                    Duyệt yêu cầu
                </button>
            </form>

            <button type="button" class="btn-reject" onclick="showRejectModal()">
                Từ chối
            </button>
        </div>

        <!-- Reject Modal -->
        <div id="reject-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
            <div style="background: white; padding: 2rem; border-radius: var(--radius-xl); max-width: 500px; width: 90%;">
                <h3 style="margin: 0 0 1rem 0;">Từ chối yêu cầu</h3>
                <form action="{{ route('verifier.requests.reject', $verificationRequest) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Lý do từ chối *</label>
                        <textarea name="notes" rows="4" required minlength="10"
                                  style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: var(--radius-md);"
                                  placeholder="Vui lòng nhập lý do từ chối (tối thiểu 10 ký tự)"></textarea>
                    </div>
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-outline" onclick="hideRejectModal()">Hủy</button>
                        <button type="submit" class="btn-reject">Xác nhận từ chối</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<div style="margin-top: 1.5rem;">
    <a href="{{ route('verifier.requests.index') }}" class="btn btn-outline">
        ← Quay lại danh sách
    </a>
</div>
@endsection

@section('js')
<script>
function showRejectModal() {
    document.getElementById('reject-modal').style.display = 'flex';
}

function hideRejectModal() {
    document.getElementById('reject-modal').style.display = 'none';
}

document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRejectModal();
    }
});
</script>
@endsection
