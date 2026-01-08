@extends('layouts.front')

@section('title', 'Yêu cầu xác minh OPR')

@section('css')
<style>
    .verification-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .verification-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .verification-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .verification-header p {
        color: #64748b;
    }

    .verification-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }

    .form-hint {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: border-color 0.2s;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .file-upload {
        border: 2px dashed #e2e8f0;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .file-upload:hover {
        border-color: #3b82f6;
        background: #f8fafc;
    }

    .file-upload input[type="file"] {
        display: none;
    }

    .file-upload-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .file-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .file-preview-item {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 2px solid #e2e8f0;
    }

    .file-preview-item img,
    .file-preview-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .file-preview-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 24px;
        height: 24px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .links-container {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .link-row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .link-row select {
        width: 150px;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
    }

    .link-row input {
        flex: 1;
    }

    .link-row button {
        padding: 0.75rem;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
    }

    .add-link-btn {
        padding: 0.75rem 1.5rem;
        background: #f1f5f9;
        color: #1e293b;
        border: 2px dashed #e2e8f0;
        border-radius: 0.5rem;
        cursor: pointer;
        font-weight: 600;
    }

    .add-link-btn:hover {
        background: #e2e8f0;
    }

    .pending-alert {
        background: #fef3c7;
        border: 2px solid #f59e0b;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .rejected-alert {
        background: #fee2e2;
        border: 2px solid #ef4444;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .submit-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #00D9B5, #0099CC);
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
    }

    .submit-btn:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        transform: none;
    }

    .user-info-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .user-stats {
        display: flex;
        gap: 2rem;
        margin-top: 0.5rem;
    }

    .user-stat {
        font-size: 0.875rem;
        color: #64748b;
    }

    .user-stat strong {
        color: #1e293b;
    }
</style>
@endsection

@section('content')
<div class="verification-container">
    <div class="verification-header">
        <h1>Yêu cầu xác minh OPR</h1>
        <p>Gửi bằng chứng để xác minh mức độ kỹ năng của bạn</p>
    </div>

    <!-- User Info -->
    <div class="verification-card">
        <div class="user-info-card">
            @if($user->getAvatarUrl())
                <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="user-avatar" style="background: none;">
            @else
                <div class="user-avatar">{{ $user->getInitials() }}</div>
            @endif
            <div>
                <h3 style="margin: 0;">{{ $user->name }}</h3>
                <div class="user-stats">
                    <span class="user-stat">OPR Level: <strong>{{ $user->opr_level ?? '2.0' }}</strong></span>
                    <span class="user-stat">ELO: <strong>{{ number_format($user->elo_rating ?? 1000) }}</strong></span>
                    <span class="user-stat">Trận đấu: <strong>{{ $user->total_ocr_matches ?? 0 }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    @if($pendingRequest)
    <div class="pending-alert">
        <h4 style="margin: 0 0 0.5rem 0;">⏳ Yêu cầu đang chờ duyệt</h4>
        <p style="margin: 0;">Bạn đã gửi yêu cầu xác minh vào {{ $pendingRequest->created_at->format('d/m/Y H:i') }}. Vui lòng chờ người xác minh duyệt.</p>
        <a href="{{ route('opr-verification.show', $pendingRequest) }}" style="color: #92400e; font-weight: 600; margin-top: 0.5rem; display: inline-block;">
            Xem trạng thái →
        </a>
    </div>
    @else

    @if($rejectedRequest)
    <div class="rejected-alert">
        <h4 style="margin: 0 0 0.5rem 0;">❌ Yêu cầu trước đã bị từ chối</h4>
        <p style="margin: 0 0 0.5rem 0;">Lý do: {{ $rejectedRequest->verifier_notes }}</p>
        <p style="margin: 0; font-size: 0.875rem;">Bạn có thể gửi lại yêu cầu mới với bằng chứng khác.</p>
    </div>
    @endif

    <div class="verification-card">
        <form action="{{ route('opr-verification.store') }}" method="POST" enctype="multipart/form-data" id="verification-form">
            @csrf

            <!-- Notes -->
            <div class="form-group">
                <label class="form-label">Ghi chú (không bắt buộc)</label>
                <textarea name="notes" class="form-textarea" rows="3"
                          placeholder="Mô tả kinh nghiệm chơi pickleball của bạn, các giải đấu đã tham gia...">{{ old('notes') }}</textarea>
                <p class="form-hint">Cung cấp thêm thông tin để người xác minh đánh giá chính xác hơn.</p>
            </div>

            <!-- Images Upload -->
            <div class="form-group">
                <label class="form-label">Hình ảnh (tối đa {{ $maxImages }} ảnh)</label>
                <div class="file-upload" onclick="document.getElementById('images-input').click()">
                    <input type="file" name="images[]" id="images-input" accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="file-upload-icon">📷</div>
                    <div>Nhấn để chọn hoặc kéo thả hình ảnh</div>
                    <div style="font-size: 0.875rem; color: #64748b;">JPEG, PNG, WebP - Tối đa 5MB/ảnh</div>
                </div>
                <div id="images-preview" class="file-preview"></div>
            </div>

            <!-- Videos Upload -->
            <div class="form-group">
                <label class="form-label">Video (tối đa {{ $maxVideos }} video)</label>
                <div class="file-upload" onclick="document.getElementById('videos-input').click()">
                    <input type="file" name="videos[]" id="videos-input" accept="video/mp4,video/quicktime,video/webm" multiple>
                    <div class="file-upload-icon">🎬</div>
                    <div>Nhấn để chọn hoặc kéo thả video</div>
                    <div style="font-size: 0.875rem; color: #64748b;">MP4, MOV, WebM - Tối đa 50MB/video</div>
                </div>
                <div id="videos-preview" class="file-preview"></div>
            </div>

            <!-- External Links -->
            <div class="form-group">
                <label class="form-label">Liên kết video trực tuyến</label>
                <p class="form-hint" style="margin-bottom: 0.75rem;">Thêm link video trận đấu từ YouTube, Facebook hoặc TikTok</p>
                <div id="links-container" class="links-container">
                    <!-- Links will be added here -->
                </div>
                <button type="button" class="add-link-btn" onclick="addLinkRow()" style="margin-top: 0.75rem;">
                    + Thêm liên kết
                </button>
            </div>

            <button type="submit" class="submit-btn">
                Gửi yêu cầu xác minh
            </button>
        </form>
    </div>
    @endif
</div>
@endsection

@section('js')
<script>
const maxImages = {{ $maxImages }};
const maxVideos = {{ $maxVideos }};
let linkCount = 0;

// Image preview
document.getElementById('images-input').addEventListener('change', function(e) {
    const preview = document.getElementById('images-preview');
    preview.innerHTML = '';

    const files = Array.from(e.target.files).slice(0, maxImages);
    files.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'file-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="file-preview-remove" onclick="removeFile('images', ${index})">×</button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

// Video preview
document.getElementById('videos-input').addEventListener('change', function(e) {
    const preview = document.getElementById('videos-preview');
    preview.innerHTML = '';

    const files = Array.from(e.target.files).slice(0, maxVideos);
    files.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = 'file-preview-item';
        div.innerHTML = `
            <video src="${URL.createObjectURL(file)}"></video>
            <button type="button" class="file-preview-remove" onclick="removeFile('videos', ${index})">×</button>
        `;
        preview.appendChild(div);
    });
});

function removeFile(type, index) {
    const input = document.getElementById(type + '-input');
    const dt = new DataTransfer();
    const files = Array.from(input.files);

    files.forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });

    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
}

function addLinkRow() {
    const container = document.getElementById('links-container');
    const row = document.createElement('div');
    row.className = 'link-row';
    row.innerHTML = `
        <select name="links[${linkCount}][type]">
            <option value="youtube">YouTube</option>
            <option value="facebook">Facebook</option>
            <option value="tiktok">TikTok</option>
            <option value="other">Khác</option>
        </select>
        <input type="url" name="links[${linkCount}][url]" class="form-input" placeholder="https://...">
        <button type="button" onclick="this.parentElement.remove()">×</button>
    `;
    container.appendChild(row);
    linkCount++;
}

// Add initial link row
addLinkRow();
</script>
@endsection
