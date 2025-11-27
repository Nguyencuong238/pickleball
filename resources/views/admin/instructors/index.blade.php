@extends('layouts.app')
@section('title', 'Quản lý giảng viên')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="mb-3 text-end">
                    <a href="{{ route('admin.instructors.create') }}" class="btn btn-primary">Thêm</a>
                </div>

                <!-- Table Responsive -->
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tên giảng viên</th>
                                <th class="text-center">Ảnh</th>
                                <th>Kinh nghiệm</th>
                                <th>Phường</th>
                                <th>Tỉnh, TP</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($instructors as $instructor)
                                <tr>
                                    <td>{{ $instructor->name }}</td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        @if ($instructor->image)
                                            <div style="display:flex; justify-content:center; align-items:center; height:80px;">
                                                <img src="{{ asset('storage/' . $instructor->image) }}" alt="image"
                                                    style="height:80px; width:120px; object-fit:cover;">
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($instructor->experience, 50) }}</small>
                                    </td>
                                    <td>{{ $instructor->ward }}</td>
                                    <td>{{ $instructor->province->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.instructors.edit', $instructor->id) }}"
                                                class="btn btn-sm btn-warning mb-1">Sửa</a>
                                            <form action="{{ route('admin.instructors.destroy', $instructor->id) }}" method="POST"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger mb-1"
                                                    onclick="return confirm('Xóa giảng viên này?')">Xóa</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có giảng viên nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav class="d-flex justify-content-center mt-5 mb-4">
                    {{ $instructors->links('pagination::bootstrap-4') }}
                </nav>
            </div>
        </div>
    </div>
@endsection
