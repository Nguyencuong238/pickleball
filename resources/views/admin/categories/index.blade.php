@extends('layouts.app')

@section('title', 'Quản lý danh mục')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm danh mục
            </a>
        </div>
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> Có lỗi xảy ra!
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($categories->count())
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">STT</th>
                            <th>Tên danh mục</th>
                            <th>Slug</th>
                            <th>Mô tả</th>
                            {{-- <th width="100">Icon</th> --}}
                            {{-- <th width="80">Thứ tự</th> --}}
                            {{-- <th width="100">Trạng thái</th> --}}
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $key => $category)
                        <tr>
                            <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                            <td>
                                <strong>{{ $category->name }}</strong>
                            </td>
                            <td>
                                <code>{{ $category->slug }}</code>
                            </td>
                            <td>
                                {{ Str::limit($category->description, 50) }}
                            </td>
                            {{-- <td class="text-center">
                                @if($category->icon)
                                <i class="{{ $category->icon }}"></i>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td> --}}
                            {{-- <td class="text-center">
                                <span class="badge badge-info">{{ $category->order }}</span>
                            </td> --}}
                            {{-- <td class="text-center">
                                @if($category->status)
                                <span class="badge badge-success">Hoạt động</span>
                                @else
                                <span class="badge badge-secondary">Vô hiệu</span>
                                @endif
                            </td> --}}
                            <td>
                                <div class="btn-group btn-group-sm gap-2" role="group">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning px-2 rounded" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox"></i> Chưa có danh mục nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <nav aria-label="Page navigation">
                {{ $categories->links('pagination::bootstrap-4') }}
            </nav>
            @else
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i>
                <strong>Chưa có dữ liệu!</strong> Hãy
                <a href="{{ route('admin.categories.create') }}" class="alert-link">tạo danh mục mới</a>.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Xóa thông báo sau 5 giây
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@endsection
