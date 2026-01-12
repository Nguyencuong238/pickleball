@extends('layouts.front')

@section('content')
<style>
    .referral-container {
        padding: clamp(20px, 3vw, 40px);
        max-width: 900px;
        margin: 0 auto;
    }

    .referral-header {
        margin-bottom: clamp(30px, 5vw, 50px);
    }

    .referral-header h2 {
        font-size: clamp(1.8rem, 5vw, 2.5rem);
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .referral-header p {
        color: #6b7280;
        font-size: clamp(0.9rem, 2vw, 1rem);
    }

    .profile-card {
        background: white;
        border-radius: 15px;
        padding: clamp(20px, 3vw, 30px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .profile-card h4 {
        font-size: 1.2rem;
        color: #1f2937;
        margin-bottom: 20px;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .referral-section {
        display: flex;
        flex-direction: column;
    }

    .referral-link-container {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .referral-link-input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 0.9rem;
        background-color: #f9fafb;
        color: #1f2937;
        font-family: 'Courier New', monospace;
    }

    .btn-copy {
        padding: 12px 24px;
        background: #00D9B5;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .btn-copy:hover {
        background: #00b899;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 217, 181, 0.3);
    }

    .btn-copy.copied {
        background: #10b981;
    }

    .referral-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
        margin-bottom: 25px;
    }

    .stat-box {
        background: linear-gradient(135deg, #f0fffe 0%, #d1fae5 100%);
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #00D9B5;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #065f46;
        font-weight: 500;
    }

    .referral-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .referral-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .referral-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .referral-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        color: #6b7280;
    }

    .referral-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .referral-date {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .referral-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .referral-badge.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .referral-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    @media (max-width: 768px) {
        .referral-link-container {
            flex-direction: column;
        }

        .referral-table {
            font-size: 0.85rem;
        }

        .referral-table th,
        .referral-table td {
            padding: 8px 12px;
        }
    }
</style>

<div class="referral-container">
    <div class="referral-header" style="margin-top: 100px">
        <h2>💼 Giới Thiệu Người Dùng</h2>
        <p>Chia sẻ mã referral của bạn và kiếm thêm lợi ích khi bạn bè đăng ký qua link của bạn</p>
        <div style="margin-top: 15px;">
            <a href="{{ route('user.wallet.history') }}" style="display: inline-block; padding: 10px 20px; background: #00D9B5; color: white; text-decoration: none; border-radius: 8px; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.background='#00b899'" onmouseout="this.style.background='#00D9B5'">
                📊 Xem Lịch Sử Điểm
            </a>
        </div>
    </div>

    {{-- Referral Section --}}
    <div class="profile-card">
        <h4>Chia Sẻ Liên Kết Referral</h4>
        <div class="referral-section">
            @if($user->referral_code)
            <p style="margin: 0 0 10px 0; color: #065f46; font-weight: 500;">Chia sẻ link dưới đây để bạn bè có thể đăng ký qua bạn</p>
            <p style="margin: 0 0 15px 0; color: #6b7280; font-size: 0.85rem;">Mã của bạn: <strong style="color: #00D9B5;">{{ $user->referral_code }}</strong></p>
            <div style="margin-bottom: 15px; padding: 12px; background: #dbeafe; border: 1px solid #3b82f6; border-radius: 8px; color: #1e40af; font-size: 0.9rem;">
                <strong>💡 Thông báo:</strong> Bạn sẽ nhận được 10 điểm sau khi user hoàn thành đăng ký tài khoản và đánh giá trình độ.
            </div>
            
            <div class="referral-link-container">
                <input type="text" id="referralLink" class="referral-link-input" readonly value="{{ url('/register?ref=' . $user->referral_code) }}">
                <button type="button" class="btn-copy" onclick="copyReferralLink()">
                    <span id="copyText">📋 Copy Link</span>
                </button>
            </div>

            <div style="margin-top: 15px; padding: 12px; background: #f0fffe; border: 1px solid #a7f3d0; border-radius: 8px; font-size: 0.9rem; color: #065f46;">
                <strong>✓ Mã của bạn:</strong> {{ $user->referral_code }}<br>
                <span style="font-size: 0.85rem;">Gửi cho bạn bè để họ biết ai giới thiệu họ</span>
            </div>
            @else
            <div style="padding: 15px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; color: #991b1b;">
                <strong>⚠️ Lỗi:</strong> Mã referral chưa được tạo. Vui lòng liên hệ admin.
            </div>
            @endif
            
            @if($referralStats)
            <div class="referral-stats">
                <div class="stat-box">
                    <div class="stat-number">{{ $referralStats['total'] }}</div>
                    <div class="stat-label">Tổng Lời Mời</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $referralStats['completed'] }}</div>
                    <div class="stat-label">Đã Hoàn Thành</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $referralStats['pending'] }}</div>
                    <div class="stat-label">Đang Chờ</div>
                </div>
            </div>
            @endif

            {{-- Referral Details Table --}}
            @if($referralDetails && $referralDetails->count() > 0)
            <div style="margin-top: 25px;">
                <h5 style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 15px;">Danh Sách Người Được Giới Thiệu</h5>
                <div style="overflow-x: auto;">
                    <table class="referral-table">
                        <thead>
                            <tr>
                                <th>Người Đăng Ký</th>
                                <th>Email</th>
                                <th>Ngày Đăng Ký</th>
                                <th>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referralDetails as $referral)
                            <tr>
                                <td>
                                    <strong>{{ $referral->referredUser->name }}</strong>
                                </td>
                                <td>{{ $referral->referredUser->email }}</td>
                                <td>
                                    <span class="referral-date">{{ $referral->referred_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>
                                    <span class="referral-badge {{ $referral->status }}">
                                        {{ $referral->status === 'completed' ? '✓ Đã hoàn thành' : '⏳ Đang chờ' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div style="margin-top: 20px; padding: 15px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; color: #6b7280; text-align: center;">
                Bạn chưa có ai được giới thiệu. Chia sẻ link của bạn để bắt đầu!
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    // Referral Link Copy Function
    function copyReferralLink() {
        const referralLink = document.getElementById('referralLink');
        const copyBtn = document.querySelector('.btn-copy');
        const copyText = document.getElementById('copyText');
        
        // Select text
        referralLink.select();
        referralLink.setSelectionRange(0, 99999);
        
        // Copy to clipboard
        navigator.clipboard.writeText(referralLink.value).then(() => {
            // Show feedback
            copyText.textContent = '✓ Đã Copy!';
            copyBtn.classList.add('copied');
            
            // Reset after 2 seconds
            setTimeout(() => {
                copyText.textContent = '📋 Copy Link';
                copyBtn.classList.remove('copied');
            }, 2000);
            
            toastr.success('Liên kết referral đã được sao chép!');
        }).catch(err => {
            toastr.error('Không thể sao chép liên kết');
        });
    }
</script>
@endsection
