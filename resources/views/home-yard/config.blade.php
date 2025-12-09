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
                            <a href="{{ route('homeyard.overview') }}" class="breadcrumb-link">🏠 Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">
                            <a href="{{ route('homeyard.tournaments.index') }}" class="breadcrumb-link">Giải đấu</a>
                        </span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">Cấu hình</span>
                    </div>
                </div>
                <div class="header-right">
                    {{-- <button class="btn btn-success">💾 Lưu thay đổi</button>
                    <button class="btn btn-secondary">👁️ Xem trước</button>
                    <div class="header-notifications">
                        <button class="notification-btn">
                            <span>🔔</span>
                            <span class="notification-badge">5</span>
                        </button>
                    </div> --}}
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
                    <h2 class="tournament-header-title">{{ $tournament->name }}</h2>
                    <div class="tournament-header-meta">
                        <div class="header-meta-item">
                            <span>📅</span>
                            <span>{{ $tournament->start_date->format('j \\T\\h\\á\\n\\g n, Y') }} -
                                {{ $tournament->end_date->format('j \\T\\h\\á\\n\\g n, Y') }}</span>
                        </div>
                        <div class="header-meta-item">
                            <span>📍</span>
                            <span>{{ $tournament->location }}</span>
                        </div>
                        <div class="header-meta-item">
                            <span>👥</span>
                            <span>{{ $tournament->max_participants }} Vận động viên</span>
                        </div>
                        <div class="header-meta-item">
                            <span>💰</span>
                            <span>Giải thưởng: {{ number_format($tournament->prizes, 0, ',', '.') }} VNĐ</span>
                        </div>
                        <div class="header-meta-item">
                            @if ($tournament->start_date < now() && $tournament->end_date > now())
                                <span class="badge badge-success">Đang diễn ra</span>
                            @elseif($tournament->start_date > now())
                                <span class="badge badge-warning">Sắp diễn ra</span>
                            @elseif($tournament->end_date < now())
                                <span class="badge badge-danger">Đã kết thúc</span>
                            @endif
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
                <button class="config-tab" onclick="showConfigTab('matchManagement')">
                    ⚡ Tạo trận mới
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
                        <div class="alert alert-info">
                            💡 Tạo các nội dung thi đấu khác nhau cho giải đấu
                        </div>
                        <form method="POST" class="mt-3"
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
                                    <input type="number" name="max_participants" class="form-input" placeholder="32"
                                        min="4" max="128" required>
                                    @error('max_participants')
                                        <span class="text-danger"
                                            style="font-size: 0.85rem; color: #ef4444;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Giải thưởng (VNĐ)</label>
                                    <input type="number" name="prize_money" class="form-input" placeholder="5000000"
                                        min="0">
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
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Tên</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Loại</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Độ tuổi</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                VĐV tối đa</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Giải thưởng</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
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
                        <div class="alert alert-info mb-3">
                            💡 Thiết lập các vòng đấu cho giải (Vòng bảng, Vòng 1/8, Tứ kết, Bán kết, Chung kết)
                        </div>

                        <form method="POST" action="{{ route('homeyard.tournaments.rounds.store', $tournament->id) }}">
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
                        <div class="alert alert-info mb-3">
                            💡 Tạo các bảng đấu cho nội dung thi đấu
                        </div>
                        <form method="POST" action="{{ route('homeyard.tournaments.groups.store', $tournament->id) }}">
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
                                    <input type="text" name="group_name" class="form-input" placeholder="VD: Bảng A"
                                        required>
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
                                    <label class="form-label">Số VĐV *</label>
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
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Tên bảng</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Mã</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Nội dung</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Vòng</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                VĐV / Tối đa</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Lọt vào vòng sau</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
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
                    </div>
                </div>
            </div>

            <!-- TAB 4: QUẢN LÝ VĐV -->
            <div id="athletes" class="tab-pane">
                <div class="card fade-in mb-3">
                    <div class="card-header">
                        <h3 class="card-title">👥 Quản lý danh sách vận động viên</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary btn-sm" onclick="openAddAthleteModal()">➕ Thêm VĐV</button>
                            <button class="btn btn-success btn-sm" onclick="exportAthletes()">📊 Xuất Excel</button>
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
                                            <button class="btn btn-primary btn-sm"
                                                onclick="openViewAthleteModal({{ $athlete->id }}, '{{ $athlete->athlete_name }}', '{{ $athlete->email }}', '{{ $athlete->phone }}', {{ $athlete->category_id ?? 'null' }})">👁️
                                                Chi tiết</button>
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
                                                <button class="btn btn-warning btn-sm"
                                                    onclick="openEditAthleteModal({{ $athlete->id }}, '{{ $athlete->athlete_name }}', '{{ $athlete->email }}', '{{ $athlete->phone }}', {{ $athlete->category_id ?? 'null' }})">✏️
                                                    Sửa</button>
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
                        <div class="alert alert-warning mb-2">
                            ⚠️ Sau khi bốc thăm, bạn có thể bốc lại bất cứ lúc nào
                        </div>

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
                                                data-category="{{ $group->category_id }}"
                                                data-max-participants="{{ $group->max_participants }}">
                                                {{ $group->group_name }}
                                                ({{ $group->current_participants }}/{{ $group->max_participants }})
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

            <!-- TAB 5B: TẠO TRẬN MỚI -->
            <div id="matchManagement" class="tab-pane">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">⚡ Tạo trận đấu mới</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            💡 Tạo trận đấu mới cho giải đấu này
                        </div>


                        <button type="button" class="btn btn-primary btn-sm" onclick="openCreateMatchModal()">➕ Tạo
                            trận mới</button>

                        <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách trận đấu</h4>
                        @if ($tournament && $tournament->matches && $tournament->matches->count() > 0)
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead style="background: #f5f5f5;">
                                        <tr>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                VĐV 1</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                VĐV 2</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Nội dung</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Vòng</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Thời gian</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Trạng thái</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tournament->matches as $match)
                                            <tr style="border-bottom: 1px solid #ddd;">
                                                <td style="padding: 10px;">
                                                    {{ $match->athlete1->athlete_name ?? 'N/A' }}</td>
                                                <td style="padding: 10px;">
                                                    {{ $match->athlete2->athlete_name ?? 'N/A' }}</td>
                                                <td style="padding: 10px;">
                                                    {{ $match->category->category_name ?? 'N/A' }}</td>
                                                <td style="padding: 10px;">{{ $match->round->round_name ?? 'N/A' }}
                                                </td>
                                                <td style="padding: 10px;">
                                                    {{ $match->match_date ? \Carbon\Carbon::parse($match->match_date)->format('d/m/Y') : '' }}
                                                    {{ $match->match_time ? \Carbon\Carbon::parse($match->match_time)->format('H:i') : '' }}
                                                </td>
                                                <td style="padding: 10px;">
                                                    @if ($match->status === 'scheduled')
                                                        <span class="badge badge-warning">⏳ Chờ thi đấu</span>
                                                    @elseif ($match->status === 'ready')
                                                        <span class="badge badge-info">📋 Sẵn sàng</span>
                                                    @elseif ($match->status === 'in_progress')
                                                        <span class="badge badge-danger">🔴 Đang diễn ra</span>
                                                    @elseif ($match->status === 'completed')
                                                        <span class="badge badge-success">✅ Hoàn thành</span>
                                                    @elseif ($match->status === 'cancelled')
                                                        <span class="badge badge-secondary">❌ Hủy</span>
                                                    @elseif ($match->status === 'postponed')
                                                        <span class="badge badge-warning">⏸️ Hoãn lại</span>
                                                    @elseif ($match->status === 'bye')
                                                        <span class="badge badge-light">🎯 Bye</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ $match->status }}</span>
                                                    @endif
                                                </td>
                                                <td style="padding: 10px;">
                                                    <button class="btn btn-warning btn-sm"
                                                        onclick="openEditMatchModal({{ $match->id }}, '{{ $match->athlete1_id }}', '{{ $match->athlete2_id }}', '{{ $match->category_id }}', '{{ $match->round_id }}', '{{ $match->match_date ? \Carbon\Carbon::parse($match->match_date)->format('Y-m-d') : '' }}', '{{ $match->match_time ? \Carbon\Carbon::parse($match->match_time)->format('H:i') : '' }}', '{{ $match->group_id }}', '{{ $match->status }}', '{{ $match->referee_id }}')">✏️</button>
                                                    <form method="POST"
                                                        action="{{ route('homeyard.tournaments.matches.destroy', [$tournament->id, $match->id]) }}"
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
                                <p>Chưa có trận đấu nào. Hãy tạo trận mới ở trên.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 6: BẢNG XẾP HẠNG -->
            <div id="rankings" class="tab-pane">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">🏅 Bảng xếp hạng VĐV</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary btn-sm" onclick="printRankings()">📄 In bảng</button>
                            <button class="btn btn-success btn-sm" onclick="exportRankingsExcel()">📊 Xuất Excel</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-2">
                            💡 Bảng xếp hạng sắp xếp theo: Điểm > Trận thắng > Hiệu số game
                        </div>

                        <!-- Filter Controls -->
                        <div
                            style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; max-width: 600px;">
                            <div>
                                <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Nội dung thi
                                    đấu:</label>
                                <select id="filterCategory" class="form-select"
                                    onchange="updateGroupFilter(); loadRankings()">
                                    <option value="">-- Tất cả nội dung --</option>
                                    @if ($tournament && $tournament->categories)
                                        @foreach ($tournament->categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Bảng đấu:</label>
                                <select id="filterGroup" class="form-select" onchange="loadRankings()">
                                    <option value="">-- Tất cả bảng --</option>
                                    @if ($tournament && $tournament->groups)
                                        @foreach ($tournament->groups as $group)
                                            <option value="{{ $group->id }}"
                                                data-category-id="{{ $group->category_id }}">{{ $group->group_name }}
                                                ({{ $group->category->category_name ?? 'N/A' }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Rankings Table -->
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; background: white;">
                                <thead
                                    style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; text-align: center;">
                                    <tr>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 70px;">
                                            XH</th>
                                        <th
                                            style="padding: 12px; text-align: left; border-bottom: 2px solid #667eea; font-weight: 700;">
                                            Tên VĐV</th>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 100px;">
                                            Nội Dung</th>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 80px;">
                                            Trận</th>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 80px;">
                                            🏆 Thắng</th>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 80px;">
                                            ❌ Thua</th>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 100px;">
                                            ⭐ Điểm</th>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 100px;">
                                            Set W/L</th>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 100px;">
                                            Hiệu Số Game</th>
                                        <th
                                            style="padding: 12px; text-align: center; border-bottom: 2px solid #667eea; font-weight: 700; width: 100px;">
                                            % Thắng</th>
                                    </tr>
                                </thead>
                                <tbody id="rankingsTableBody">
                                    <tr>
                                        <td colspan="10" style="padding: 40px; text-align: center; color: #999;">
                                            ⏳ Đang tải dữ liệu...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div id="paginationControls"
                            style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
                            <!-- Generated by JavaScript -->
                        </div>

                        <!-- Statistics Cards -->
                         <div class="statistics-cards"
                             style="margin-top: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                             <div
                                 style="background: linear-gradient(135deg, #2FE8C4 0%, #1FDDAF 100%); color: white; padding: 1.5rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 8px 16px rgba(47, 232, 196, 0.14), inset 0 1px 0 rgba(255,255,255,0.15); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default; position: relative; overflow: hidden;">
                                 <div style="position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%); pointer-events: none;"></div>
                                 <div style="font-size: 0.75rem; opacity: 0.82; margin-bottom: 0.75rem; font-weight: 500; letter-spacing: 0.5px; text-transform: uppercase;">🥇 VĐV Hạng 1</div>
                                 <div style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px;" id="topAthlete">-</div>
                             </div>
                             <div
                                 style="background: linear-gradient(135deg, #35E5C2 0%, #1DD9B5 100%); color: white; padding: 1.5rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 8px 16px rgba(53, 229, 194, 0.14), inset 0 1px 0 rgba(255,255,255,0.15); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default; position: relative; overflow: hidden;">
                                 <div style="position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%); pointer-events: none;"></div>
                                 <div style="font-size: 0.75rem; opacity: 0.82; margin-bottom: 0.75rem; font-weight: 500; letter-spacing: 0.5px; text-transform: uppercase;">📊 Tổng Trận Đấu</div>
                                 <div style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px;" id="totalMatches">0</div>
                             </div>
                             <div
                                 style="background: linear-gradient(135deg, #28E0BC 0%, #10D4A3 100%); color: white; padding: 1.5rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 8px 16px rgba(40, 224, 188, 0.14), inset 0 1px 0 rgba(255,255,255,0.15); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default; position: relative; overflow: hidden;">
                                 <div style="position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%); pointer-events: none;"></div>
                                 <div style="font-size: 0.75rem; opacity: 0.82; margin-bottom: 0.75rem; font-weight: 500; letter-spacing: 0.5px; text-transform: uppercase;">👥 Tổng VĐV</div>
                                 <div style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px;" id="totalAthletes">0</div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: QUẢN LÝ VĐV -->
            <div id="athletes" class="tab-pane">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">👥 Quản lý Vận động viên</h3>
                        <div class="card-actions">
                            <button class="btn btn-success btn-sm" onclick="openAddAthleteModal()">➕ Thêm VĐV</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            💡 Quản lý danh sách vận động viên tham gia giải đấu
                        </div>

                        @if ($tournament && $tournament->athletes && $tournament->athletes->count() > 0)
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead style="background: #f5f5f5;">
                                        <tr>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Tên
                                                VĐV</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Email</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Điện thoại</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Nội
                                                dung</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Trạng thái</th>
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
                                                Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tournament->athletes as $athlete)
                                            <tr style="border-bottom: 1px solid #ddd;">
                                                <td style="padding: 10px;">{{ $athlete->athlete_name }}</td>
                                                <td style="padding: 10px;">{{ $athlete->email }}</td>
                                                <td style="padding: 10px;">{{ $athlete->phone }}</td>
                                                <td style="padding: 10px;">
                                                    @if ($athlete->category)
                                                        {{ $athlete->category->category_name }}
                                                    @else
                                                        <span style="color: #999;">-</span>
                                                    @endif
                                                </td>
                                                <td style="padding: 10px;">
                                                    @if ($athlete->status === 'approved')
                                                        <span class="badge badge-success">✅ Duyệt</span>
                                                    @elseif ($athlete->status === 'pending')
                                                        <span class="badge badge-warning">⏳ Chờ duyệt</span>
                                                    @else
                                                        <span class="badge badge-danger">❌ Từ chối</span>
                                                    @endif
                                                </td>
                                                <td style="padding: 10px;">
                                                    <button class="btn btn-primary btn-sm"
                                                        onclick="openViewAthleteModal({{ $athlete->id }}, '{{ $athlete->athlete_name }}', '{{ $athlete->email }}', '{{ $athlete->phone }}', {{ $athlete->category_id ?? 'null' }})">👁️
                                                        Xem</button>
                                                    <button class="btn btn-warning btn-sm"
                                                        onclick="openEditAthleteModal({{ $athlete->id }}, '{{ $athlete->athlete_name }}', '{{ $athlete->email }}', '{{ $athlete->phone }}', {{ $athlete->category_id ?? 'null' }})">✏️
                                                        Sửa</button>
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="deleteAthlete({{ $athlete->id }})">🗑️ Xóa</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #999;">
                                <p>Chưa có vận động viên nào. Hãy thêm VĐV mới ở trên.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: TẠO TRẬN MỚI -->
        <div id="createMatchModal"
            style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div
                style="background-color: var(--bg-white); margin: 5% auto; padding: 2rem; border-radius: var(--radius-xl); width: 90%; max-width: 600px; box-shadow: var(--shadow-lg);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">➕ Tạo Trận Đấu Mới</h2>
                    <button
                        style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                        onclick="closeCreateMatchModal()">×</button>
                </div>

                <form id="createMatchForm">
                    <!-- Bước 1: Chọn nội dung thi đấu -->
                    <div class="form-group">
                        <label class="form-label">🎯 Bước 1: Chọn nội dung thi đấu *</label>
                        <select id="matchCategoryId" name="category_id" class="form-select" required>
                            <option value="">-- Chọn nội dung --</option>
                            @if ($tournament && $tournament->categories)
                                @foreach ($tournament->categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Bước 2: Chọn VĐV thuộc nội dung thi đấu đó -->
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">👤 Bước 2: Chọn VĐV 1 *</label>
                            <select id="athlete1Select" name="athlete1_id" class="form-select" required disabled>
                                <option value="">-- Hãy chọn nội dung thi đấu trước --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">👤 Chọn VĐV 2 *</label>
                            <select id="athlete2Select" name="athlete2_id" class="form-select" required disabled>
                                <option value="">-- Hãy chọn nội dung thi đấu trước --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Chọn vòng đấu -->
                    <div class="form-group">
                        <label class="form-label">🔄 Vòng đấu (Round)</label>
                        <select name="round_id" class="form-select" required>
                            <option value="">-- Chọn vòng (tuỳ chọn) --</option>
                            @if ($tournament && $tournament->rounds)
                                @foreach ($tournament->rounds as $round)
                                    <option value="{{ $round->id }}">{{ $round->round_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Chọn bảng/nhóm -->
                    <div class="form-group">
                        <label class="form-label">👥 Bảng/Nhóm (Group)</label>
                        <select id="matchGroupSelect" name="group_id" class="form-select" disabled>
                            <option value="">-- Chọn nội dung thi đấu trước --</option>
                        </select>
                    </div>

                    <!-- Ngày + Giờ bắt đầu -->
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">📅 Ngày bắt đầu *</label>
                            <input type="date" name="match_date" class="form-select" required
                                style="cursor: pointer;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">🕐 Giờ bắt đầu *</label>
                            <input type="time" name="match_time" class="form-select" required
                                style="cursor: pointer;">
                        </div>
                    </div>

                    <!-- Trạng thái trận đấu -->
                    <div class="form-group">
                        <label class="form-label">📊 Trạng thái trận đấu</label>
                        <select name="status" class="form-select">
                            <option value="scheduled">⏳ Chờ thi đấu</option>
                            <option value="ready">📋 Sẵn sàng</option>
                            <option value="in_progress">🔴 Đang diễn ra</option>
                            <option value="completed">✅ Hoàn thành</option>
                            <option value="cancelled">❌ Hủy</option>
                            <option value="postponed">⏸️ Hoãn lại</option>
                            <option value="bye">🎯 Bye</option>
                        </select>
                    </div>

                    <!-- Assign Referee -->
                    <div class="form-group">
                        <label class="form-label">Trọng tài (Referee)</label>
                        <select name="referee_id" id="matchRefereeId" class="form-select">
                            <option value="">--Không chỉ định trọng tài --</option>
                            @if (isset($referees) && $referees->count() > 0)
                                @foreach ($referees as $referee)
                                    <option value="{{ $referee->id }}">{{ $referee->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <small style="color: var(--text-light); font-size: 0.75rem;">Chi co the chon trong tai da duoc gan vao giai dau nay</small>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-success" id="submitMatchBtn">✅ Tạo trận</button>
                        <button type="button" class="btn btn-secondary" onclick="closeCreateMatchModal()">❌ Hủy</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: CHỈNH SỬA TRẬN ĐẤU -->
        <div id="editMatchModal"
            style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div
                style="background-color: var(--bg-white); margin: 5% auto; padding: 2rem; border-radius: var(--radius-xl); width: 90%; max-width: 600px; box-shadow: var(--shadow-lg);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">✏️ Chỉnh Sửa Trận Đấu</h2>
                    <button
                        style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                        onclick="closeEditMatchModal()">×</button>
                </div>

                <form id="editMatchForm">
                    <input type="hidden" id="editMatchId" name="match_id" value="">

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">VĐV 1 *</label>
                            <select id="editAthlete1" name="athlete1_id" class="form-select" required>
                                <option value="">-- Chọn VĐV --</option>
                                @if ($tournament && $tournament->athletes)
                                    @foreach ($tournament->athletes as $athlete)
                                        <option value="{{ $athlete->id }}">{{ $athlete->athlete_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">VĐV 2 *</label>
                            <select id="editAthlete2" name="athlete2_id" class="form-select" required>
                                <option value="">-- Chọn VĐV --</option>
                                @if ($tournament && $tournament->athletes)
                                    @foreach ($tournament->athletes as $athlete)
                                        <option value="{{ $athlete->id }}">{{ $athlete->athlete_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Nội dung thi đấu (Category) *</label>
                            <select id="editCategory" name="category_id" class="form-select" required>
                                <option value="">-- Chọn nội dung --</option>
                                @if ($tournament && $tournament->categories)
                                    @foreach ($tournament->categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vòng đấu (Round) *</label>
                            <select id="editRound" name="round_id" class="form-select" required>
                                <option value="">-- Chọn vòng --</option>
                                @if ($tournament && $tournament->rounds)
                                    @foreach ($tournament->rounds as $round)
                                        <option value="{{ $round->id }}">{{ $round->round_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <!-- Ngày + Giờ bắt đầu -->
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">📅 Ngày bắt đầu</label>
                            <input type="date" id="editMatchDate" name="match_date" class="form-select"
                                style="cursor: pointer;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">🕐 Giờ bắt đầu</label>
                            <input type="time" id="editMatchTime" name="match_time" class="form-select"
                                style="cursor: pointer;">
                        </div>
                    </div>

                    <!-- Chọn Bảng/Nhóm -->
                    <div class="form-group">
                        <label class="form-label">👥 Bảng/Nhóm (Group)</label>
                        <select id="editMatchGroup" name="group_id" class="form-select">
                            <option value="">-- Không chọn bảng --</option>
                            @if ($tournament && $tournament->groups)
                                @foreach ($tournament->groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Trạng thái trận đấu -->
                    <div class="form-group">
                        <label class="form-label">📊 Trạng thái trận đấu</label>
                        <select id="editStatus" name="status" class="form-select">
                            <option value="scheduled">⏳ Chờ thi đấu</option>
                            <option value="ready">📋 Sẵn sàng</option>
                            <option value="in_progress">🔴 Đang diễn ra</option>
                            <option value="completed">✅ Hoàn thành</option>
                            <option value="cancelled">❌ Hủy</option>
                            <option value="postponed">⏸️ Hoãn lại</option>
                            <option value="bye">🎯 Bye</option>
                        </select>
                    </div>

                    <!-- Assign Referee -->
                    <div class="form-group">
                        <label class="form-label">Trọng tài (Referee)</label>
                        <select name="referee_id" id="editRefereeId" class="form-select">
                            <option value="">-- không chỉ định trọng tài --</option>
                            @if (isset($referees) && $referees->count() > 0)
                                @foreach ($referees as $referee)
                                    <option value="{{ $referee->id }}">{{ $referee->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <small style="color: var(--text-light); font-size: 0.75rem;">Chỉ có thể chọn trọng tài đã được chỉ định trong giải đấu</small>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-success" id="submitEditMatchBtn">✅ Cập nhật</button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditMatchModal()">❌ Hủy</button>
                    </div>
                </form>
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
            console.log('=== DOMContentLoaded FIRED ===');
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

            // Initialize athlete form handler
            initializeAthleteForm();
        });

        // Draw/Lottery Functionality
        function initializeDraw() {
            const drawBtn = document.getElementById('drawBtn');
            const resetBtn = document.getElementById('resetBtn');
            const categorySelect = document.getElementById('categorySelect');
            const drawMethod = document.getElementById('drawMethod');
            const groupSelect = document.getElementById('groupSelect');
            const tournamentId = {{ $tournament->id ?? 0 }};

            // ✅ Load kết quả bốc thăm khi page load hoặc chọn category khác
            categorySelect.addEventListener('change', function() {
                if (this.value) {
                    loadDrawResults(this.value, tournamentId);
                }
            });

            if (drawBtn) {
                drawBtn.addEventListener('click', function() {
                    if (!categorySelect.value) {
                        toastr.warning('Vui lòng chọn nội dung thi đấu');
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
                        toastr.warning('Không có bảng nào cho nội dung này. Vui lòng tạo bảng trước.');
                        return;
                    }

                    // ✅ Lấy số VĐV đã duyệt
                    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                    const approvedAthletes = parseInt(selectedOption.dataset.athletes) || 0;

                    // ✅ Tính tổng sức chứa của bảng
                    const totalCapacity = selectedGroups.reduce((sum, group) => {
                        // Tìm phần tử select tương ứng để lấy max_participants từ data attribute
                        const groupOption = Array.from(groupSelect.options).find(opt => opt.value === group
                            .id);
                        const maxParticipants = groupOption ? parseInt(groupOption.dataset
                            .maxParticipants) || 0 : 0;
                        console.log(`Group ${group.id}: max = ${maxParticipants}`);
                        return sum + maxParticipants;
                    }, 0);

                    // ✅ VALIDATE trước khi gửi
                    if (approvedAthletes === 0) {
                        toastr.warning('Không có VĐV nào được duyệt cho nội dung này');
                        return;
                    }

                    if (approvedAthletes > totalCapacity) {
                        toastr.error(
                            `❌ Không đủ chỗ trống. Bạn có ${approvedAthletes} VĐV nhưng các bảng chỉ có sức chứa ${totalCapacity}. Vui lòng tạo thêm bảng hoặc tăng số VĐV tối đa của bảng.`
                            );
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
                                toastr.success('✅ ' + data.message);
                                displayResults(data.athletes);
                            } else {
                                toastr.error('❌ ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Draw error details:', error);
                            toastr.error('❌ ' + (error.message || 'Lỗi không xác định'));
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
                        toastr.warning('Vui lòng chọn nội dung thi đấu');
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
                                toastr.success('✅ ' + data.message);
                                document.getElementById('groupResultsContainer').style.display = 'none';
                                document.getElementById('noResultsMsg').style.display = 'block';
                            } else {
                                toastr.error('❌ ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            toastr.error('❌ Lỗi khi reset: ' + error);
                        })
                        .finally(() => {
                            resetBtn.disabled = false;
                            resetBtn.innerHTML = '🔄 Bốc lại';
                        });
                });
            }
        }

        // ✅ Load kết quả bốc thăm từ DB
        function loadDrawResults(categoryId, tournamentId) {
            fetch(`/homeyard/tournaments/${tournamentId}/draw-results?category_id=${categoryId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        displayResults(data.data);
                        console.log('Draw results loaded for category:', categoryId);
                    } else {
                        document.getElementById('groupResultsContainer').style.display = 'none';
                        document.getElementById('noResultsMsg').style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Error loading draw results:', err);
                });
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

        // Modal thêm vận động viên
        function openAddAthleteModal() {
            const modal = document.getElementById('addAthleteModal');
            if (modal) {
                modal.style.display = 'block';
            }
        }

        function closeAddAthleteModal() {
            const modal = document.getElementById('addAthleteModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('addAthleteModal');
            if (event.target === modal) {
                closeAddAthleteModal();
            }
        });

        // Initialize athlete form handler
        function initializeAthleteForm() {
            const addAthleteForm = document.getElementById('addAthleteForm');

            addAthleteForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('submitAthleteBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Đang xử lý...';

                const formData = new FormData(this);
                const tournamentId = {!! $tournament->id ?? 0 !!};

                const url = `/homeyard/tournaments/${tournamentId}/athletes`;

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok && response.status !== 422) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            toastr.success('Thêm vận động viên thành công!');
                            setTimeout(() => {
                                closeAddAthleteModal();
                                addAthleteForm.reset();
                                // Set active tab to athletes before reload
                                localStorage.setItem('activeTab', 'athletes');
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error('Lỗi không xác định. Vui lòng thử lại sau.');
                        }
                    })
                    .catch(error => {
                        toastr.error('Lỗi không xác định. Vui lòng thử lại sau.');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });
        }

        // View Athlete Modal Functions
        function openViewAthleteModal(id, name, email, phone, categoryId) {
            document.getElementById('viewAthleteName').textContent = name;
            document.getElementById('viewAthleteEmail').textContent = email;
            document.getElementById('viewAthletePhone').textContent = phone;

            // Get category name
            const categorySelect = document.querySelector('#editAthleteCategory');
            let categoryName = '-';
            if (categoryId && categorySelect) {
                const option = categorySelect.querySelector(`option[value="${categoryId}"]`);
                if (option) {
                    categoryName = option.textContent;
                }
            }
            document.getElementById('viewAthleteCategory').textContent = categoryName;

            const modal = document.getElementById('viewAthleteModal');
            if (modal) {
                modal.style.display = 'block';
            }
        }

        function closeViewAthleteModal() {
            const modal = document.getElementById('viewAthleteModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Edit Athlete Modal Functions
        function openEditAthleteModal(id, name, email, phone, categoryId) {
            document.getElementById('editAthleteId').value = id;
            document.getElementById('editAthleteName').value = name;
            document.getElementById('editAthleteEmail').value = email;
            document.getElementById('editAthletePhone').value = phone;
            if (categoryId) {
                document.getElementById('editAthleteCategory').value = categoryId;
            }

            const modal = document.getElementById('editAthleteModal');
            if (modal) {
                modal.style.display = 'block';
            }
        }

        function closeEditAthleteModal() {
            const modal = document.getElementById('editAthleteModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const viewModal = document.getElementById('viewAthleteModal');
            const editModal = document.getElementById('editAthleteModal');

            if (event.target === viewModal) {
                closeViewAthleteModal();
            }
            if (event.target === editModal) {
                closeEditAthleteModal();
            }
        });

        // Edit athlete form handler
        function initializeEditAthleteForm() {
            const editAthleteForm = document.getElementById('editAthleteForm');
            const messageDiv = document.getElementById('editAthleteMessages');

            if (!editAthleteForm) {
                console.error('Edit athlete form not found');
                return;
            }

            editAthleteForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('submitEditAthleteBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Đang cập nhật...';

                const athleteId = document.getElementById('editAthleteId').value;
                const tournamentId = {!! $tournament->id ?? 0 !!};

                const formData = new FormData(this);

                fetch(`/homeyard/tournaments/${tournamentId}/athletes/${athleteId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            athlete_name: formData.get('athlete_name'),
                            email: formData.get('email'),
                            phone: formData.get('phone'),
                            category_id: formData.get('category_id')
                        })
                    })
                    .then(response => {
                        if (!response.ok && response.status !== 422) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            toastr.success('Cập nhật vận động viên thành công!');
                            setTimeout(() => {
                                closeEditAthleteModal();
                                editAthleteForm.reset();
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(data.message || 'Lỗi không xác định. Vui lòng thử lại sau.')
                        }
                    })
                    .catch(error => {
                        toastr.error(error.message || 'Lỗi không xác định. Vui lòng thử lại sau.')
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });
        }

        // Delete athlete function
        function deleteAthlete(athleteId) {
            if (!confirm('Bạn chắc chắn muốn xóa vận động viên này?')) {
                return;
            }

            const tournamentId = {!! $tournament->id ?? 0 !!};

            fetch(`/homeyard/tournaments/${tournamentId}/athletes/${athleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success('Xóa vận động viên thành công!');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(data.message || 'Lỗi không xác định');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Lỗi: ' + error.message);
                });
        }

        // Initialize edit form when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeEditAthleteForm();
        });

        // Xuất Excel
        function exportAthletes() {
            const tournamentId = {!! $tournament->id ?? 0 !!};
            window.location.href = `/homeyard/tournaments/${tournamentId}/athletes/export`;
        }

        // ===== MATCH MANAGEMENT FUNCTIONS =====

        // Open Create Match Modal
        function openCreateMatchModal() {
            const modal = document.getElementById('createMatchModal');
            if (modal) {
                modal.style.display = 'block';
            }
        }

        // Close Create Match Modal
        function closeCreateMatchModal() {
            const modal = document.getElementById('createMatchModal');
            if (modal) {
                modal.style.display = 'none';
                // Reset form
                document.getElementById('createMatchForm').reset();
                document.getElementById('athlete1Select').disabled = true;
                document.getElementById('athlete2Select').disabled = true;
            }
        }

        // Handle category selection in match modal
        function setupCategorySelectListener() {
             const categorySelect = document.getElementById('matchCategoryId');
             const athlete1Select = document.getElementById('athlete1Select');
             const athlete2Select = document.getElementById('athlete2Select');
             const groupSelect = document.getElementById('matchGroupSelect');
             const tournamentId = {!! $tournament->id ?? 0 !!};

             if (categorySelect) {
                 // Remove old listeners
                 categorySelect.removeEventListener('change', handleCategoryChange);
                 // Add new listener
                 categorySelect.addEventListener('change', handleCategoryChange);
             }
         }

         function handleCategoryChange() {
             const categorySelect = document.getElementById('matchCategoryId');
             const athlete1Select = document.getElementById('athlete1Select');
             const athlete2Select = document.getElementById('athlete2Select');
             const groupSelect = document.getElementById('matchGroupSelect');
             const tournamentId = {!! $tournament->id ?? 0 !!};

             if (!categorySelect.value) {
                 // Reset nếu không chọn category
                 athlete1Select.innerHTML =
                     '<option value="">-- Hãy chọn nội dung thi đấu trước --</option>';
                 athlete2Select.innerHTML =
                     '<option value="">-- Hãy chọn nội dung thi đấu trước --</option>';
                 athlete1Select.disabled = true;
                 athlete2Select.disabled = true;

                 groupSelect.innerHTML =
                     '<option value="">-- Chọn nội dung thi đấu trước --</option>';
                 groupSelect.disabled = true;
                 return;
             }

             const categoryId = categorySelect.value;

             // Fetch danh sách VĐV của category từ server
             fetch(`/homeyard/tournaments/${tournamentId}/categories/${categoryId}/athletes`, {
                     headers: {
                         'X-Requested-With': 'XMLHttpRequest'
                     }
                 })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success && data.athletes) {
                         const athletes = data.athletes;
                         const athleteOptions = athletes.map(athlete =>
                             `<option value="${athlete.id}">${athlete.athlete_name}</option>`
                         ).join('');

                         athlete1Select.innerHTML =
                             `<option value="">-- Chọn VĐV 1 --</option>${athleteOptions}`;
                         athlete2Select.innerHTML =
                             `<option value="">-- Chọn VĐV 2 --</option>${athleteOptions}`;

                         athlete1Select.disabled = false;
                         athlete2Select.disabled = false;
                     } else {
                         athlete1Select.innerHTML =
                             '<option value="">Không có VĐV nào</option>';
                         athlete2Select.innerHTML =
                             '<option value="">Không có VĐV nào</option>';
                         athlete1Select.disabled = true;
                         athlete2Select.disabled = true;
                     }
                 })
                 .catch(error => {
                     console.error('Error fetching athletes:', error);
                     athlete1Select.innerHTML =
                         '<option value="">Lỗi tải dữ liệu</option>';
                     athlete2Select.innerHTML =
                         '<option value="">Lỗi tải dữ liệu</option>';
                     athlete1Select.disabled = true;
                     athlete2Select.disabled = true;
                 });

             // Fetch danh sách groups của category từ server
             console.log('Fetching groups for categoryId:', categoryId);
             fetch(`/homeyard/tournaments/${tournamentId}/categories/${categoryId}/groups`, {
                     headers: {
                         'X-Requested-With': 'XMLHttpRequest'
                     }
                 })
                 .then(response => {
                     console.log('Groups fetch response status:', response.status);
                     return response.json();
                 })
                 .then(data => {
                     console.log('Groups fetch response data:', data);
                     if (data.success && data.groups && data.groups.length > 0) {
                         const groups = data.groups;
                         const groupOptions = groups.map(group => {
                             return `<option value="${group.id}">${group.group_name}</option>`;
                         }).join('');

                         groupSelect.innerHTML =
                             `<option value="">-- Chọn bảng/nhóm (tuỳ chọn) --</option>${groupOptions}`;
                         groupSelect.disabled = false;
                     } else {
                         groupSelect.innerHTML =
                             '<option value="">-- Không có bảng/nhóm nào (Tạo bảng trước) --</option>';
                         groupSelect.disabled = true;
                     }
                 })
                 .catch(error => {
                     console.error('Error fetching groups:', error);
                     groupSelect.innerHTML =
                         '<option value="">Lỗi tải dữ liệu</option>';
                     groupSelect.disabled = true;
                 });
         }

        // Open Edit Match Modal
        function openEditMatchModal(matchId, athlete1Id, athlete2Id, categoryId, roundId, matchDate, matchTime, groupId, status, refereeId) {
            document.getElementById('editMatchId').value = matchId;
            document.getElementById('editAthlete1').value = athlete1Id;
            document.getElementById('editAthlete2').value = athlete2Id;
            document.getElementById('editCategory').value = categoryId;
            document.getElementById('editRound').value = roundId;
            document.getElementById('editMatchDate').value = matchDate || '';
            document.getElementById('editMatchTime').value = matchTime || '';
            document.getElementById('editMatchGroup').value = groupId || '';
            document.getElementById('editStatus').value = status || 'scheduled';
            document.getElementById('editRefereeId').value = refereeId || '';

            const modal = document.getElementById('editMatchModal');
            if (modal) {
                modal.style.display = 'block';
            }
        }

        // Close Edit Match Modal
        function closeEditMatchModal() {
            const modal = document.getElementById('editMatchModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const createModal = document.getElementById('createMatchModal');
            const editModal = document.getElementById('editMatchModal');

            if (event.target === createModal) {
                closeCreateMatchModal();
            }
            if (event.target === editModal) {
                closeEditMatchModal();
            }
        });

        // Initialize Create Match Form Handler
        function initializeCreateMatchForm() {
            const createMatchForm = document.getElementById('createMatchForm');

            if (!createMatchForm) {
                console.error('Create match form not found');
                return;
            }

            // Remove existing listeners to prevent duplicates
            const newForm = createMatchForm.cloneNode(true);
            createMatchForm.parentNode.replaceChild(newForm, createMatchForm);
            const form = document.getElementById('createMatchForm');

            // Setup category select listener
            setupCategorySelectListener();

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('#submitMatchBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Đang xử lý...';

                const formData = new FormData(form);
                const tournamentId = {!! $tournament->id ?? 0 !!};

                const matchDate = formData.get('match_date')?.trim();
                const matchTime = formData.get('match_time')?.trim();
                const roundId = formData.get('round_id')?.trim();
                const groupId = formData.get('group_id')?.trim();
                const refereeId = formData.get('referee_id')?.trim();

                const data = {
                    athlete1_id: formData.get('athlete1_id'),
                    athlete2_id: formData.get('athlete2_id'),
                    category_id: formData.get('category_id'),
                    round_id: roundId || null,
                    match_date: matchDate || null,
                    match_time: matchTime || null,
                    group_id: groupId || null,
                    tournament_id: tournamentId,
                    status: formData.get('status'),
                    referee_id: refereeId || null,
                };

                console.log('Creating match with data:', data);

                fetch(`/homeyard/tournaments/${tournamentId}/matches`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok && response.status !== 422) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            toastr.success('Tạo trận đấu thành công!');
                            setTimeout(() => {
                                closeCreateMatchModal();
                                createMatchForm.reset();

                                // Switch to matchManagement tab and reload page
                                localStorage.setItem('activeTab', 'matchManagement');
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(data.message || 'Lỗi không xác định');
                        }
                    })
                    .catch(error => {
                        toastr.error(error.message || 'Lỗi không xác định');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });
        }

        // Initialize Edit Match Form Handler
        function initializeEditMatchForm() {
            const editMatchForm = document.getElementById('editMatchForm');

            if (!editMatchForm) {
                console.error('Edit match form not found');
                return;
            }

            // Remove existing listeners to prevent duplicates
            const newForm = editMatchForm.cloneNode(true);
            editMatchForm.parentNode.replaceChild(newForm, editMatchForm);
            const form = document.getElementById('editMatchForm');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('#submitEditMatchBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Đang cập nhật...';

                const matchId = form.querySelector('#editMatchId').value;
                const tournamentId = {!! $tournament->id ?? 0 !!};

                const formData = new FormData(form);

                const matchDate = formData.get('match_date')?.trim();
                const matchTime = formData.get('match_time')?.trim();
                const roundId = formData.get('round_id')?.trim();
                const groupId = formData.get('group_id')?.trim();
                const status = formData.get('status')?.trim();
                const refereeId = formData.get('referee_id')?.trim();

                const data = {
                    athlete1_id: formData.get('athlete1_id'),
                    athlete2_id: formData.get('athlete2_id'),
                    category_id: formData.get('category_id'),
                    round_id: roundId || null,
                    match_date: matchDate || null,
                    match_time: matchTime || null,
                    group_id: groupId || null,
                    status: status || 'scheduled',
                    referee_id: refereeId || null,
                };

                fetch(`/homeyard/tournaments/${tournamentId}/matches/${matchId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        if (!response.ok && response.status !== 422) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            toastr.success('Cập nhật trận đấu thành công!', 'success', 3000000);
                            setTimeout(() => {
                                closeEditMatchModal();
                                editMatchForm.reset();
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(data.message || 'Lỗi không xác định');
                        }
                    })
                    .catch(error => {
                        toastr.error(erro.message || 'Lỗi không xác định');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });
        }

        // Initialize match forms when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setupCategorySelectListener();
            initializeCreateMatchForm();
            initializeEditMatchForm();
            updateGroupFilter();
            loadRankings();
        });

        // ===== RANKINGS/LEADERBOARD FUNCTIONS =====

        function updateGroupFilter() {
            const categorySelect = document.getElementById('filterCategory');
            const groupSelect = document.getElementById('filterGroup');
            const selectedCategoryId = categorySelect.value;

            if (!groupSelect) return;

            // Show/hide group options based on selected category
            const allOptions = groupSelect.querySelectorAll('option');
            let hasVisibleOptions = false;

            allOptions.forEach((option, index) => {
                if (index === 0) {
                    // Always show "-- Tất cả bảng --" option
                    option.style.display = '';
                    return;
                }

                const optionCategoryId = option.getAttribute('data-category-id');

                // If no category selected, show all options
                if (!selectedCategoryId) {
                    option.style.display = '';
                    hasVisibleOptions = true;
                } else if (optionCategoryId === selectedCategoryId) {
                    // Show only options matching the selected category
                    option.style.display = '';
                    hasVisibleOptions = true;
                } else {
                    // Hide non-matching options
                    option.style.display = 'none';
                }
            });

            // Reset group filter when category changes
            if (selectedCategoryId) {
                groupSelect.value = '';
            }
        }

        async function loadRankings(page = 1) {
            const tournamentId = {!! $tournament->id ?? 0 !!};
            const categoryId = document.getElementById('filterCategory')?.value || '';
            const groupId = document.getElementById('filterGroup')?.value || '';

            if (!tournamentId) {
                return;
            }

            try {
                const params = new URLSearchParams();
                if (categoryId) params.append('category_id', categoryId);
                if (groupId) params.append('group_id', groupId);
                params.append('page', page);

                const response = await fetch(
                    `/homeyard/tournaments/${tournamentId}/rankings?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                console.log('Rankings API Response:', data);

                if (data.success && data.rankings) {
                    renderRankingsTable(data.rankings);
                    // Only update stats on page 1 to show overall top athlete
                    if (page === 1) {
                        updateRankingsStats(data);
                    }
                    renderPagination(data.pagination);
                } else {
                    showRankingsError('Không có dữ liệu xếp hạng');
                }
            } catch (error) {
                console.error('Error loading rankings:', error);
                showRankingsError('Lỗi tải dữ liệu xếp hạng: ' + error.message);
            }
        }

        function renderRankingsTable(rankings) {
            const tableBody = document.getElementById('rankingsTableBody');
            if (!tableBody) return;

            if (!rankings || !Array.isArray(rankings) || rankings.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" style="padding: 40px; text-align: center; color: #999;">
                            Chưa có dữ liệu xếp hạng
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            rankings.forEach((athlete) => {
                const rank = athlete.rank || 0;
                const medal = rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : rank;
                const setsDiff = (athlete.sets_won || 0) - (athlete.sets_lost || 0);
                const gamesDiff = (athlete.games_won || 0) - (athlete.games_lost || 0);
                // win_rate từ API đã là phần trăm (0-100), không cần tính lại
                const winRate = (athlete.win_rate || 0).toFixed(1);

                const rowBg = rank <= 3 ? 'background-color: rgba(255, 215, 0, 0.1);' : '';

                html += `
                    <tr style="${rowBg} border-bottom: 1px solid #ddd; transition: background-color 0.3s;">
                        <td style="padding: 12px; text-align: center; font-weight: 700; font-size: 1.1rem;">${medal}</td>
                        <td style="padding: 12px; text-align: left; font-weight: 600;">${athlete.athlete_name || 'N/A'}</td>
                        <td style="padding: 12px; text-align: center; font-size: 0.9rem;">${athlete.category_name || 'N/A'}</td>
                        <td style="padding: 12px; text-align: center;">${athlete.matches_played || 0}</td>
                        <td style="padding: 12px; text-align: center; color: #10B981; font-weight: 600;">${athlete.matches_won || 0}</td>
                        <td style="padding: 12px; text-align: center; color: #EF4444; font-weight: 600;">${athlete.matches_lost || 0}</td>
                        <td style="padding: 12px; text-align: center; background-color: #FFE5B4; font-weight: 700; font-size: 1.1rem;">${athlete.points || 0}</td>
                        <td style="padding: 12px; text-align: center; font-size: 0.9rem;">${(athlete.sets_won || 0)}/${(athlete.sets_lost || 0)}</td>
                        <td style="padding: 12px; text-align: center; font-weight: 600; background-color: #E0F2FE; color: ${gamesDiff >= 0 ? '#059669' : '#DC2626'};">${gamesDiff >= 0 ? '+' : ''}${gamesDiff}</td>
                        <td style="padding: 12px; text-align: center;">${winRate}%</td>
                    </tr>
                `;
            });

            tableBody.innerHTML = html;
        }

        function updateRankingsStats(data) {
            const topAthleteEl = document.getElementById('topAthlete');
            const totalMatchesEl = document.getElementById('totalMatches');
            const totalAthletesEl = document.getElementById('totalAthletes');

            if (topAthleteEl && Array.isArray(data.rankings) && data.rankings.length > 0) {
                topAthleteEl.textContent = data.rankings[0].athlete_name || '-';
            } else if (topAthleteEl) {
                topAthleteEl.textContent = '-';
            }

            if (totalMatchesEl) {
                totalMatchesEl.textContent = data.total_matches || 0;
            }

            if (totalAthletesEl) {
                totalAthletesEl.textContent = data.total_athletes || 0;
            }
        }

        function showRankingsError(message) {
            const tableBody = document.getElementById('rankingsTableBody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" style="padding: 20px; text-align: center; color: #EF4444;">
                            ⚠️ ${message}
                        </td>
                    </tr>
                `;
            }
        }

        function printRankings() {
            const printContent = document.querySelector('.tab-pane:not([style*="display: none"]) table')?.outerHTML;
            if (!printContent) {
                toastr.error('Không tìm thấy bảng xếp hạng');
                return;
            }

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Bảng Xếp Hạng VĐV</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
                        th { background-color: #f5f5f5; font-weight: bold; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                    </style>
                </head>
                <body>
                    <h2>Bảng Xếp Hạng Vận Động Viên</h2>
                    ${printContent}
                </body>
                </html>
            `);
            printWindow.document.close();
            setTimeout(() => printWindow.print(), 250);
        }

        function renderPagination(pagination) {
            const paginationContainer = document.getElementById('paginationControls');
            if (!paginationContainer) return;

            // If no pagination or only 1 page, hide pagination
            if (!pagination || pagination.total_pages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            paginationContainer.style.display = 'flex';

            let html = '';

            // Previous button
            if (pagination.current_page > 1) {
                html += `<button style="padding: 8px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; transition: 0.3s;" 
                         onclick="loadRankings(${pagination.current_page - 1})" 
                         onmouseover="this.style.background='#f0f0f0'" 
                         onmouseout="this.style.background='white'">← Trang trước</button>`;
            } else {
                html +=
                    `<button style="padding: 8px 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px; cursor: not-allowed; color: #999;" disabled>← Trang trước</button>`;
            }

            // Page numbers
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === pagination.current_page) {
                    html +=
                        `<button style="padding: 8px 12px; border: none; background: #667eea; color: white; border-radius: 4px; font-weight: 600; cursor: default; min-width: 40px;">${i}</button>`;
                } else if (i <= 2 || i > pagination.total_pages - 2 || (i >= pagination.current_page - 1 && i <= pagination
                        .current_page + 1)) {
                    html += `<button style="padding: 8px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; transition: 0.3s; min-width: 40px;" 
                             onclick="loadRankings(${i})" 
                             onmouseover="this.style.background='#f0f0f0'" 
                             onmouseout="this.style.background='white'">${i}</button>`;
                } else if (i === 3 || i === pagination.total_pages - 2) {
                    html += `<span style="padding: 8px 6px; color: #999;">...</span>`;
                }
            }

            // Next button
            if (pagination.current_page < pagination.total_pages) {
                html += `<button style="padding: 8px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; transition: 0.3s;" 
                         onclick="loadRankings(${pagination.current_page + 1})" 
                         onmouseover="this.style.background='#f0f0f0'" 
                         onmouseout="this.style.background='white'">Trang sau →</button>`;
            } else {
                html +=
                    `<button style="padding: 8px 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px; cursor: not-allowed; color: #999;" disabled>Trang sau →</button>`;
            }

            // Info text
            html +=
                `<span style="margin-left: 10px; padding: 8px 12px; color: #666; font-weight: 500;">Trang ${pagination.current_page}/${pagination.total_pages}</span>`;

            paginationContainer.innerHTML = html;
        }

        function exportRankingsExcel() {
            const tournamentId = {!! $tournament->id ?? 0 !!};
            const categoryId = document.getElementById('filterCategory')?.value || '';
            const groupId = document.getElementById('filterGroup')?.value || '';

            const params = new URLSearchParams();
            if (categoryId) params.append('category_id', categoryId);
            if (groupId) params.append('group_id', groupId);

            const url =
                `/homeyard/tournaments/${tournamentId}/rankings/export${params.toString() ? '?' + params.toString() : ''}`;
            window.location.href = url;
        }
    </script>

    <!-- MODAL: THÊM VĐV -->
    <div id="addAthleteModal"
        style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow-y: auto;">
        <div
            style="background-color: var(--bg-white); margin: 5% auto; padding: 2rem; border-radius: var(--radius-xl); width: 90%; max-width: 600px; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">➕ Thêm Vận Động Viên</h2>
                <button
                    style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                    onclick="closeAddAthleteModal()">×</button>
            </div>

            <form id="addAthleteForm">
                <div class="form-group">
                    <label class="form-label">Tên VĐV *</label>
                    <input type="text" name="athlete_name" class="form-input" placeholder="Nhập tên vận động viên"
                        required>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-input" placeholder="VD: athlete@example.com"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số điện thoại *</label>
                        <input type="tel" name="phone" class="form-input" placeholder="VD: 0123456789" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nội dung thi đấu *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn nội dung --</option>
                        @if ($tournament && $tournament->categories)
                            @foreach ($tournament->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        @else
                            <option value="">Chưa có nội dung. Vui lòng tạo nội dung thi đấu trước.</option>
                        @endif
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success" id="submitAthleteBtn">✅ Thêm VĐV</button>
                    <button type="button" class="btn btn-secondary" onclick="closeAddAthleteModal()">❌ Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: XEM CHI TIẾT VĐV -->
    <div id="viewAthleteModal"
        style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow-y: auto;">
        <div
            style="background-color: var(--bg-white); margin: 5% auto; padding: 2rem; border-radius: var(--radius-xl); width: 90%; max-width: 600px; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">👁️ Chi tiết Vận Động Viên</h2>
                <button
                    style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                    onclick="closeViewAthleteModal()">×</button>
            </div>

            <div id="viewAthleteContent" style="padding: 20px; background: #f9f9f9; border-radius: 8px;">
                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Tên VĐV:</label>
                    <p style="margin: 0; font-size: 16px;" id="viewAthleteName"></p>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Email:</label>
                    <p style="margin: 0; font-size: 16px;" id="viewAthleteEmail"></p>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Điện thoại:</label>
                    <p style="margin: 0; font-size: 16px;" id="viewAthletePhone"></p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Nội dung thi
                        đấu:</label>
                    <p style="margin: 0; font-size: 16px;" id="viewAthleteCategory"></p>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeViewAthleteModal()">❌ Đóng</button>
            </div>
        </div>
    </div>

    <!-- MODAL: SỬA THÔNG TIN VĐV -->
    <div id="editAthleteModal"
        style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow-y: auto;">
        <div
            style="background-color: var(--bg-white); margin: 5% auto; padding: 2rem; border-radius: var(--radius-xl); width: 90%; max-width: 600px; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">✏️ Sửa Vận Động Viên</h2>
                <button
                    style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                    onclick="closeEditAthleteModal()">×</button>
            </div>

            <div id="editAthleteMessages"></div>

            <form id="editAthleteForm">
                <input type="hidden" id="editAthleteId" name="athlete_id" value="">

                <div class="form-group">
                    <label class="form-label">Tên VĐV *</label>
                    <input type="text" id="editAthleteName" name="athlete_name" class="form-input"
                        placeholder="Nhập tên vận động viên" required>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" id="editAthleteEmail" name="email" class="form-input"
                            placeholder="VD: athlete@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số điện thoại *</label>
                        <input type="tel" id="editAthletePhone" name="phone" class="form-input"
                            placeholder="VD: 0123456789" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nội dung thi đấu *</label>
                    <select id="editAthleteCategory" name="category_id" class="form-select" required>
                        <option value="">-- Chọn nội dung --</option>
                        @if ($tournament && $tournament->categories)
                            @foreach ($tournament->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success" id="submitEditAthleteBtn">✅ Cập nhật</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditAthleteModal()">❌ Hủy</button>
                </div>
            </form>
        </div>
    </div>

@endsection
