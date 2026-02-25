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

@php
    $config = isset($league) ? ($league->config ?? []) : [];
    $defaultConfig = \App\Services\LeagueService::DEFAULT_CONFIG;
@endphp

<div style="background: white; border-radius: 15px; padding: clamp(20px, 5vw, 30px); box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
    <form method="POST" action="{{ isset($league) ? route('homeyard.leagues.update', $league) : route('homeyard.leagues.store') }}" enctype="multipart/form-data" id="leagueForm">
        @csrf
        @if(isset($league))
            @method('PUT')
        @endif

        <!-- Tên và Mùa Giải -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tên League *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $league->name ?? '') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mùa Giải</label>
                <input type="text" name="season_name" class="form-control" value="{{ old('season_name', $league->season_name ?? '') }}" placeholder="VD: Mùa Xuân 2026"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Mô Tả -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Mô Tả</label>
            <textarea name="description" class="form-control" rows="3"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ old('description', $league->description ?? '') }}</textarea>
        </div>

        <!-- Ngày -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ngày Bắt Đầu</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', isset($league) && $league->start_date ? $league->start_date->format('Y-m-d') : '') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Ngày Kết Thúc</label>
                <input type="date" name="end_date" class="form-control" value="{{ old('end_date', isset($league) && $league->end_date ? $league->end_date->format('Y-m-d') : '') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Hạn Đăng Ký</label>
                <input type="datetime-local" name="registration_deadline" class="form-control" value="{{ old('registration_deadline', isset($league) && $league->registration_deadline ? $league->registration_deadline->format('Y-m-d\TH:i') : '') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Cấu Hình League -->
        <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: #f8fafc;">
            <h3 style="color: #1e293b; font-size: 1.1rem; margin: 0 0 15px 0;">
                <i class="fas fa-cog"></i> Cấu Hình League
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Số Đội Tối Đa</label>
                    <input type="number" name="config[max_teams]" class="form-control config-field" min="2" max="32"
                        value="{{ old('config.max_teams', $config['max_teams'] ?? $defaultConfig['max_teams']) }}"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Số VĐV/Đội Tối Đa</label>
                    <input type="number" name="config[max_players_per_team]" class="form-control config-field" min="2" max="20"
                        value="{{ old('config.max_players_per_team', $config['max_players_per_team'] ?? $defaultConfig['max_players_per_team']) }}"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Điểm Thắng</label>
                    <input type="number" name="config[points_for_win]" class="form-control config-field" min="0"
                        value="{{ old('config.points_for_win', $config['points_for_win'] ?? $defaultConfig['points_for_win']) }}"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Điểm Thua</label>
                    <input type="number" name="config[points_for_loss]" class="form-control config-field" min="0"
                        value="{{ old('config.points_for_loss', $config['points_for_loss'] ?? $defaultConfig['points_for_loss']) }}"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                </div>
            </div>

            <!-- Match Format -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Nội Dung Thi Đấu</label>
                <div id="matchFormatContainer" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;">
                    @php
                        $matchFormat = old('config.match_format', $config['match_format'] ?? $defaultConfig['match_format']);
                    @endphp
                    @foreach($matchFormat as $i => $format)
                        <div class="format-tag" style="display: inline-flex; align-items: center; gap: 6px; background: #dbeafe; color: #1e40af; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem;">
                            <select name="config[match_format][]" style="border: none; background: transparent; color: #1e40af; font-weight: 600; font-size: 0.85rem;">
                                <option value="WD" {{ $format === 'WD' ? 'selected' : '' }}>WD (Nữ Đôi)</option>
                                <option value="MD" {{ $format === 'MD' ? 'selected' : '' }}>MD (Nam Đôi)</option>
                                <option value="MXD" {{ $format === 'MXD' ? 'selected' : '' }}>MXD (Đôi Hỗn Hợp)</option>
                            </select>
                            <button type="button" onclick="this.closest('.format-tag').remove()" style="background: none; border: none; color: #1e40af; cursor: pointer; font-size: 1rem; padding: 0; line-height: 1;">&times;</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addMatchFormat()" style="background: #e0f2fe; color: #0369a1; border: 1px dashed #0369a1; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                    <i class="fas fa-plus"></i> Thêm Nội Dung
                </button>
            </div>
        </div>

        <!-- Buttons -->
        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('homeyard.leagues.index') }}" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">Hủy</a>
            <button type="submit" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                {{ isset($league) ? 'Cập Nhật League' : 'Tạo League' }}
            </button>
        </div>
    </form>
</div>

<script>
function addMatchFormat() {
    const container = document.getElementById('matchFormatContainer');
    const tag = document.createElement('div');
    tag.className = 'format-tag';
    tag.style.cssText = 'display: inline-flex; align-items: center; gap: 6px; background: #dbeafe; color: #1e40af; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem;';
    tag.innerHTML = `
        <select name="config[match_format][]" style="border: none; background: transparent; color: #1e40af; font-weight: 600; font-size: 0.85rem;">
            <option value="WD">WD (Nữ Đôi)</option>
            <option value="MD">MD (Nam Đôi)</option>
            <option value="MXD">MXD (Đôi Hỗn Hợp)</option>
        </select>
        <button type="button" onclick="this.closest('.format-tag').remove()" style="background: none; border: none; color: #1e40af; cursor: pointer; font-size: 1rem; padding: 0; line-height: 1;">&times;</button>
    `;
    container.appendChild(tag);
}

@if(isset($league) && $league->status === 'active')
// Cảnh báo thay đổi cấu hình cho league đang hoạt động
document.getElementById('leagueForm').addEventListener('submit', function(e) {
    const configFields = document.querySelectorAll('.config-field');
    const originalConfig = @json($config);
    let configChanged = false;

    configFields.forEach(function(field) {
        const name = field.name.replace('config[', '').replace(']', '');
        if (originalConfig[name] !== undefined && String(originalConfig[name]) !== String(field.value)) {
            configChanged = true;
        }
    });

    if (configChanged) {
        if (!confirm('Thay đổi cấu hình sẽ tính lại toàn bộ bảng xếp hạng. Bạn có muốn tiếp tục?')) {
            e.preventDefault();
        }
    }
});
@endif
</script>
