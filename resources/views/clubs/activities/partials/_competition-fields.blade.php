{{-- Competition-specific fields --}}
@php
    $config = old('competition_config', $activity->competition_config ?? []);
    $format = $config['format'] ?? 'round_robin';
    $pointsWin = $config['points_for_win'] ?? 3;
    $pointsLoss = $config['points_for_loss'] ?? 0;
@endphp

<div id="competition-fields" style="display: none;">
    <div class="form-group">
        <label for="competition_format">Thể thức thi đấu <span style="color: red;">*</span></label>
        <select name="competition_config[format]" id="competition_format">
            <option value="round_robin" {{ $format === 'round_robin' ? 'selected' : '' }}>Vòng tròn (Round Robin)</option>
            <option value="pool_play" {{ $format === 'pool_play' ? 'selected' : '' }}>Bảng đấu + Loại trực tiếp (Pool Play)</option>
            <option value="single_elimination" {{ $format === 'single_elimination' ? 'selected' : '' }}>Loại trực tiếp (Single Elimination)</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="points_for_win">Điểm thắng</label>
            <input type="number" name="competition_config[points_for_win]" id="points_for_win"
                   value="{{ $pointsWin }}" min="0" max="10">
        </div>
        <div class="form-group">
            <label for="points_for_loss">Điểm thua</label>
            <input type="number" name="competition_config[points_for_loss]" id="points_for_loss"
                   value="{{ $pointsLoss }}" min="0" max="10">
        </div>
    </div>
</div>
