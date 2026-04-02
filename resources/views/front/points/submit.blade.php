@extends('layouts.front')

@section('title', 'Gửi Yêu Cầu - ' . $task->name)

@section('css')
<style>
    .submit-card {
        border-radius: 12px;
        overflow: hidden;
    }
    .task-info {
        background: linear-gradient(135deg, #006646 0%, #52c98c 100%);
        color: white;
        padding: 1.25rem;
    }
    .points-display {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .instructions-card {
        border-radius: 12px;
        background: #f8f9fa;
    }
    .instructions-card ol {
        padding-left: 1.25rem;
    }
    .instructions-card li {
        margin-bottom: 0.5rem;
    }
    .pending-alert {
        border-radius: 8px;
        background: #fff3cd;
        border: 1px solid #ffc107;
        padding: 1rem;
    }
    .file-preview {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }
    .file-preview img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
</style>
@endsection

@section('content')
<div class="container py-4 page-content">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.points.index') }}">Kiếm Điểm</a></li>
            <li class="breadcrumb-item active">{{ $task->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card submit-card">
                <div class="task-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">{{ $task->name }}</h4>
                            <p class="mb-0 opacity-75">{{ $task->description }}</p>
                        </div>
                        <div class="text-end">
                            <div class="points-display">+{{ $task->points }}</div>
                            <small class="opacity-75">điểm</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($pendingSubmission)
                        <div class="pending-alert">
                            <h6 class="text-warning mb-2">⏳ Đang Chờ Duyệt</h6>
                            <p class="mb-1">Bạn đã gửi yêu cầu cho nhiệm vụ này vào ngày {{ $pendingSubmission->created_at->format('d/m/Y H:i') }}.</p>
                            <p class="mb-0">Vui lòng đợi admin duyệt yêu cầu của bạn.</p>
                            <a href="{{ route('user.points.submissions') }}" class="btn btn-outline-warning btn-sm mt-2">Xem trạng thái</a>
                        </div>
                    @else
                        <form action="{{ route('user.points.submit', $task) }}" method="POST" enctype="multipart/form-data" id="submitForm">
                            @csrf

                            @if($task->proof_type === 'image')
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Upload Hình Ảnh Minh Chứng (tối đa 5) *</label>
                                    <input type="file"
                                           name="images[]"
                                           class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                                           multiple
                                           accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                           required
                                           id="imageInput">
                                    <small class="form-text text-muted">Chấp nhận: JPG, PNG, GIF, WebP. Tối đa 5MB mỗi file.</small>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="file-preview" id="imagePreview"></div>
                                </div>
                            @endif

                            @if($task->proof_type === 'link')
                                <div class="mb-3">
                                    <label class="form-label fw-bold">URL Hồ Sơ *</label>
                                    <input type="url"
                                           name="url"
                                           class="form-control @error('url') is-invalid @enderror"
                                           value="{{ old('url') }}"
                                           placeholder="https://facebook.com/yourprofile"
                                           required>
                                    <small class="form-text text-muted">Nhập đầy đủ URL hồ sơ của bạn</small>
                                    @error('url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            @if($task->proof_type === 'qr_code')
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mã QR *</label>
                                    <input type="text"
                                           name="qr_data"
                                           class="form-control @error('qr_data') is-invalid @enderror"
                                           value="{{ old('qr_data') }}"
                                           placeholder="Giá trị mã QR đã quét"
                                           required>
                                    @error('qr_data')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            @if($task->code === 'special_challenge' && $challenges->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Chọn Thách Thức *</label>
                                    <select name="challenge_id" class="form-select @error('challenge_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn thách thức --</option>
                                        @foreach($challenges as $challenge)
                                            <option value="{{ $challenge->id }}" {{ old('challenge_id') == $challenge->id ? 'selected' : '' }}>
                                                {{ $challenge->title }} (+{{ $challenge->points }} điểm)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('challenge_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    Gửi Yêu Cầu
                                </button>
                                <a href="{{ route('user.points.index') }}" class="btn btn-outline-secondary">Hủy</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card instructions-card">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">ℹ️ Hướng Dẫn</h6>
                </div>
                <div class="card-body">
                    @switch($task->code)
                        @case('check_in_stadium')
                            <ol>
                                <li>Đi đến sân pickleball</li>
                                <li>Chụp hình tại địa điểm</li>
                                <li>Upload hình ảnh lên đây</li>
                                <li>Đợi admin duyệt yêu cầu</li>
                            </ol>
                            @break

                        @case('join_fb_group')
                            <ol>
                                <li><a href="{{ $task->getExternalUrl() }}" target="_blank" class="text-primary fw-bold">Truy cập Group Facebook OnePickleball</a></li>
                                <li>Tham gia nhóm</li>
                                <li>Copy URL hồ sơ Facebook của bạn</li>
                                <li>Dán vào đây và gửi yêu cầu</li>
                            </ol>
                            @break

                        @case('follow_fb_page')
                            <ol>
                                <li><a href="{{ $task->getExternalUrl() }}" target="_blank" class="text-primary fw-bold">Truy cập Fanpage OnePickleball</a></li>
                                <li>Theo dõi trang</li>
                                <li>Copy URL hồ sơ Facebook của bạn</li>
                                <li>Dán vào đây và gửi yêu cầu</li>
                            </ol>
                            @break

                        @case('subscribe_youtube')
                            <ol>
                                <li><a href="{{ $task->getExternalUrl() }}" target="_blank" class="text-primary fw-bold">Truy cập kênh YouTube OnePickleball</a></li>
                                <li>Đăng ký kênh</li>
                                <li>Copy URL hồ sơ YouTube của bạn</li>
                                <li>Dán vào đây và gửi yêu cầu</li>
                            </ol>
                            @break

                        @case('follow_tiktok')
                            <ol>
                                <li><a href="{{ $task->getExternalUrl() }}" target="_blank" class="text-primary fw-bold">Truy cập TikTok OnePickleball</a></li>
                                <li>Theo dõi tài khoản</li>
                                <li>Copy URL hồ sơ TikTok của bạn</li>
                                <li>Dán vào đây và gửi yêu cầu</li>
                            </ol>
                            @break

                        @case('special_challenge')
                            <ol>
                                <li>Chọn thách thức bạn đã hoàn thành</li>
                                <li>Upload hình ảnh minh chứng</li>
                                <li>Đợi admin xác minh</li>
                            </ol>
                            @break

                        @default
                            <p>Làm theo yêu cầu của nhiệm vụ và gửi minh chứng.</p>
                    @endswitch

                    <hr>

                    <p class="small text-muted mb-0">
                        <strong>Lưu ý:</strong> Yêu cầu sẽ được admin xem xét trong vòng 24-48 giờ.
                        Bạn sẽ nhận điểm khi yêu cầu được phê duyệt.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput) {
        imageInput.addEventListener('change', function() {
            imagePreview.innerHTML = '';
            const files = this.files;

            if (files.length > 5) {
                toastr.warning('Chỉ được upload tối đa 5 hình');
                this.value = '';
                return;
            }

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.size > 5 * 1024 * 1024) {
                    toastr.warning('File ' + file.name + ' quá lớn (tối đa 5MB)');
                    continue;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    imagePreview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    const submitForm = document.getElementById('submitForm');
    const submitBtn = document.getElementById('submitBtn');

    if (submitForm) {
        submitForm.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang gửi...';
        });
    }
});
</script>
@endsection
