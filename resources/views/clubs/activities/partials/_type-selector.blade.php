{{-- Type selector: 3 card buttons for activity type --}}
@php
    $selectedType = old('type', $activity->type ?? 'one_off');
@endphp

<div class="form-group">
    <label>Loại Hoạt Động <span style="color: red;">*</span></label>
    <input type="hidden" name="type" id="type-input" value="{{ $selectedType }}">
    <div class="type-selector">
        <div class="type-card {{ $selectedType === 'one_off' ? 'active' : '' }}" data-type="one_off">
            <div class="type-icon">📅</div>
            <div class="type-label">Buổi chơi</div>
            <div class="type-desc">Hoạt động một lần</div>
        </div>
        <div class="type-card {{ $selectedType === 'recurring' ? 'active' : '' }}" data-type="recurring">
            <div class="type-icon">🔄</div>
            <div class="type-label">Lịch cố định</div>
            <div class="type-desc">Lặp lại hàng tuần</div>
        </div>
        {{-- <div class="type-card {{ $selectedType === 'competition' ? 'active' : '' }}" data-type="competition">
            <div class="type-icon">🏆</div>
            <div class="type-label">Giải đấu</div>
            <div class="type-desc">Thi đấu có xếp hạng</div>
        </div> --}}
        <div class="type-card {{ $selectedType === 'open_play' ? 'active' : '' }}" data-type="open_play">
            <div class="type-icon">🎯</div>
            <div class="type-label">Chơi mở</div>
            <div class="type-desc">Check-in QR, ghép trận tự động</div>
        </div>
    </div>
</div>

<style>
.type-selector {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.type-card {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}
.type-card:hover {
    border-color: #006646;
    background: #f0fdfb;
}
.type-card.active {
    border-color: #006646;
    background: linear-gradient(135deg, rgba(0,217,181,0.08), rgba(0,217,181,0.02));
    box-shadow: 0 0 0 3px rgba(0,217,181,0.1);
}
.type-icon {
    font-size: 1.5rem;
    margin-bottom: 8px;
    color: #6b7280;
}
.type-card.active .type-icon {
    color: #006646;
}
.type-label {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}
.type-desc {
    font-size: 0.8rem;
    color: #9ca3af;
}
@media (max-width: 480px) {
    .type-selector {
        grid-template-columns: 1fr;
    }
}
</style>
