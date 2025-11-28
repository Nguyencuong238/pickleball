@extends('layouts.app')

@section('title', 'Danh Sách Đăng Ký Học')

@section('content')
<div class="container-fluid">
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Lỗi!</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card" style="border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
        <div class="card-header" style="background-color: #f8fafc; padding: 20px; border-bottom: 1px solid #e2e8f0;">
            <h5 style="margin: 0; font-weight: 600;">Danh Sách Đăng Ký Học</h5>
        </div>
        <div class="card-body" style="padding: 20px;">
            @if($registrations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" style="margin-bottom: 0;">
                        <thead style="background-color: #f1f5f9;">
                            <tr>
                                <th style="border: none; padding: 12px; font-weight: 600; color: #475569;">Tên Thầy</th>
                                <th style="border: none; padding: 12px; font-weight: 600; color: #475569;">Người Đăng Ký</th>
                                <th style="border: none; padding: 12px; font-weight: 600; color: #475569;">Gói Đăng Ký</th>
                                <th style="border: none; padding: 12px; font-weight: 600; color: #475569;">Số Điện Thoại</th>
                                <th style="border: none; padding: 12px; font-weight: 600; color: #475569;">Ghi Chú</th>
                                <th style="border: none; padding: 12px; font-weight: 600; color: #475569; text-align: center;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 12px; color: #1e293b;">
                                        @if($registration->instructor)
                                            {{ $registration->instructor->name }}
                                        @else
                                            <span style="color: #94a3b8;">N/A</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px; color: #1e293b;">{{ $registration->customer_name }}</td>
                                    <td style="padding: 12px; color: #1e293b;">
                                        @if($registration->package)
                                            {{ $registration->package->name }}
                                        @else
                                            <span style="color: #94a3b8;">N/A</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px; color: #1e293b;">{{ $registration->customer_phone }}</td>
                                    <td style="padding: 12px; color: #1e293b;">
                                        @if($registration->notes)
                                            <small>{{ Str::limit($registration->notes, 50) }}</small>
                                        @else
                                            <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <form action="{{ route('admin.instructor-registrations.destroy', $registration->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa đăng ký này?')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 20px; text-align: center; color: #94a3b8;">
                                        <i class="fas fa-inbox"></i> Không có dữ liệu đăng ký học
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($registrations->hasPages())
                    <div style="margin-top: 20px;">
                        {{ $registrations->links() }}
                    </div>
                @endif
            @else
                <div style="padding: 40px; text-align: center; color: #94a3b8;">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p style="margin: 10px 0;">Chưa có dữ liệu đăng ký học</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table-responsive {
        border-radius: 0.5rem;
    }
    
    .btn-danger {
        background-color: #ef4444;
        border-color: #ef4444;
        padding: 6px 12px;
        font-size: 0.875rem;
    }
    
    .btn-danger:hover {
        background-color: #dc2626;
        border-color: #dc2626;
    }
</style>
@endsection
