{{-- Recurring-specific fields --}}
@php
    $recurrenceDay = old('recurrence_day', $activity->recurrence_day ?? '');
    $autoApprove = old('auto_approve', $activity->auto_approve ?? false);
    $days = [0 => 'Chủ Nhật', 1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7'];
@endphp

<div id="recurring-fields" style="display: none;">
    <div class="form-group">
        <label for="recurrence_day">Ngày lặp lại hàng tuần <span style="color: red;">*</span></label>
        <select name="recurrence_day" id="recurrence_day">
            <option value="">-- Chọn ngày --</option>
            @foreach($days as $value => $label)
                <option value="{{ $value }}" {{ (string)$recurrenceDay === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <div class="hint">Hệ thống sẽ tự động tạo buổi chơi mới tuần vào ngày này</div>
    </div>

    <div class="form-group">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="hidden" name="auto_approve" value="0">
            <input type="checkbox" name="auto_approve" value="1" {{ $autoApprove ? 'checked' : '' }}
                   style="width: auto; accent-color: #00D9B5;">
            Tự động duyệt người tham gia
        </label>
        <div class="hint">Nếu bật, người đăng ký sẽ được xác nhận ngay khi còn chỗ trống</div>
    </div>
</div>
