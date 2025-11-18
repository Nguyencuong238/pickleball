@extends('layouts.front')

@section('content')
<style>
    .athletes-container {
        padding: 40px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .athletes-header {
        margin-bottom: 40px;
    }

    .athletes-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .athletes-header p {
        color: #6b7280;
        font-size: 1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
        border-left: 4px solid #f59e0b;
    }

    .stat-card.pending {
        border-left-color: #f59e0b;
    }

    .stat-card.approved {
        border-left-color: #10b981;
    }

    .stat-card.rejected {
        border-left-color: #ef4444;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #f59e0b;
        margin-bottom: 10px;
    }

    .stat-card.approved .stat-number {
        color: #10b981;
    }

    .stat-card.rejected .stat-number {
        color: #ef4444;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0;
    }

    .filter-tab {
        padding: 12px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        color: #9ca3af;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        position: relative;
        top: 2px;
    }

    .filter-tab.active {
        color: #f59e0b;
        border-bottom-color: #f59e0b;
    }

    .filter-tab:hover {
        color: #6b7280;
    }

    .athletes-table {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .table thead {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .table th {
        padding: 18px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        padding: 18px;
        border-bottom: 1px solid #e5e7eb;
        color: #4b5563;
    }

    .table tbody tr:hover {
        background: #f9fafb;
    }

    .athlete-name {
        font-weight: 600;
        color: #1f2937;
    }

    .tournament-name {
        font-size: 0.9rem;
        color: #6b7280;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-small {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-approve {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-reject {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .empty-state p {
        color: #9ca3af;
        font-size: 1.1rem;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
        padding: 20px;
    }

    .pagination a, .pagination span {
        padding: 10px 14px;
        border-radius: 6px;
        background: white;
        border: 1px solid #e5e7eb;
        color: #374151;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
    }

    .pagination .active span {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
    }

    @media (max-width: 768px) {
        .athletes-container {
            padding: 20px 15px;
        }

        .athletes-header h2 {
            font-size: 1.8rem;
        }

        .filter-tabs {
            flex-wrap: wrap;
        }

        .table {
            font-size: 0.9rem;
        }

        .table th, .table td {
            padding: 12px;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="athletes-container">
    <div class="athletes-header">
        <h2>Quản Lý Vận Động Viên 👥</h2>
        <p>Duyệt và quản lý các vận động viên đăng ký tham gia giải đấu của bạn</p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card pending">
            <div class="stat-number">{{ $stats['pending'] }}</div>
            <div class="stat-label">Chờ Duyệt</div>
        </div>
        <div class="stat-card approved">
            <div class="stat-number">{{ $stats['approved'] }}</div>
            <div class="stat-label">Đã Duyệt</div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-number">{{ $stats['rejected'] }}</div>
            <div class="stat-label">Từ Chối</div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="{{ route('homeyard.athletes.index', ['status' => 'pending']) }}" 
           class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">
            ⏳ Chờ Duyệt
        </a>
        <a href="{{ route('homeyard.athletes.index', ['status' => 'approved']) }}" 
           class="filter-tab {{ $status === 'approved' ? 'active' : '' }}">
            ✓ Đã Duyệt
        </a>
        <a href="{{ route('homeyard.athletes.index', ['status' => 'rejected']) }}" 
           class="filter-tab {{ $status === 'rejected' ? 'active' : '' }}">
            ✕ Từ Chối
        </a>
    </div>

    <!-- Athletes List -->
    @if($athletes->count() > 0)
        <div class="athletes-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tên Vận Động Viên</th>
                        <th>Giải Đấu</th>
                        <th>Email</th>
                        <th>Điện Thoại</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($athletes as $athlete)
                        <tr>
                            <td>
                                <div class="athlete-name">{{ $athlete->athlete_name }}</div>
                            </td>
                            <td>
                                <div class="tournament-name">{{ $athlete->tournament->name }}</div>
                            </td>
                            <td>{{ $athlete->email ?? '—' }}</td>
                            <td>{{ $athlete->phone ?? '—' }}</td>
                            <td>
                                @if($athlete->status === 'pending')
                                    <span class="status-badge status-pending">⏳ Chờ Duyệt</span>
                                @elseif($athlete->status === 'approved')
                                    <span class="status-badge status-approved">✓ Đã Duyệt</span>
                                @else
                                    <span class="status-badge status-rejected">✕ Từ Chối</span>
                                @endif
                            </td>
                            <td>
                                @if($athlete->status === 'pending')
                                    <div class="action-buttons">
                                        <form action="{{ route('homeyard.athletes.approve', ['tournament' => $athlete->tournament_id, 'athlete' => $athlete->id]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-small btn-approve">✓ Duyệt</button>
                                        </form>
                                        <form action="{{ route('homeyard.athletes.reject', ['tournament' => $athlete->tournament_id, 'athlete' => $athlete->id]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-small btn-reject">✕ Từ Chối</button>
                                        </form>
                                    </div>
                                @else
                                    <span style="color: #9ca3af;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            {{ $athletes->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <p>
                @if($status === 'pending')
                    Không có vận động viên nào đang chờ duyệt.
                @elseif($status === 'approved')
                    Không có vận động viên nào được duyệt.
                @else
                    Không có vận động viên nào bị từ chối.
                @endif
            </p>
        </div>
    @endif
</div>
@endsection
