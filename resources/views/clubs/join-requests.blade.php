@extends('layouts.front')

@section('content')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .requests-wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 40px 20px;
        padding-top: 120px;
    }

    .requests-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .header-section {
        background: white;
        border-radius: 20px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-content h1 {
        font-size: 2.2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #006646 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 8px;
    }

    .header-content p {
        color: #6b7280;
        font-size: 1.05rem;
        margin-bottom: 5px;
    }

    .club-name {
        font-weight: 700;
        color: #1f2937;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #f3f4f6;
        color: #6b7280;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .back-btn:hover {
        background: linear-gradient(135deg, #006646 0%, #0db89d 100%);
        color: white;
        border-color: #006646;
        transform: translateX(-3px);
    }

    .tabs-container {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #f3f4f6;
        padding: 0 30px;
    }

    .tab-btn {
        padding: 16px 24px;
        background: none;
        border: none;
        color: #9ca3af;
        font-weight: 700;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        position: relative;
    }

    .tab-btn:hover {
        color: #6b7280;
    }

    .tab-btn.active {
        color: #006646;
        border-bottom-color: #006646;
    }

    .tab-content {
        display: none;
        padding: 30px;
        animation: slideIn 0.3s ease;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .request-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .request-card {
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 4px solid #006646;
        transition: all 0.3s ease;
    }

    .request-card:hover {
        box-shadow: 0 8px 25px rgba(0, 217, 181, 0.15);
        transform: translateY(-2px);
    }

    .request-info {
        flex: 1;
    }

    .user-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #006646 0%, #0db89d 100%);
        color: white;
        font-weight: 700;
        font-size: 1.2rem;
        margin-right: 15px;
        margin-bottom: 10px;
    }

    .user-details {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 10px;
    }

    .user-name {
        font-weight: 800;
        color: #1f2937;
        font-size: 1.1rem;
    }

    .badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #7f1d1d;
    }

    .request-meta {
        display: flex;
        gap: 20px;
        margin-top: 8px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .request-actions {
        display: flex;
        gap: 10px;
        margin-left: 20px;
    }

    .btn-approve, .btn-reject {
        padding: 10px 18px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-approve {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    .btn-reject {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .empty-state {
        text-align: center;
        padding: 80px 40px;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        color: #1f2937;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .empty-state p {
        color: #9ca3af;
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .header-section {
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }

        .header-content h1 {
            font-size: 1.8rem;
        }

        .request-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .request-actions {
            margin-left: 0;
            margin-top: 15px;
            width: 100%;
        }

        .request-actions form {
            flex: 1;
        }

        .btn-approve, .btn-reject {
            width: 100%;
            justify-content: center;
        }

        .tabs {
            padding: 0 16px;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 14px 16px;
            font-size: 0.85rem;
        }

        .tab-content {
            padding: 20px;
        }

        .request-meta {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<div class="requests-wrapper">
    <div class="requests-container">
        <div class="header-section">
            <div class="header-content">
                <h1>📋 Quản Lý Yêu Cầu Tham Gia</h1>
                <p>Câu lạc bộ / Nhóm: <span class="club-name">{{ $club->name }}</span></p>
            </div>
            <a href="{{ route('clubs.show', $club) }}" class="back-btn">
                <span>←</span> Quay Lại
            </a>
        </div>

        @if ($pendingRequests->count() > 0 || $approvedRequests->count() > 0 || $rejectedRequests->count() > 0)
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('pending')">
                        ⏳ Chờ Duyệt <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 12px; margin-left: 8px; font-weight: 800; font-size: 0.8rem;">{{ $pendingRequests->count() }}</span>
                    </button>
                    <button class="tab-btn" onclick="switchTab('approved')">
                        ✓ Đã Duyệt <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 12px; margin-left: 8px; font-weight: 800; font-size: 0.8rem;">{{ $approvedRequests->count() }}</span>
                    </button>
                    <button class="tab-btn" onclick="switchTab('rejected')">
                        ✗ Bị Từ Chối <span style="background: #fee2e2; color: #7f1d1d; padding: 2px 8px; border-radius: 12px; margin-left: 8px; font-weight: 800; font-size: 0.8rem;">{{ $rejectedRequests->count() }}</span>
                    </button>
                </div>

                <!-- Pending Requests Tab -->
                <div id="pending" class="tab-content active">
                    @if ($pendingRequests->count() > 0)
                        <div class="request-list">
                            @foreach ($pendingRequests as $request)
                                <div class="request-card">
                                    <div class="request-info">
                                        <div class="user-details">
                                            <div class="user-avatar">{{ strtoupper(substr($request->user->name, 0, 1)) }}</div>
                                            <div>
                                                <div class="user-name">{{ $request->user->name }}</div>
                                                <div class="request-meta">
                                                    <div class="meta-item">
                                                        <span>📧</span> {{ $request->user->email }}
                                                    </div>
                                                    <div class="meta-item">
                                                        <span>🕐</span> Gửi: {{ $request->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="request-actions">
                                        <form action="{{ route('clubs.approve-join-request', [$club, $request]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-approve">
                                                <span>✓</span> Duyệt
                                            </button>
                                        </form>
                                        <form action="{{ route('clubs.reject-join-request', [$club, $request]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-reject">
                                                <span>✗</span> Từ Chối
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <h3>Không có yêu cầu chờ duyệt</h3>
                            <p>Tất cả yêu cầu đã được xử lý</p>
                        </div>
                    @endif
                </div>

                <!-- Approved Requests Tab -->
                <div id="approved" class="tab-content">
                    @if ($approvedRequests->count() > 0)
                        <div class="request-list">
                            @foreach ($approvedRequests as $request)
                                <div class="request-card">
                                    <div class="request-info">
                                        <div class="user-details">
                                            <div class="user-avatar" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">{{ strtoupper(substr($request->user->name, 0, 1)) }}</div>
                                            <div>
                                                <div class="user-name">
                                                    {{ $request->user->name }}
                                                    <span class="badge badge-approved">Thành viên</span>
                                                </div>
                                                <div class="request-meta">
                                                    <div class="meta-item">
                                                        <span>📧</span> {{ $request->user->email }}
                                                    </div>
                                                    <div class="meta-item">
                                                        <span>✓</span> Duyệt: {{ $request->updated_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">📬</div>
                            <h3>Chưa có yêu cầu nào được duyệt</h3>
                            <p>Duyệt các yêu cầu từ tab "Chờ Duyệt"</p>
                        </div>
                    @endif
                </div>

                <!-- Rejected Requests Tab -->
                <div id="rejected" class="tab-content">
                    @if ($rejectedRequests->count() > 0)
                        <div class="request-list">
                            @foreach ($rejectedRequests as $request)
                                <div class="request-card">
                                    <div class="request-info">
                                        <div class="user-details">
                                            <div class="user-avatar" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">{{ strtoupper(substr($request->user->name, 0, 1)) }}</div>
                                            <div>
                                                <div class="user-name">
                                                    {{ $request->user->name }}
                                                    <span class="badge badge-rejected">Bị từ chối</span>
                                                </div>
                                                <div class="request-meta">
                                                    <div class="meta-item">
                                                        <span>📧</span> {{ $request->user->email }}
                                                    </div>
                                                    <div class="meta-item">
                                                        <span>✗</span> Từ chối: {{ $request->updated_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">✓</div>
                            <h3>Tuyệt vời!</h3>
                            <p>Không có yêu cầu nào bị từ chối</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="tabs-container" style="overflow: hidden;">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>Chưa có yêu cầu tham gia</h3>
                    <p>Chưa ai gửi yêu cầu tham gia câu lạc bộ/nhóm này</p>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName).classList.add('active');

        // Add active class to clicked button
        event.target.classList.add('active');
    }
</script>

@endsection
