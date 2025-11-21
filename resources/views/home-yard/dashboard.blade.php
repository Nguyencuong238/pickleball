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
                <button class="config-tab active" onclick="showConfigTab('categories')">
                    🎯 Nội dung thi đấu
                </button>
                <button class="config-tab" onclick="showConfigTab('rounds')">
                    🔄 Vòng đấu
                </button>
                <button class="config-tab" onclick="showConfigTab('brackets')">
                    🏆 Tạo bảng đấu
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
            <!-- TAB 1: NỘI DUNG THI ĐẤU -->
            <div id="categories" class="tab-pane active">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🎯 Nội dung thi đấu</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger" style="border-color: #EF4444; background-color: #FEE2E2;">
                                <strong>⚠️ Lỗi:</strong>
                                <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success" style="border-color: #10B981; background-color: #ECFDF5;">
                                ✅ {{ session('success') }}
                            </div>
                        @endif
                        <div class="alert alert-info">
                            💡 Tạo các nội dung thi đấu khác nhau cho giải đấu
                        </div>
                        @if (!$tournament)
                            <div class="alert alert-warning" style="border-color: #FBBF24; background-color: #FFFBEB;">
                                ⚠️ <strong>Vui lòng tạo giải đấu trước khi thêm nội dung</strong>
                                <p style="margin-top: 0.5rem; font-size: 0.9rem;">Bạn cần tạo giải đấu cơ bản trước, sau đó
                                    mới có thể thêm nội dung thi đấu.</p>
                            </div>
                        @else
                            <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm nội dung mới</h4>
                            <form method="POST"
                                action="{{ route('homeyard.tournaments.categories.store', $tournament->id) }}">
                                @csrf

                                <div class="grid grid-3">
                                    <div class="form-group">
                                        <label class="form-label">Tên nội dung *</label>
                                        <input type="text" name="category_name" class="form-input"
                                            placeholder="VD: Nam đơn 18+" required>
                                        @error('category_name')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
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
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
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
                                        <input type="number" name="max_participants" class="form-input"
                                            placeholder="32" min="4" max="128" required>
                                        @error('max_participants')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Giải thưởng (VNĐ)</label>
                                        <input type="number" name="prize_money" class="form-input"
                                            placeholder="5000000" min="0">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success">➕ Thêm nội dung</button>
                            </form>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách nội dung đã tạo</h4>
                            @if ($tournament && $tournament->categories && $tournament->categories->count() > 0)
                                <div style="overflow-x: auto;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead style="background: #f5f5f5;">
                                            <tr>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Tên</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Loại</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Độ tuổi</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    VĐV tối đa</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Giải thưởng</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tournament->categories as $category)
                                                <tr style="border-bottom: 1px solid #ddd;">
                                                    <td style="padding: 10px;">{{ $category->category_name }}</td>
                                                    <td style="padding: 10px;">
                                                        @switch($category->category_type)
                                                            @case('single_men')
                                                                Đơn nam
                                                            @break

                                                            @case('single_women')
                                                                Đơn nữ
                                                            @break

                                                            @case('double_men')
                                                                Đôi nam
                                                            @break

                                                            @case('double_women')
                                                                Đôi nữ
                                                            @break

                                                            @case('double_mixed')
                                                                Đôi nam nữ
                                                            @break
                                                        @endswitch
                                                    </td>
                                                    <td style="padding: 10px;">{{ $category->age_group }}</td>
                                                    <td style="padding: 10px;">{{ $category->max_participants }}</td>
                                                    <td style="padding: 10px;">
                                                        {{ number_format($category->prize_money ?? 0, 0, ',', '.') }} VNĐ
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <form method="POST"
                                                            action="{{ route('homeyard.tournaments.categories.destroy', [$tournament->id, $category->id]) }}"
                                                            style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Xác nhận xóa?')">🗑️</button>
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

                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 2: VÒNG ĐẤU -->
            <div id="rounds" class="tab-pane">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🔄 Tạo vòng đấu</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            💡 Thiết lập các vòng đấu cho giải (Vòng bảng, Vòng 1/8, Tứ kết, Bán kết, Chung kết)
                        </div>
                        @if (!$tournament)
                            <div class="alert alert-warning">
                                ⚠️ Vui lòng tạo giải đấu trước
                            </div>
                        @else
                            <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm vòng đấu mới</h4>

                            <form method="POST"
                                action="{{ route('homeyard.tournaments.rounds.store', $tournament->id) }}">
                                @csrf

                                <div class="grid grid-3">
                                    <div class="form-group">
                                        <label class="form-label">Tên vòng đấu *</label>
                                        <input type="text" name="round_name" class="form-input"
                                            placeholder="VD: Vòng bảng" required>
                                        @error('round_name')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Ngày thi đấu *</label>
                                        <input type="date" name="start_date" class="form-input" required>
                                        @error('start_date')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Giờ bắt đầu *</label>
                                        <input type="time" name="start_time" class="form-input" required>
                                        @error('start_time')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label">Số thứ tự vòng *</label>
                                        <input type="number" name="round_number" class="form-input" placeholder="1"
                                            min="1" max="20" required>
                                        @error('round_number')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
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
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success">➕ Thêm vòng đấu</button>
                            </form>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách vòng đấu</h4>
                            @if ($tournament && $tournament->rounds && $tournament->rounds->count() > 0)
                                <div class="item-grid">
                                    @foreach ($tournament->rounds as $round)
                                        <div class="item-card">
                                            <strong>{{ $round->round_name }}</strong>
                                            <p>{{ \Carbon\Carbon::parse($round->start_date)->format('d/m/Y') }} -
                                                {{ $round->start_time }}</p>
                                            <form method="POST"
                                                action="{{ route('homeyard.tournaments.rounds.destroy', [$tournament->id, $round->id]) }}"
                                                style="display: inline; margin-top: 10px;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Xác nhận xóa?')">🗑️ Xóa</button>
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
            </div>

            <!-- TAB 3: TẠO BẢNG ĐẤU -->
            <div id="brackets" class="tab-pane">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🏆 Tạo bảng đấu</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger" style="border-color: #EF4444; background-color: #FEE2E2;">
                                <strong>⚠️ Lỗi:</strong>
                                <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success" style="border-color: #10B981; background-color: #ECFDF5;">
                                ✅ {{ session('success') }}
                            </div>
                        @endif
                        <div class="alert alert-info">
                            💡 Tạo các bảng đấu cho nội dung thi đấu
                        </div>
                        @if ($tournament)
                            <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm bảng mới</h4>
                            <form method="POST"
                                action="{{ route('homeyard.tournaments.groups.store', $tournament->id) }}">
                                @csrf

                                <div class="grid grid-3">
                                    <div class="form-group">
                                        <label class="form-label">Chọn nội dung thi đấu *</label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">-- Chọn nội dung --</option>
                                            @if ($tournament && $tournament->categories)
                                                @foreach ($tournament->categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->category_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('category_id')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Chọn vòng đấu</label>
                                        <select name="round_id" class="form-select">
                                            <option value="">-- Không chọn vòng --</option>
                                            @if ($tournament && $tournament->rounds)
                                                @foreach ($tournament->rounds as $round)
                                                    <option value="{{ $round->id }}">{{ $round->round_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('round_id')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Tên bảng (VD: A, B, C) *</label>
                                        <input type="text" name="group_name" class="form-input"
                                            placeholder="VD: Bảng A" required>
                                        @error('group_name')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-3">
                                    <div class="form-group">
                                        <label class="form-label">Mã bảng (VD: A, GRP1) *</label>
                                        <input type="text" name="group_code" class="form-input" placeholder="VD: A"
                                            required>
                                        @error('group_code')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Số VĐV mỗi bảng *</label>
                                        <input type="number" name="max_participants" class="form-input" placeholder="4"
                                            min="2" max="128" required>
                                        @error('max_participants')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Số người lọt vào vòng sau *</label>
                                        <input type="number" name="advancing_count" class="form-input" placeholder="2"
                                            min="1" required
                                            title="Ví dụ: Bảng 4 VĐV, nhập 2 = top 2 tiến lên vòng tứ kết">
                                        <small style="color: #666; margin-top: 0.25rem; display: block;">VD: Bảng có 4 VĐV,
                                            nhập 2 = top 2 tiến lên vòng tiếp theo</small>
                                        @error('advancing_count')
                                            <span class="text-danger"
                                                style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="description" class="form-input" placeholder="Ghi chú về bảng đấu (tuỳ chọn)" rows="3"></textarea>
                                </div>

                                <button type="submit" class="btn btn-success">➕ Thêm bảng</button>
                            </form>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách bảng đã tạo</h4>
                            @if ($tournament && $tournament->groups && $tournament->groups->count() > 0)
                                <div style="overflow-x: auto;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead style="background: #f5f5f5;">
                                            <tr>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Tên bảng</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Mã</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Nội dung</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Vòng</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    VĐV / Tối đa</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Lọt vào vòng sau</th>
                                                <th
                                                    style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                    Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tournament->groups as $group)
                                                <tr style="border-bottom: 1px solid #ddd;">
                                                    <td style="padding: 10px;"><strong>{{ $group->group_name }}</strong>
                                                    </td>
                                                    <td style="padding: 10px;">{{ $group->group_code }}</td>
                                                    <td style="padding: 10px;">
                                                        {{ $group->category->category_name ?? 'N/A' }}</td>
                                                    <td style="padding: 10px;">{{ $group->round->round_name ?? 'Không' }}
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        {{ $group->current_participants }}/{{ $group->max_participants }}
                                                    </td>
                                                    <td style="padding: 10px;">{{ $group->advancing_count }}</td>
                                                    <td style="padding: 10px;">
                                                        <form method="POST"
                                                            action="{{ route('homeyard.tournaments.groups.destroy', [$tournament->id, $group->id]) }}"
                                                            style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Xác nhận xóa?')">🗑️</button>
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
                        @else
                            <div class="alert alert-warning">
                                ⚠️ Vui lòng tạo giải đấu trước
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 4: QUẢN LÝ VĐV -->
            <div id="athletes" class="tab-pane">
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
                                        <div class="stat-value">{{ $athletes->count() ?? 0 }}</div>
                                    </div>
                                    <div class="stat-icon primary">👥</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div>
                                        <div class="stat-label">Đã phê duyệt</div>
                                        <div class="stat-value">{{ $athletes->where('status', 'approved')->count() ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="stat-icon success">✅</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div>
                                        <div class="stat-label">Chờ phê duyệt</div>
                                        <div class="stat-value">{{ $athletes->where('status', 'pending')->count() ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="stat-icon warning">⏳</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div>
                                        <div class="stat-label">Đã thanh toán</div>
                                        <div class="stat-value">
                                            {{ $athletes->where('payment_status', 'paid')->count() ?? 0 }}</div>
                                    </div>
                                    <div class="stat-icon success">💰</div>
                                </div>
                            </div>
                        </div>
                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách VĐV đăng ký</h4>

                        @if ($athletes && $athletes->count() > 0)
                            <div class="athlete-list">
                                @foreach ($athletes as $athlete)
                                    <div class="athlete-item"
                                        style="@if ($athlete->status === 'rejected') border-left-color: #EF4444; @elseif($athlete->status === 'pending') border-left-color: #F59E0B; @endif">
                                        <div class="athlete-info">
                                            <div class="athlete-name">{{ $athlete->athlete_name }}</div>
                                            <div class="athlete-details">
                                                📧 {{ $athlete->email }} | 📞 {{ $athlete->phone }} | 🎯
                                                {{ $athlete->category->category_name ?? 'N/A' }}<br>
                                                @if ($athlete->status === 'pending')
                                                    <span class="badge badge-warning">⏳ Chờ phê duyệt</span>
                                                @elseif ($athlete->status === 'approved')
                                                    <span class="badge badge-success">✅ Đã phê duyệt</span>
                                                @elseif ($athlete->status === 'rejected')
                                                    <span class="badge badge-danger">❌ Từ chối</span>
                                                @endif
                                                @if ($athlete->payment_status === 'paid')
                                                    <span class="badge badge-success">💰 Đã thanh toán</span>
                                                @elseif ($athlete->payment_status === 'pending')
                                                    <span class="badge badge-warning">⏳ Chờ thanh toán</span>
                                                @else
                                                    <span class="badge badge-danger">❌ Chưa thanh toán</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="athlete-actions">
                                            <button class="btn btn-secondary btn-sm">👁️ Chi tiết</button>
                                            @if ($athlete->status === 'pending')
                                                <form method="POST"
                                                    action="{{ route('homeyard.athletes.approve', [$tournament->id, $athlete->id]) }}"
                                                    style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">✅ Phê
                                                        duyệt</button>
                                                </form>
                                                <form method="POST"
                                                    action="{{ route('homeyard.athletes.reject', [$tournament->id, $athlete->id]) }}"
                                                    style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Từ chối đơn đăng ký?')">❌ Từ chối</button>
                                                </form>
                                            @else
                                                <button class="btn btn-warning btn-sm">✏️ Sửa</button>
                                                <form method="POST"
                                                    action="{{ route('homeyard.tournaments.athletes.remove', [$tournament->id, $athlete->id]) }}"
                                                    style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Xóa VĐV?')">🗑️</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #999;">
                                <p>Chưa có VĐV nào đăng ký cho giải đấu này.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🎲 Bốc thăm chia bảng</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            ⚠️ Sau khi bốc thăm, bạn có thể bốc lại bất cứ lúc nào
                        </div>
                        <div id="drawAlert" style="display: none;"></div>

                        <div class="grid grid-3">
                            <div class="form-group">
                                <label class="form-label">Chọn nội dung thi đấu *</label>
                                <select id="categorySelect" class="form-select">
                                    <option value="">-- Chọn nội dung --</option>
                                    @if ($tournament && $tournament->categories)
                                        @foreach ($tournament->categories as $category)
                                            <option value="{{ $category->id }}"
                                                data-athletes="{{ $tournament->athletes->where('category_id', $category->id)->where('status', 'approved')->count() }}">
                                                {{ $category->category_name }}
                                                ({{ $tournament->athletes->where('category_id', $category->id)->where('status', 'approved')->count() }}
                                                VĐV)
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Chọn bảng cần chia</label>
                                <select id="groupSelect" class="form-select">
                                    <option value="">-- Tự động chia vào bảng đã tạo --</option>
                                    @if ($tournament && $tournament->groups)
                                        @foreach ($tournament->groups as $group)
                                            <option value="{{ $group->id }}"
                                                data-category="{{ $group->category_id }}">
                                                {{ $group->group_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phương thức</label>
                                <select id="drawMethod" class="form-select">
                                    <option value="auto">Tự động (Random)</option>
                                    <option value="seeded" selected>Theo hạt giống (Seeded)</option>
                                </select>
                            </div>
                        </div>
                        <button id="drawBtn" class="btn btn-success">🎲 Bốc thăm</button>
                        <button id="resetBtn" class="btn btn-warning">🔄 Bốc lại</button>
                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Kết quả chia bảng</h4>
                        <div id="groupResultsContainer" style="display: none;">
                            <div id="groupResults" class="group-grid">
                                <!-- Kết quả sẽ được hiển thị ở đây -->
                            </div>
                            {{-- <button id="saveResultBtn" class="btn btn-primary mt-2">💾 Lưu kết quả</button> --}}
                        </div>
                        <div id="noResultsMsg" style="text-align: center; padding: 2rem; color: #999;">
                            <p>Hãy chọn nội dung thi đấu và bốc thăm để xem kết quả</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- TAB 5: QUẢN LÝ TRẬN ĐẤU -->
            <div id="matches" class="tab-pane">
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
            <!-- TAB 6: BẢNG XẾP HẠNG -->
            <div id="rankings" class="tab-pane">
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
        // Save and restore active tab
        function showConfigTab(tabName) {
            // Save tab TRƯỚC
            localStorage.setItem('activeTab', tabName);

            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-pane');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Show selected tab
            const selectedTab = document.getElementById(tabName);
            if (selectedTab) {
                selectedTab.classList.add('active');
            }

            // Update active button
            const buttons = document.querySelectorAll('.config-tab');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }

        // Lưu tab trước khi form submit (rất quan trọng!)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const tabPane = form.closest('.tab-pane');
            if (tabPane) {
                localStorage.setItem('activeTab', tabPane.id);
            }
        }, true);

        // Restore tab on page load
        window.addEventListener('DOMContentLoaded', function() {
            const activeTab = localStorage.getItem('activeTab') || 'categories';

            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-pane');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Show saved tab
            const selectedTab = document.getElementById(activeTab);
            if (selectedTab) {
                selectedTab.classList.add('active');
            }

            // Update buttons
            const buttons = document.querySelectorAll('.config-tab');
            buttons.forEach(btn => btn.classList.remove('active'));
            const activeButton = Array.from(buttons).find(btn =>
                btn.getAttribute('onclick').includes(`'${activeTab}'`)
            );
            if (activeButton) {
                activeButton.classList.add('active');
            }

            // Initialize draw functionality
            initializeDraw();
        });

        // Draw/Lottery Functionality
        function initializeDraw() {
            const drawBtn = document.getElementById('drawBtn');
            const resetBtn = document.getElementById('resetBtn');
            const categorySelect = document.getElementById('categorySelect');
            const drawMethod = document.getElementById('drawMethod');
            const groupSelect = document.getElementById('groupSelect');

            if (drawBtn) {
                drawBtn.addEventListener('click', function() {
                    if (!categorySelect.value) {
                        showAlert('Vui lòng chọn nội dung thi đấu', 'warning');
                        return;
                    }

                    const categoryId = categorySelect.value;
                    const method = drawMethod.value;
                    const tournamentId = {{ $tournament->id ?? 0 }};

                    // Lấy danh sách bảng cho nội dung này
                    const selectedGroups = Array.from(groupSelect.options)
                        .filter(opt => opt.dataset.category && opt.dataset.category == categoryId && opt.value)
                        .map(opt => ({
                            id: opt.value,
                            name: opt.text
                        }));

                    console.log('Category ID:', categoryId);
                    console.log('All options:', Array.from(groupSelect.options).map(opt => ({
                        value: opt.value,
                        category: opt.dataset.category,
                        text: opt.text
                    })));
                    console.log('Selected Groups:', selectedGroups);

                    if (selectedGroups.length === 0) {
                        showAlert('Không có bảng nào cho nội dung này. Vui lòng tạo bảng trước.', 'warning');
                        return;
                    }

                    drawBtn.disabled = true;
                    drawBtn.innerHTML = '⏳ Đang bốc thăm...';

                    fetch(`/homeyard/tournaments/${tournamentId}/draw`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                category_id: categoryId,
                                number_of_groups: selectedGroups.length,
                                draw_method: method
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => {
                                    console.error('Response status:', response.status);
                                    console.error('Response body:', text);
                                    throw new Error(`HTTP ${response.status}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showAlert('✅ ' + data.message, 'success');
                                displayResults(data.athletes);
                            } else {
                                showAlert('❌ ' + data.message, 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Draw error details:', error);
                            showAlert('❌ ' + (error.message || 'Lỗi không xác định'), 'danger');
                        })
                        .finally(() => {
                            drawBtn.disabled = false;
                            drawBtn.innerHTML = '🎲 Bốc thăm';
                        });
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (!categorySelect.value) {
                        showAlert('Vui lòng chọn nội dung thi đấu', 'warning');
                        return;
                    }

                    if (!confirm('Bạn có chắc chắn muốn xóa kết quả bốc thăm hiện tại?')) {
                        return;
                    }

                    const categoryId = categorySelect.value;
                    const tournamentId = {{ $tournament->id ?? 0 }};

                    resetBtn.disabled = true;
                    resetBtn.innerHTML = '⏳ Đang reset...';

                    fetch(`/homeyard/tournaments/${tournamentId}/reset-draw`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                category_id: categoryId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAlert('✅ ' + data.message, 'success');
                                document.getElementById('groupResultsContainer').style.display = 'none';
                                document.getElementById('noResultsMsg').style.display = 'block';
                            } else {
                                showAlert('❌ ' + data.message, 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('❌ Lỗi khi reset: ' + error, 'danger');
                        })
                        .finally(() => {
                            resetBtn.disabled = false;
                            resetBtn.innerHTML = '🔄 Bốc lại';
                        });
                });
            }
        }

        function displayResults(groupedAthletes) {
            const container = document.getElementById('groupResults');
            const resultsContainer = document.getElementById('groupResultsContainer');
            const noResultsMsg = document.getElementById('noResultsMsg');

            if (!groupedAthletes || groupedAthletes.length === 0) {
                resultsContainer.style.display = 'none';
                noResultsMsg.style.display = 'block';
                return;
            }

            container.innerHTML = '';

            groupedAthletes.forEach((group, index) => {
                const groupCard = document.createElement('div');
                groupCard.className = 'group-card';

                let athletesHtml = '';
                group.athletes.forEach((athlete, position) => {
                    const seedBadge = athlete.seed_number ?
                        `<span class="badge badge-warning">⭐ #${athlete.seed_number}</span>` :
                        '';
                    athletesHtml += `
                        <li>
                            <span>${position + 1}. ${athlete.name}</span>
                            ${seedBadge}
                        </li>
                    `;
                });

                groupCard.innerHTML = `
                    <div class="group-header">${group.group_name} (${group.group_code})</div>
                    <ul class="group-players">
                        ${athletesHtml}
                    </ul>
                `;

                container.appendChild(groupCard);
            });

            resultsContainer.style.display = 'block';
            noResultsMsg.style.display = 'none';
        }

        function showAlert(message, type) {
            const alertDiv = document.getElementById('drawAlert');
            const alertClass =
            `alert alert-${type === 'warning' ? 'warning' : (type === 'success' ? 'success' : 'danger')}`;

            alertDiv.innerHTML = message;
            alertDiv.className = alertClass;
            alertDiv.style.display = 'block';

            if (type === 'success') {
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            }
        }
    </script>

@endsection
