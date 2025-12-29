@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Quản Lý Câu Hỏi Quiz</h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm Câu Hỏi Mới
            </a>
        </div>
    </div>

    @if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>{{ $message }}</strong>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.quizzes.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-control">
                            <option value="">-- Tất cả danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Tìm kiếm</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Tiêu Đề</th>
                            <th>Danh Mục</th>
                            <th>Độ Khó</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quizzes as $quiz)
                            <tr>
                                <td>{{ $quiz->title }}</td>
                                <td>
                                    @if($quiz->category)
                                        <span class="badge badge-info" style="color: black">{{ $quiz->category }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($quiz->difficulty)
                                        @case(1)
                                            <span class="badge badge-success" style="color: black">Dễ</span>
                                            @break
                                        @case(2)
                                            <span class="badge badge-warning" style="color: black">Trung Bình</span>
                                            @break
                                        @case(3)
                                            <span class="badge badge-danger" style="color: black">Khó</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($quiz->is_active)
                                        <span class="badge badge-success" style="color: black">Kích Hoạt</span>
                                    @else
                                        <span class="badge badge-secondary" style="color: black">Vô Hiệu</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-sm btn-info" title="Xem">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-sm btn-warning" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Không có câu hỏi nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $quizzes->links() }}
        </div>
    </div>
</div>
@endsection
