{{-- Skill level range fields (all activity types) --}}
@php
    $minSkill = old('min_skill_level', $activity->min_skill_level ?? '');
    $maxSkill = old('max_skill_level', $activity->max_skill_level ?? '');
@endphp

<div class="form-group">
    <label>Trình độ OPR yêu cầu</label>
    <div class="form-row">
        <div>
            <input type="number" name="min_skill_level" value="{{ $minSkill }}"
                   min="1.0" max="6.0" step="0.5" placeholder="Từ (VD: 2.0)">
        </div>
        <div>
            <input type="number" name="max_skill_level" value="{{ $maxSkill }}"
                   min="1.0" max="6.0" step="0.5" placeholder="Đến (VD: 4.5)">
        </div>
    </div>
    <div class="hint">Để trống nếu không giới hạn trình độ. Thang 1.0 - 6.0</div>
</div>
