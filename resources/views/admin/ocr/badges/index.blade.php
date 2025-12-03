@extends('layouts.app')

@section('title', 'Quản lý Huy hiệu OCR')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Quản lý Huy hiệu OCR</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Lưới Huy hiệu --}}
            <div class="row mb-4">
                @foreach($badges as $badge)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="badge-icon me-3 p-3 rounded-circle bg-warning text-white">
                                    {{ $badge['icon'] }}
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">{{ $badge['name'] }}</h5>
                                    <p class="card-text text-muted small mb-1">{{ $badge['description'] }}</p>
                                    <span class="badge bg-secondary">{{ $badge['count'] }} người dùng</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('admin.ocr.badges.show', $badge['type']) }}" class="btn btn-sm btn-outline-primary">
                                Xem Người dùng
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Biểu mẫu Trao tặng Huy hiệu --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Trao tặng Huy hiệu Thủ công</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ocr.badges.award') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label for="user_id" class="form-label">Mã số Người dùng</label>
                            <input type="number" name="user_id" id="user_id" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="badge_type" class="form-label">Loại Huy hiệu</label>
                            <select name="badge_type" id="badge_type" class="form-select" required>
                                @foreach($badges as $badge)
                                    <option value="{{ $badge['type'] }}">{{ $badge['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Trao tặng Huy hiệu</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Biểu mẫu Hủy Huy hiệu --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Hủy Huy hiệu</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ocr.badges.revoke') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label for="revoke_user_id" class="form-label">Mã số Người dùng</label>
                            <input type="number" name="user_id" id="revoke_user_id" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="revoke_badge_type" class="form-label">Loại Huy hiệu</label>
                            <select name="badge_type" id="revoke_badge_type" class="form-select" required>
                                @foreach($badges as $badge)
                                    <option value="{{ $badge['type'] }}">{{ $badge['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn hủy huy hiệu này không?')">Hủy Huy hiệu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
