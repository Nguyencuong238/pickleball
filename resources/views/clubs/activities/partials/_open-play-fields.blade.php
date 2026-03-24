{{-- Open Play specific fields --}}
@php
    $courts = old('courts_count', $activity->courts_count ?? 3);
    $rotation = old('rotation_mode', $activity->rotation_mode ?? 'oprs_based');
    $oprsWeight = old('oprs_weight', $activity->oprs_weight ?? 0.50);
    $avgDuration = old('avg_match_duration', $activity->avg_match_duration ?? 15);
@endphp

<div id="open-play-fields" style="display: none;">
    <div style="background: #f0fdfb; border: 1px solid #d1fae5; border-radius: 12px; padding: 20px; margin-top: 16px;">
        <h4 style="margin: 0 0 16px; color: #065f46; font-size: 15px;">Cấu hình Chơi mở</h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>Số sân <span style="color: red;">*</span></label>
                <input type="number" name="courts_count" class="form-control" value="{{ $courts }}" min="1" max="20" required>
                <small style="color: #6b7280;">Số sân có thể chơi đồng thời</small>
            </div>

            <div class="form-group">
                <label>Thời gian trung bình mỗi trận (phút)</label>
                <input type="number" name="avg_match_duration" class="form-control" value="{{ $avgDuration }}" min="5" max="60">
            </div>

            <div class="form-group">
                <label>Chế độ ghép trận</label>
                <select name="rotation_mode" class="form-control">
                    <option value="oprs_based" {{ $rotation === 'oprs_based' ? 'selected' : '' }}>Theo trình độ (OPRS)</option>
                    <option value="random" {{ $rotation === 'random' ? 'selected' : '' }}>Ngẫu nhiên</option>
                    <option value="round_robin" {{ $rotation === 'round_robin' ? 'selected' : '' }}>Vòng tròn (theo thứ tự)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Mức ảnh hưởng OPRS</label>
                <select name="oprs_weight" class="form-control">
                    <option value="0" {{ (float)$oprsWeight === 0.0 ? 'selected' : '' }}>Không ảnh hưởng (0%)</option>
                    <option value="0.25" {{ (float)$oprsWeight === 0.25 ? 'selected' : '' }}>Thấp (25%)</option>
                    <option value="0.50" {{ (float)$oprsWeight === 0.50 ? 'selected' : '' }}>Vừa (50%)</option>
                    <option value="0.75" {{ (float)$oprsWeight === 0.75 ? 'selected' : '' }}>Cao (75%)</option>
                    <option value="1.00" {{ (float)$oprsWeight === 1.0 ? 'selected' : '' }}>Tối đa (100%)</option>
                </select>
                <small style="color: #6b7280;">Mức thay đổi điểm OPRS sau mỗi trận</small>
            </div>
        </div>
    </div>
</div>
