<div class="modal-content">
    <form id="editTournamentForm" method="POST" enctype="multipart/form-data"
        action="{{ route('homeyard.tournaments.update', $tournament->id) }}">
        @csrf
        @method('PUT')

        <div class="modal-header"
            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-bottom: none;">
            <h3 class="modal-title" style="color: white; margin: 0;">Chỉnh Sửa Giải Đấu</h3>
            <button type="button" class="modal-close" style="color: white;" onclick="closeEditModal()">×</button>
        </div>
        <div class="modal-body" id="editModalBody" style="max-height: 70vh; overflow-y: auto;">
            <div class="form-group">
                <label class="form-label">Tên giải đấu *</label>
                <input type="text" class="form-input" name="name" placeholder="VD: Giải Pickleball Mở Rộng"
                    required value="{{ $tournament->name }}">
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Ngày bắt đầu *</label>
                    <input type="date" class="form-input" name="start_date"
                        value="{{ $tournament->start_date->format('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày kết thúc *</label>
                    <input type="date" class="form-input" name="end_date"
                        value="{{ $tournament->end_date->format('Y-m-d') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Địa điểm</label>
                <input type="text" class="form-input" name="location" placeholder="VD: Sân Pickleball Thảo Điền"
                    value="{{ $tournament->location }}">
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Loại giải *</label>
                    <select class="form-select" name="competition_format">
                        <option value="">Chọn loại giải</option>
                        <option value="single" {{ $tournament->competition_format == 'single' ? 'selected' : '' }}>Đơn
                        </option>
                        <option value="double" {{ $tournament->competition_format == 'double' ? 'selected' : '' }}>Đôi
                        </option>
                        <option value="mixed" {{ $tournament->competition_format == 'mixed' ? 'selected' : '' }}>Đôi
                            nam nữ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Số VĐV tối đa</label>
                    <input type="number" class="form-input" name="max_participants" placeholder="64"
                        value="{{ $tournament->max_participants }}">
                </div>
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Lệ phí giải đấu (VNĐ)</label>
                    <input type="number" class="form-input" name="price" placeholder="500000"
                        value="{{ $tournament->price }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Giải thưởng (VNĐ)</label>
                    <input type="number" class="form-input" name="prizes" placeholder="50000000"
                        value="{{ $tournament->prizes }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Thời hạn đăng ký</label>
                <input type="datetime-local" class="form-input" name="registration_deadline"
                    value="{{ $tournament->registration_deadline?->format('Y-m-d H:i') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea class="form-input" name="description" placeholder="Nhập mô tả giải đấu..." rows="3">{{ $tournament->description }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Quy định</label>
                <textarea class="form-input" name="competition_rules" placeholder="Nhập quy định của giải đấu..." rows="3">{{ $tournament->competition_rules }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Quyền lợi khi tham gia</label>
                <textarea class="form-input" name="registration_benefits" placeholder="Nhập quyền lợi khi tham gia..." rows="3">{{ $tournament->registration_benefits }}</textarea>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Hạng Đấu</label>
                    <select class="form-select" name="tournament_rank">
                        <option value="">-- Chọn --</option>
                        <option value="beginner" {{ $tournament->tournament_rank === 'beginner' ? 'selected' : '' }}>Sơ Cấp</option>
                        <option value="intermediate" {{ $tournament->tournament_rank === 'intermediate' ? 'selected' : '' }}>Trung Cấp</option>
                        <option value="advanced" {{ $tournament->tournament_rank === 'advanced' ? 'selected' : '' }}>Cao Cấp</option>
                        <option value="professional" {{ $tournament->tournament_rank === 'professional' ? 'selected' : '' }}>Chuyên Nghiệp</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Timeline Sự Kiện</label>
                <textarea class="form-input" name="event_timeline" placeholder="Nhập timeline sự kiện của giải đấu..." rows="4">{{ $tournament->event_timeline }}</textarea>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Email liên hệ</label>
                    <input type="text" class="form-input" name="organizer_email" placeholder="example@gmail.com" value="{{ $tournament->organizer_email }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Số điện thoại liên hệ</label>
                    <input type="text" class="form-input" name="organizer_hotline" placeholder="0987654321" value="{{ $tournament->organizer_hotline }}">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Thông tin mạng xã hội</label>
                <textarea class="form-input" name="social_information" placeholder="Nhập thông tin mạng xã hội..." rows="3">{{ $tournament->social_information }}</textarea>
            </div>

            {{-- Referee Selection --}}
            <div class="form-group">
                <label class="form-label">Chỉ định trọng tài</label>
                @php
                    $availableReferees = \App\Models\User::role('referee')->orderBy('name')->get();
                    $currentRefereeIds = $tournament->referees->pluck('id')->toArray();
                @endphp
                @if($availableReferees->isEmpty())
                    <div style="background: #fef3c7; color: #92400e; padding: 10px 12px; border-radius: 6px; font-size: 0.9rem;">
                        Chưa có trọng tài nào được tạo. Vui lòng thêm trọng tài trước khi chỉ định.
                    </div>
                @else
                    <select name="referee_ids[]" class="form-select" multiple size="4" style="height: auto;">
                        @foreach($availableReferees as $referee)
                            <option value="{{ $referee->id }}" {{ in_array($referee->id, $currentRefereeIds) ? 'selected' : '' }}>
                                {{ $referee->name }} ({{ $referee->email }})
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 4px;">
                        Giữ phím Ctrl/Cmd để chọn nhiều trọng tài. Bỏ chọn tất cả để xóa trọng tài.
                    </small>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Banner</label>
                @include('components.media-uploader', [
                    'model' => $tournament,
                    'collection' => 'banner',
                    'name' => 'banner',
                    'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                    'maxItems' => 1,
                ])
            </div>
            <div class="form-group">
                <label class="form-label">Hình ảnh</label>
                @include('components.media-uploader', [
                    'model' => $tournament,
                    'collection' => 'gallery',
                    'name' => 'gallery',
                    'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                ])
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
            <button type="submit" class="btn btn-primary">💾 Lưu Thay Đổi</button>
        </div>

    </form>
</div>
