@extends('layouts.homeyard')

@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1>Cấu Hình Giải Đấu</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="overview.html" class="breadcrumb-link">🏠 Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">
                            <a href="tournaments.html" class="breadcrumb-link">Giải đấu</a>
                        </span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">Cấu hình</span>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-success">💾 Lưu thay đổi</button>
                    <button class="btn btn-secondary">👁️ Xem trước</button>
                    <div class="header-notifications">
                        <button class="notification-btn">
                            <span>🔔</span>
                            <span class="notification-badge">5</span>
                        </button>
                    </div>
                    <div class="header-user">
                        <div class="user-avatar">AD</div>
                        <div class="user-info">
                            <div class="user-name">Admin User</div>
                            <div class="user-role">Quản trị viên</div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Tournament Header Banner -->
            <div class="tournament-header-banner fade-in">
                <div class="tournament-header-content">
                    <h2 class="tournament-header-title">Giải Pickleball Mở Rộng TP.HCM 2025</h2>
                    <div class="tournament-header-meta">
                        <div class="header-meta-item">
                            <span>📅</span>
                            <span>20-22 Tháng 1, 2025</span>
                        </div>
                        <div class="header-meta-item">
                            <span>📍</span>
                            <span>Sân Pickleball Thảo Điền</span>
                        </div>
                        <div class="header-meta-item">
                            <span>👥</span>
                            <span>64 Vận động viên</span>
                        </div>
                        <div class="header-meta-item">
                            <span>💰</span>
                            <span>Giải thưởng: 50,000,000 VNĐ</span>
                        </div>
                        <div class="header-meta-item">
                            <span class="badge badge-success">Đang diễn ra</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Config Tabs -->
            <div class="config-tabs fade-in">
                <button class="config-tab active" onclick="showConfigTab('config')">
                    ⚙️ Cấu hình giải đấu
                </button>
                <button class="config-tab" onclick="showConfigTab('athletes')">
                    👥 Quản lý VĐV
                </button>
                <button class="config-tab" onclick="showConfigTab('matches')">
                    🎾 Quản lý trận đấu
                </button>
                <button class="config-tab" onclick="showConfigTab('rankings')">
                    🏅 Bảng xếp hạng
                </button>
            </div>
            <!-- TAB 1: CẤU HÌNH GIẢI ĐẤU -->
            <div id="config" class="tab-content active">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step completed">
                        <div class="step-circle">1</div>
                        <div class="step-label">Cấu hình cơ bản</div>
                    </div>
                    <div class="step completed">
                        <div class="step-circle">2</div>
                        <div class="step-label">Nội dung thi đấu</div>
                    </div>
                    <div class="step active">
                        <div class="step-circle">3</div>
                        <div class="step-label">Vòng đấu & Sân</div>
                    </div>
                    <div class="step">
                        <div class="step-circle">4</div>
                        <div class="step-label">Bảng đấu</div>
                    </div>
                </div>
                <!-- Step 1: Cấu hình cơ bản -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">📋 Thông tin giải đấu</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ $tournament ? route('homeyard.tournaments.update', $tournament->id) : route('homeyard.tournaments.store') }}">
                            @csrf
                            @if($tournament)
                                @method('PUT')
                            @endif
                            
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">Tên giải đấu *</label>
                                    <input type="text" name="name" class="form-input" value="{{ $tournament->name ?? '' }}"
                                        placeholder="VD: Giải Pickleball Mở Rộng TP.HCM 2025"
                                        required>
                                    @error('name')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Giá đăng ký (VNĐ)</label>
                                    <input type="number" name="price" class="form-input" value="{{ $tournament->price ?? 0 }}"
                                        placeholder="500000" min="0">
                                    @error('price')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Ngày bắt đầu *</label>
                                    <input type="date" name="start_date" class="form-input" value="{{ $tournament->start_date ?? '' }}" required>
                                    @error('start_date')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Ngày kết thúc *</label>
                                    <input type="date" name="end_date" class="form-input" value="{{ $tournament->end_date ?? '' }}" required>
                                    @error('end_date')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Địa điểm tổ chức *</label>
                                    <input type="text" name="location" class="form-input" value="{{ $tournament->location ?? '' }}"
                                        placeholder="VD: Sân Pickleball Thảo Điền" required>
                                    @error('location')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mô tả giải đấu</label>
                                <textarea name="description" class="form-textarea" rows="4"
                                    placeholder="Mô tả chi tiết về giải đấu">{{ $tournament->description ?? '' }}</textarea>
                                @error('description')
                                    <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">Số lượng VĐV tối đa</label>
                                    <input type="number" name="max_participants" class="form-input" value="{{ $tournament->max_participants ?? 32 }}"
                                        min="4" max="1000">
                                    @error('max_participants')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Trạng thái *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="upcoming" {{ ($tournament && $tournament->status) ? 'selected' : '' }}>Sắp diễn ra</option>
                                        <option value="ongoing" {{ ($tournament && $tournament->status) ? 'selected' : '' }}>Đang diễn ra</option>
                                        <option value="completed" {{ ($tournament && !$tournament->status) ? 'selected' : '' }}>Đã hoàn thành</option>
                                        <option value="cancelled" {{ ($tournament && !$tournament->status) ? 'selected' : '' }}>Bị hủy</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quy tắc thi đấu</label>
                                <textarea name="rules" class="form-textarea" rows="3"
                                    placeholder="Nhập quy tắc thi đấu (tùy chọn)">{{ $tournament->rules ?? '' }}</textarea>
                            </div>
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <button type="submit" class="btn btn-success">💾 Lưu thông tin</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Tiếp tục ➜</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Step 2: Nội dung thi đấu -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🎯 Nội dung thi đấu</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            💡 Tạo các nội dung thi đấu khác nhau cho giải đấu
                        </div>
                        @if(!$tournament)
                            <div class="alert alert-warning" style="border-color: #FBBF24; background-color: #FFFBEB;">
                                ⚠️ <strong>Vui lòng lưu thông tin giải đấu ở Step 1 trước khi thêm nội dung</strong>
                                <p style="margin-top: 0.5rem; font-size: 0.9rem;">Bạn cần tạo giải đấu cơ bản trước, sau đó mới có thể thêm nội dung thi đấu.</p>
                            </div>
                        @else
                        <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm nội dung mới</h4>
                        <form method="POST" action="{{ route('homeyard.tournaments.categories.store', $tournament->id) }}">
                            @csrf
                            
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Tên nội dung *</label>
                                    <input 
                                        type="text" 
                                        name="category_name" 
                                        class="form-input" 
                                        placeholder="VD: Nam đơn 18+"
                                        required
                                    >
                                    @error('category_name')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Loại nội dung *</label>
                                    <select name="category_type" class="form-select" required>
                                        <option value="">-- Chọn loại --</option>
                                        <option value="single_men">Đơn nam</option>
                                        <option value="single_women">Đơn nữ</option>
                                        <option value="double_men">Đôi nam</option>
                                        <option value="double_women">Đôi nữ</option>
                                        <option value="double_mixed">Đôi nam nữ</option>
                                    </select>
                                    @error('category_type')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Độ tuổi *</label>
                                    <select name="age_group" class="form-select" required>
                                        <option value="open">Mở rộng</option>
                                        <option value="u18">U18</option>
                                        <option value="18+" selected>18+</option>
                                        <option value="35+">35+</option>
                                        <option value="45+">45+</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">Số VĐV tối đa *</label>
                                    <input 
                                        type="number" 
                                        name="max_participants" 
                                        class="form-input" 
                                        placeholder="32"
                                        min="4"
                                        max="128"
                                        required
                                    >
                                    @error('max_participants')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Giải thưởng (VNĐ)</label>
                                    <input 
                                        type="number" 
                                        name="prize_money" 
                                        class="form-input" 
                                        placeholder="5000000"
                                        min="0"
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">➕ Thêm nội dung</button>
                        </form>

                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách nội dung đã tạo</h4>
                        @if($tournament && $tournament->categories && $tournament->categories->count() > 0)
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead style="background: #f5f5f5;">
                                        <tr>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Tên</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Loại</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Độ tuổi</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">VĐV tối đa</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Giải thưởng</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tournament->categories as $category)
                                            <tr style="border-bottom: 1px solid #ddd;">
                                                <td style="padding: 10px;">{{ $category->category_name }}</td>
                                                <td style="padding: 10px;">
                                                    @switch($category->category_type)
                                                        @case('single_men') Đơn nam @break
                                                        @case('single_women') Đơn nữ @break
                                                        @case('double_men') Đôi nam @break
                                                        @case('double_women') Đôi nữ @break
                                                        @case('double_mixed') Đôi nam nữ @break
                                                    @endswitch
                                                </td>
                                                <td style="padding: 10px;">{{ $category->age_group }}</td>
                                                <td style="padding: 10px;">{{ $category->max_participants }}</td>
                                                <td style="padding: 10px;">{{ number_format($category->prize_money ?? 0, 0, ',', '.') }} VNĐ</td>
                                                <td style="padding: 10px;">
                                                    <form method="POST" action="{{ route('homeyard.tournaments.categories.destroy', [$tournament->id, $category->id]) }}" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa?')">🗑️</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #999;">
                                <p>Chưa có nội dung nào. Hãy thêm nội dung mới ở trên.</p>
                            </div>
                        @endif

                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button class="btn btn-secondary" onclick="prevStep(1)">⬅ Quay lại</button>
                            <button class="btn btn-primary" onclick="nextStep(3)">Tiếp tục ➜</button>
                        </div>
                        @endif
                    </div>
                </div>
                <!-- Step 3: Vòng đấu & Sân -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🔄 Tạo vòng đấu</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            💡 Thiết lập các vòng đấu cho giải (Vòng bảng, Vòng 1/8, Tứ kết, Bán kết, Chung kết)
                        </div>
                        @if(!$tournament)
                            <div class="alert alert-warning">
                                ⚠️ Vui lòng lưu thông tin giải đấu ở Step 1 trước
                            </div>
                        @else
                        <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm vòng đấu mới</h4>

                        <form method="POST" action="{{ route('homeyard.tournaments.rounds.store', $tournament->id) }}">
                            @csrf
                            
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Tên vòng đấu *</label>
                                    <input 
                                        type="text" 
                                        name="round_name" 
                                        class="form-input" 
                                        placeholder="VD: Vòng bảng"
                                        required
                                    >
                                    @error('round_name')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Ngày thi đấu *</label>
                                    <input 
                                        type="date" 
                                        name="start_date" 
                                        class="form-input"
                                        required
                                    >
                                    @error('start_date')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Giờ bắt đầu *</label>
                                    <input 
                                        type="time" 
                                        name="start_time" 
                                        class="form-input"
                                        required
                                    >
                                    @error('start_time')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">Số thứ tự vòng *</label>
                                    <input 
                                        type="number" 
                                        name="round_number" 
                                        class="form-input" 
                                        placeholder="1"
                                        min="1"
                                        max="20"
                                        required
                                    >
                                    @error('round_number')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Loại vòng *</label>
                                    <select name="round_type" class="form-select" required>
                                        <option value="">-- Chọn loại --</option>
                                        <option value="group_stage">Vòng bảng</option>
                                        <option value="knockout">Loại trực tiếp</option>
                                        <option value="quarterfinal">Tứ kết</option>
                                        <option value="semifinal">Bán kết</option>
                                        <option value="final">Chung kết</option>
                                        <option value="bronze">Tranh hạng 3</option>
                                    </select>
                                    @error('round_type')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">➕ Thêm vòng đấu</button>
                        </form>

                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách vòng đấu</h4>
                        @if($tournament && $tournament->rounds && $tournament->rounds->count() > 0)
                            <div class="item-grid">
                                @foreach($tournament->rounds as $round)
                                    <div class="item-card">
                                        <strong>{{ $round->round_name }}</strong>
                                        <p>{{ \Carbon\Carbon::parse($round->start_date)->format('d/m/Y') }} - {{ $round->start_time }}</p>
                                        <form method="POST" action="{{ route('homeyard.tournaments.rounds.destroy', [$tournament->id, $round->id]) }}" style="display: inline; margin-top: 10px;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa?')">🗑️ Xóa</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #999;">
                                <p>Chưa có vòng nào. Hãy thêm vòng mới ở trên.</p>
                            </div>
                        @endif
                        @endif
                    </div>
                </div>
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🏟️ Chọn sân thi đấu</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            💡 Chọn các sân sẽ được sử dụng cho giải đấu
                        </div>
                        {{-- <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm sân mới</h4>

                        <div class="grid grid-3">
                            <div class="form-group">
                                <label class="form-label">Tên sân *</label>
                                <input type="text" class="form-input" id="courtName" placeholder="VD: Sân số 1">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Loại sân *</label>
                                <select class="form-select" id="courtType">
                                    <option value="indoor">Trong nhà</option>
                                    <option value="outdoor">Ngoài trời</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" id="courtStatus">
                                    <option value="available">Có thể sử dụng</option>
                                    <option value="maintenance">Bảo trì</option>
                                    <option value="reserved">Đã đặt</option>
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-success" onclick="addCourt()">➕ Thêm sân</button> --}}
                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách sân đã chọn</h4>
                        <form id="courtsForm" method="POST" action="{{ $tournament ? route('homeyard.tournaments.courts.save', $tournament->id) : '#' }}">
                            @csrf
                            <div class="item-grid" id="courtsGrid">
                                @if($courts && $courts->count() > 0)
                                    @php
                                        $selectedCourtIds = $tournament && $tournament->tournament_courts 
                                            ? json_decode($tournament->tournament_courts, true) 
                                            : [];
                                    @endphp
                                    @foreach($courts as $court)
                                        <label style="cursor: pointer;">
                                            <input type="checkbox" name="court_ids[]" value="{{ $court->id }}" 
                                                {{ in_array($court->id, $selectedCourtIds) ? 'checked' : '' }}
                                                style="display: none;">
                                            <div class="item-card court-card {{ in_array($court->id, $selectedCourtIds) ? 'selected' : '' }}" data-court-id="{{ $court->id }}" style="cursor: pointer;">
                                                <strong>{{ $court->court_name ?? 'Sân ' . $court->court_number }}</strong>
                                                <p>{{ $court->court_type === 'indoor' ? 'Trong nhà' : 'Ngoài trời' }}</p>
                                                <small style="color: #666; font-size: 0.8rem;">
                                                    @if($court->status === 'available')
                                                        <span style="color: black;">✓ Có thể sử dụng</span>
                                                    @elseif($court->status === 'maintenance')
                                                        <span style="color: #F59E0B;">⚠ Bảo trì</span>
                                                    @else
                                                        <span style="color: #EF4444;">✗ Đã đặt</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </label>
                                    @endforeach
                                @else
                                    <div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #999;">
                                        <p>Chưa có sân nào. <a href="{{ route('homeyard.courts') }}" style="color: #00D9B5; text-decoration: underline;">Thêm sân ngay</a></p>
                                    </div>
                                @endif
                            </div>
                            @if($courts && $courts->count() > 0)
                                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                                    <button type="submit" class="btn btn-success">💾 Lưu sân</button>
                                    <button type="reset" class="btn btn-secondary">↻ Xóa lựa chọn</button>
                                </div>
                            @endif
                        </form>
                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button class="btn btn-secondary" onclick="prevStep(2)">⬅ Quay lại</button>
                            <button class="btn btn-primary" onclick="nextStep(4)">Tiếp tục ➜</button>
                        </div>
                    </div>
                </div>
                <!-- Step 4: Tạo bảng đấu -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">📊 Tạo bảng đấu</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            ✅ Tự động tạo bảng đấu dựa trên hình thức thi đấu và số lượng VĐV đã đăng ký
                        </div>
                        @if($tournament)
                        <form method="POST" action="{{ route('homeyard.tournaments.groups.store', $tournament->id) }}">
                            @csrf
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Chọn nội dung thi đấu *</label>
                                    <select name="category_id" class="form-select" onchange="filterAthletesByCategory()" required>
                                        <option value="">-- Chọn nội dung --</option>
                                        @forelse($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                        @empty
                                            <option value="">Không có nội dung nào</option>
                                        @endforelse
                                    </select>
                                    @error('category_id')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Chọn vòng đấu</label>
                                    <select name="round_id" class="form-select">
                                        <option value="">-- Chọn vòng (tùy chọn) --</option>
                                        @if($tournament && $tournament->rounds && $tournament->rounds->count() > 0)
                                            @foreach($tournament->rounds as $round)
                                                <option value="{{ $round->id }}">{{ $round->round_name }}</option>
                                            @endforeach
                                        @else
                                            <option value="">Chưa có vòng nào</option>
                                        @endif
                                    </select>
                                    @error('round_id')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tên bảng *</label>
                                    <input type="text" name="group_name" class="form-input" placeholder="VD: Bảng A" required>
                                    @error('group_name')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid grid-1">
                                <div class="form-group">
                                    <label class="form-label">Mã bảng *</label>
                                    <input type="text" name="group_code" class="form-input" placeholder="VD: A" maxlength="10" required>
                                    @error('group_code')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">Số VĐV tối đa *</label>
                                    <input type="number" name="max_participants" class="form-input" placeholder="8" min="2" max="128" required>
                                    @error('max_participants')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Số VĐV vượt qua *</label>
                                    <input type="number" name="advancing_count" class="form-input" placeholder="2" min="1" required>
                                    @error('advancing_count')
                                        <span class="text-danger" style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">➕ Thêm bảng</button>
                        </form>
                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách bảng đã tạo</h4>
                        @if($tournament && $tournament->groups && $tournament->groups->count() > 0)
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead style="background: #f5f5f5;">
                                        <tr>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Tên bảng</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Mã</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Nội dung</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">VĐV tối đa</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Vượt qua</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tournament->groups as $group)
                                            <tr style="border-bottom: 1px solid #ddd;">
                                                <td style="padding: 10px;">{{ $group->group_name }}</td>
                                                <td style="padding: 10px;">{{ $group->group_code }}</td>
                                                <td style="padding: 10px;">{{ $group->category->category_name ?? 'N/A' }}</td>
                                                <td style="padding: 10px;">{{ $group->max_participants }}</td>
                                                <td style="padding: 10px;">{{ $group->advancing_count }}</td>
                                                <td style="padding: 10px;">
                                                    <form method="POST" action="{{ route('homeyard.tournaments.groups.destroy', [$tournament->id, $group->id]) }}" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa?')">🗑️</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #999;">
                                <p>Chưa có bảng nào. Hãy thêm bảng mới ở trên.</p>
                            </div>
                        @endif

                         {{-- <div class="checkbox-group">
                            <input type="checkbox" id="autoSeed" checked>
                            <label for="autoSeed">Tự động xếp hạt giống dựa trên ranking</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="balancedGroups">
                            <label for="balancedGroups">Cân bằng độ mạnh các bảng</label>
                        </div>
                        <button class="btn btn-success">🎲 Tạo bảng đấu tự động</button>
                        <button class="btn btn-primary">✏️ Tạo bảng đấu thủ công</button>
                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Xem trước bảng đấu</h4>
                        <div class="bracket-container">
                            <div class="bracket-grid">
                                <div class="bracket-column">
                                    <h4>Vòng 1/8</h4>
                                    <div class="bracket-match">
                                        <div class="bracket-player winner">
                                            <span>Nguyễn Văn A</span>
                                            <span>11-7, 11-5</span>
                                        </div>
                                        <div class="bracket-player">
                                            <span>Trần Văn B</span>
                                            <span>7-11, 5-11</span>
                                        </div>
                                    </div>
                                    <div class="bracket-match">
                                        <div class="bracket-player">
                                            <span>Lê Văn C</span>
                                            <span>-</span>
                                        </div>
                                        <div class="bracket-player">
                                            <span>Phạm Văn D</span>
                                            <span>-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bracket-column">
                                    <h4>Tứ kết</h4>
                                    <div class="bracket-match">
                                        <div class="bracket-player">
                                            <span>Nguyễn Văn A</span>
                                            <span>-</span>
                                        </div>
                                        <div class="bracket-player">
                                            <span>Đang chờ</span>
                                            <span>-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bracket-column">
                                    <h4>Bán kết</h4>
                                    <div class="bracket-match">
                                        <div class="bracket-player">
                                            <span>Đang chờ</span>
                                            <span>-</span>
                                        </div>
                                        <div class="bracket-player">
                                            <span>Đang chờ</span>
                                            <span>-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bracket-column">
                                    <h4>Chung kết</h4>
                                    <div class="bracket-match">
                                        <div class="bracket-player">
                                            <span>Đang chờ</span>
                                            <span>-</span>
                                        </div>
                                        <div class="bracket-player">
                                            <span>Đang chờ</span>
                                            <span>-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        
                        <!-- Athletes List Section -->
                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">📋 Danh sách VĐV đã đăng ký</h4>
                        <div id="athletesListContainer">
                            @if($athletes && $athletes->count() > 0)
                                <div style="overflow-x: auto;">
                                    <table class="rankings-table">
                                        <thead>
                                            <tr>
                                                <th>STT</th>
                                                <th>Tên VĐV</th>
                                                <th>Email</th>
                                                <th>Điện thoại</th>
                                                <th>Nội dung</th>
                                                <th>Trạng thái</th>
                                                <th>Thanh toán</th>
                                            </tr>
                                        </thead>
                                        <tbody id="athletesTableBody">
                                            @forelse($athletes as $key => $athlete)
                                                <tr class="athlete-row" data-category-id="{{ $athlete->category_id ?? 'all' }}">
                                                    <td>{{ $key + 1 }}</td>
                                                    <td><strong>{{ $athlete->user->name ?? $athlete->athlete_name ?? 'N/A' }}</strong></td>
                                                    <td>{{ $athlete->user->email ?? $athlete->email ?? 'N/A' }}</td>
                                                    <td>{{ $athlete->user->phone ?? $athlete->phone ?? 'N/A' }}</td>
                                                    <td>{{ $athlete->category->category_name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge badge-success">{{ ucfirst($athlete->status) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($athlete->payment_status === 'paid')
                                                            <span class="badge badge-success">✓ Đã thanh toán</span>
                                                        @else
                                                            <span class="badge badge-warning">⏳ Chưa thanh toán</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" style="text-align: center; color: #999;">Chưa có VĐV nào đăng ký</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div style="text-align: center; padding: 2rem; color: #999;">
                                    <p>Chưa có VĐV nào đăng ký cho giải đấu này</p>
                                </div>
                            @endif
                        </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #999;">
                                <p>Vui lòng tạo giải đấu trước</p>
                            </div>
                        @endif
                        
                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button class="btn btn-secondary" onclick="prevStep(3)">⬅ Quay lại</button>
                            <button type="button" class="btn btn-success" onclick="alert('Cấu hình giải đấu đã hoàn tất!')">✅ Hoàn thành</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- TAB 2: QUẢN LÝ VĐV -->
            <div id="athletes" class="tab-content">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">👥 Quản lý danh sách vận động viên</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary btn-sm">➕ Thêm VĐV</button>
                            <button class="btn btn-success btn-sm">📊 Xuất Excel</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div>
                                        <div class="stat-label">Tổng VĐV đăng ký</div>
                                        <div class="stat-value">64</div>
                                    </div>
                                    <div class="stat-icon primary">👥</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div>
                                        <div class="stat-label">Đã xác nhận</div>
                                        <div class="stat-value">58</div>
                                    </div>
                                    <div class="stat-icon success">✅</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div>
                                        <div class="stat-label">Chờ xác nhận</div>
                                        <div class="stat-value">6</div>
                                    </div>
                                    <div class="stat-icon warning">⏳</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div>
                                        <div class="stat-label">Đã thanh toán</div>
                                        <div class="stat-value">52</div>
                                    </div>
                                    <div class="stat-icon success">💰</div>
                                </div>
                            </div>
                        </div>
                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách VĐV</h4>

                        <div class="athlete-list">
                            <div class="athlete-item">
                                <div class="athlete-info">
                                    <div class="athlete-name">Nguyễn Văn An</div>
                                    <div class="athlete-details">
                                        📧 nguyenvanan@email.com | 📞 0901234567 | 🎯 Nam đơn 18+<br>
                                        <span class="badge badge-success">Đã xác nhận</span>
                                        <span class="badge badge-success">Đã thanh toán</span>
                                    </div>
                                </div>
                                <div class="athlete-actions">
                                    <button class="btn btn-secondary btn-sm">👁️ Chi tiết</button>
                                    <button class="btn btn-warning btn-sm">✏️</button>
                                    <button class="btn btn-danger btn-sm">🗑️</button>
                                </div>
                            </div>
                            <div class="athlete-item">
                                <div class="athlete-info">
                                    <div class="athlete-name">Trần Thị Bình</div>
                                    <div class="athlete-details">
                                        📧 tranthibinh@email.com | 📞 0912345678 | 🎯 Nam đơn 18+<br>
                                        <span class="badge badge-success">Đã xác nhận</span>
                                        <span class="badge badge-success">Đã thanh toán</span>
                                    </div>
                                </div>
                                <div class="athlete-actions">
                                    <button class="btn btn-secondary btn-sm">👁️ Chi tiết</button>
                                    <button class="btn btn-warning btn-sm">✏️</button>
                                    <button class="btn btn-danger btn-sm">🗑️</button>
                                </div>
                            </div>
                            <div class="athlete-item">
                                <div class="athlete-info">
                                    <div class="athlete-name">Lê Văn Cường</div>
                                    <div class="athlete-details">
                                        📧 levanc@email.com | 📞 0923456789 | 🎯 Nam đơn 18+<br>
                                        <span class="badge badge-warning">Chờ xác nhận</span>
                                        <span class="badge badge-danger">Chưa thanh toán</span>
                                    </div>
                                </div>
                                <div class="athlete-actions">
                                    <button class="btn btn-success btn-sm">✅ Xác nhận</button>
                                    <button class="btn btn-secondary btn-sm">👁️</button>
                                    <button class="btn btn-danger btn-sm">🗑️</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🎲 Bốc thăm chia bảng</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            ⚠️ Sau khi bốc thăm, bạn không thể thay đổi danh sách VĐV
                        </div>
                        <div class="grid grid-3">
                            <div class="form-group">
                                <label class="form-label">Chọn nội dung thi đấu *</label>
                                <select class="form-select">
                                    <option value="1" selected>Nam đơn 18+ (64 VĐV)</option>
                                    <option value="2">Nữ đơn 18+ (32 VĐV)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Số lượng bảng</label>
                                <select class="form-select">
                                    <option value="2">2 bảng</option>
                                    <option value="4" selected>4 bảng</option>
                                    <option value="8">8 bảng</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phương thức</label>
                                <select class="form-select">
                                    <option value="auto">Tự động (Random)</option>
                                    <option value="seeded" selected>Theo hạt giống</option>
                                    <option value="manual">Thủ công</option>
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-success">🎲 Bốc thăm tự động</button>
                        <button class="btn btn-primary">✏️ Chia bảng thủ công</button>
                        <button class="btn btn-warning">🔄 Bốc lại</button>
                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Kết quả chia bảng</h4>
                        <div class="group-grid">
                            <div class="group-card">
                                <div class="group-header">BẢNG A</div>
                                <ul class="group-players">
                                    <li>
                                        <span>1. Nguyễn Văn An</span>
                                        <span class="badge badge-warning">⭐ #1</span>
                                    </li>
                                    <li><span>2. Trần Văn Bình</span></li>
                                    <li><span>3. Lê Văn Cường</span></li>
                                    <li><span>4. Phạm Văn Dũng</span></li>
                                </ul>
                            </div>
                            <div class="group-card">
                                <div class="group-header">BẢNG B</div>
                                <ul class="group-players">
                                    <li>
                                        <span>1. Bùi Văn Khoa</span>
                                        <span class="badge badge-warning">⭐ #2</span>
                                    </li>
                                    <li><span>2. Đinh Văn Long</span></li>
                                    <li><span>3. Trương Văn Minh</span></li>
                                    <li><span>4. Lý Văn Nam</span></li>
                                </ul>
                            </div>
                        </div>
                        <button class="btn btn-primary mt-2">💾 Lưu kết quả chia bảng</button>
                    </div>
                </div>
            </div>
            <!-- TAB 3: QUẢN LÝ TRẬN ĐẤU -->
            <div id="matches" class="tab-content">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🎾 Quản lý trận đấu</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary btn-sm">➕ Tạo trận mới</button>
                            <button class="btn btn-success btn-sm">🔄 Tạo lịch tự động</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="match-list">
                            <!-- Match 1 - Completed -->
                            <div class="match-item">
                                <div class="match-header">
                                    <div class="match-info">
                                        <div class="match-title">Trận 1 - Vòng bảng A</div>
                                        <div class="match-details">
                                            📅 20/01/2025 - 08:00 | 🏟️ Sân số 1 | 🎯 Nam đơn 18+<br>
                                            <span class="badge badge-success">Đã hoàn thành</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="match-players">
                                    <div class="player-side"
                                        style="background: linear-gradient(135deg, #4ADE80, #22C55E); color: white;">
                                        <div class="player-name">🏆 Nguyễn Văn An</div>
                                        <div style="font-size: 1.75rem; font-weight: 700; margin-top: 10px;">11 - 11</div>
                                    </div>
                                    <div class="vs-divider">VS</div>
                                    <div class="player-side">
                                        <div class="player-name">Trần Văn Bình</div>
                                        <div style="font-size: 1.75rem; font-weight: 700; margin-top: 10px;">7 - 5</div>
                                    </div>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <button class="btn btn-secondary btn-sm">👁️ Chi tiết</button>
                                    <button class="btn btn-warning btn-sm">✏️ Sửa kết quả</button>
                                </div>
                            </div>
                            <!-- Match 2 - Live -->
                            <div class="match-item" style="border-left-color: #FF6B6B;">
                                <div class="match-header">
                                    <div class="match-info">
                                        <div class="match-title">Trận 2 - Vòng bảng A</div>
                                        <div class="match-details">
                                            📅 20/01/2025 - 09:00 | 🏟️ Sân số 2 | 🎯 Nam đơn 18+<br>
                                            <span class="badge badge-danger status-live">🔴 ĐANG DIỄN RA</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="match-players">
                                    <div class="player-side">
                                        <div class="player-name">Lê Văn Cường</div>
                                        <div class="score-input">
                                            <input type="number" value="9" min="0" max="30">
                                            <input type="number" value="11" min="0" max="30">
                                            <input type="number" value="8" min="0" max="30">
                                        </div>
                                    </div>
                                    <div class="vs-divider">VS</div>
                                    <div class="player-side">
                                        <div class="player-name">Phạm Văn Dũng</div>
                                        <div class="score-input">
                                            <input type="number" value="11" min="0" max="30">
                                            <input type="number" value="7" min="0" max="30">
                                            <input type="number" value="10" min="0" max="30">
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <button class="btn btn-success">💾 Lưu tỷ số</button>
                                    <button class="btn btn-primary">🏁 Kết thúc trận</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- TAB 4: BẢNG XẾP HẠNG -->
            <div id="rankings" class="tab-content">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🏅 Bảng xếp hạng giải đấu</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary btn-sm">📊 Xuất báo cáo</button>
                            <button class="btn btn-success btn-sm">📄 In bảng</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h4 style="margin: 0 0 1.5rem 0; font-weight: 700;">Nam đơn 18+ - Bảng xếp hạng chung</h4>
                        <div style="overflow-x: auto;">
                            <table class="rankings-table">
                                <thead>
                                    <tr>
                                        <th>Hạng</th>
                                        <th>Vận động viên</th>
                                        <th>Bảng</th>
                                        <th>Trận</th>
                                        <th>Thắng</th>
                                        <th>Thua</th>
                                        <th>Tỷ lệ</th>
                                        <th>Điểm</th>
                                        <th>Set</th>
                                        <th>Hiệu số</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="rank-medal rank-1">1</span></td>
                                        <td><strong>Nguyễn Văn An</strong></td>
                                        <td>Bảng A</td>
                                        <td>5</td>
                                        <td>5</td>
                                        <td>0</td>
                                        <td>100%</td>
                                        <td><strong>15</strong></td>
                                        <td>10/0</td>
                                        <td>+110</td>
                                    </tr>
                                    <tr>
                                        <td><span class="rank-medal rank-2">2</span></td>
                                        <td><strong>Bùi Văn Khoa</strong></td>
                                        <td>Bảng B</td>
                                        <td>5</td>
                                        <td>4</td>
                                        <td>1</td>
                                        <td>80%</td>
                                        <td><strong>12</strong></td>
                                        <td>9/2</td>
                                        <td>+85</td>
                                    </tr>
                                    <tr>
                                        <td><span class="rank-medal rank-3">3</span></td>
                                        <td><strong>Ngô Văn Sơn</strong></td>
                                        <td>Bảng C</td>
                                        <td>5</td>
                                        <td>4</td>
                                        <td>1</td>
                                        <td>80%</td>
                                        <td><strong>12</strong></td>
                                        <td>8/3</td>
                                        <td>+72</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Hà Văn Chiến</td>
                                        <td>Bảng D</td>
                                        <td>5</td>
                                        <td>4</td>
                                        <td>1</td>
                                        <td>80%</td>
                                        <td>12</td>
                                        <td>8/3</td>
                                        <td>+68</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<script>
// Court selection toggle
document.querySelectorAll('#courtsGrid label').forEach(label => {
    const checkbox = label.querySelector('input[type="checkbox"]');
    const card = label.querySelector('.court-card');
    
    if (checkbox && card) {
        // Set initial state
        if (checkbox.checked) {
            card.classList.add('selected');
        }
        
        // Toggle on click
        label.addEventListener('click', function(e) {
            e.preventDefault();
            checkbox.checked = !checkbox.checked;
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }
});

// Handle form submission
document.getElementById('courtsForm')?.addEventListener('submit', function(e) {
    const checkedCount = document.querySelectorAll('#courtsGrid input[type="checkbox"]:checked').length;
    if (checkedCount === 0) {
        e.preventDefault();
        alert('Vui lòng chọn ít nhất một sân để lưu');
    }
});

// Handle form reset
document.getElementById('courtsForm')?.addEventListener('reset', function(e) {
    setTimeout(() => {
        document.querySelectorAll('#courtsGrid .court-card').forEach(card => {
            card.classList.remove('selected');
        });
    }, 0);
});
</script>
@endsection
