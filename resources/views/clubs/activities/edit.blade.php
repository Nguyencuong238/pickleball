@extends('layouts.front')

@section('content')
<style>
    .activity-form-container {
        padding: 40px 20px;
        max-width: 800px;
        margin: 0 auto;
        margin-top: 100px;
    }

    .form-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .form-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #00D9B5;
        box-shadow: 0 0 0 3px rgba(0, 217, 181, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .btn-group {
        display: flex;
        gap: 15px;
        margin-top: 40px;
    }

    .btn-submit,
    .btn-cancel,
    .btn-delete {
        flex: 1;
        padding: 14px 30px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }

    .btn-submit {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #6b7280;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-delete {
        background: #fee2e2;
        color: #b91c1c;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    .error-message {
        background: #fee2e2;
        color: #b91c1c;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .error-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .error-list li {
        padding: 4px 0;
    }

    .hint {
        font-size: 0.85rem;
        color: #9ca3af;
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .btn-group {
            flex-direction: column;
        }

        .form-header h2 {
            font-size: 1.5rem;
        }
    }
</style>

<div class="activity-form-container">
    <div class="form-header">
        <h2>✏️ Chỉnh Sửa Hoạt Động</h2>
        <p>Cập nhật thông tin hoạt động</p>
    </div>

    <div class="form-card">
        @if($errors->any())
            <div class="error-message">
                <strong>Vui lòng sửa các lỗi sau:</strong>
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clubs.activities.update', [$club, $activity]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Tên Hoạt Động <span style="color: red;">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $activity->title) }}" required 
                    placeholder="VD: Giải đấu nội bộ, Buổi huấn luyện...">
            </div>

            <div class="form-group">
                <label for="description">Mô Tả Chi Tiết</label>
                <textarea id="description" name="description" placeholder="Mô tả chi tiết về hoạt động này...">{{ old('description', $activity->description) }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="activity_date">Ngày & Giờ <span style="color: red;">*</span></label>
                    <input type="datetime-local" id="activity_date" name="activity_date" 
                        value="{{ old('activity_date', $activity->activity_date->format('Y-m-d\TH:i')) }}" required>
                    <div class="hint">Chọn ngày và giờ tổ chức hoạt động</div>
                </div>

                <div class="form-group">
                    <label for="status">Trạng Thái <span style="color: red;">*</span></label>
                    <select id="status" name="status" required>
                        <option value="upcoming" {{ old('status', $activity->status) === 'upcoming' ? 'selected' : '' }}>📅 Sắp tới</option>
                        <option value="completed" {{ old('status', $activity->status) === 'completed' ? 'selected' : '' }}>✓ Đã hoàn thành</option>
                        <option value="cancelled" {{ old('status', $activity->status) === 'cancelled' ? 'selected' : '' }}>✕ Đã hủy</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="location">Địa Điểm</label>
                <input type="text" id="location" name="location" value="{{ old('location', $activity->location) }}" 
                    placeholder="VD: Sân 1 - Hà Nội, Sân pickleball ABC...">
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-submit">✓ Cập Nhật</button>
                <a href="{{ route('clubs.activities.index', $club) }}" class="btn-cancel">← Quay Lại</a>
                <form action="{{ route('clubs.activities.destroy', [$club, $activity]) }}" method="POST" style="flex: 1; display: flex;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete" style="width: 100%;" onclick="return confirm('Bạn chắc chắn muốn xóa hoạt động này?')">🗑️ Xóa</button>
                </form>
            </div>
        </form>
    </div>
</div>

@endsection
