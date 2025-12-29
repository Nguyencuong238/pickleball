@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $quiz->title }}</h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Sửa
            </a>
            <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($quiz->description)
            <div class="mb-3">
                <h5>Mô Tả</h5>
                <p>{{ $quiz->description }}</p>
            </div>
            @endif

            <div class="mb-3">
                <h5>Câu Hỏi</h5>
                <p>{{ $quiz->question }}</p>
            </div>

            <div class="mb-3">
                <h5>Các Lựa Chọn</h5>
                <div class="list-group">
                    <div class="list-group-item {{ $quiz->correct_answer == 'a' ? 'bg-success text-white' : '' }}">
                        <strong>A.</strong> {{ $quiz->options['a'] }}
                        @if($quiz->correct_answer == 'a')
                            <span class="badge badge-light float-right">✓ Đúng</span>
                        @endif
                    </div>
                    <div class="list-group-item {{ $quiz->correct_answer == 'b' ? 'bg-success text-white' : '' }}">
                        <strong>B.</strong> {{ $quiz->options['b'] }}
                        @if($quiz->correct_answer == 'b')
                            <span class="badge badge-light float-right">✓ Đúng</span>
                        @endif
                    </div>
                    <div class="list-group-item {{ $quiz->correct_answer == 'c' ? 'bg-success text-white' : '' }}">
                        <strong>C.</strong> {{ $quiz->options['c'] }}
                        @if($quiz->correct_answer == 'c')
                            <span class="badge badge-light float-right">✓ Đúng</span>
                        @endif
                    </div>
                    <div class="list-group-item {{ $quiz->correct_answer == 'd' ? 'bg-success text-white' : '' }}">
                        <strong>D.</strong> {{ $quiz->options['d'] }}
                        @if($quiz->correct_answer == 'd')
                            <span class="badge badge-light float-right">✓ Đúng</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($quiz->explanation)
            <div class="mb-3">
                <h5>Giải Thích</h5>
                <div class="alert alert-info">
                    {{ $quiz->explanation }}
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6>Danh Mục</h6>
                        @if($quiz->category)
                            <span class="badge badge-info" style="color: black">{{ $quiz->category }}</span>
                        @else
                            <span class="text-muted" style="color: black">Chưa xác định</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6>Độ Khó</h6>
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
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <h6>Trạng Thái</h6>
                @if($quiz->is_active)
                    <span class="badge badge-success" style="color: black">Kích Hoạt</span>
                @else
                    <span class="badge badge-secondary" style="color: black">Vô Hiệu</span>
                @endif
            </div>

            <div class="mb-3 text-muted small">
                <p>
                    Tạo lúc: {{ $quiz->created_at->format('d/m/Y H:i') }}<br>
                    Cập nhật lúc: {{ $quiz->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <div>
                <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                        <i class="fas fa-trash"></i> Xóa Câu Hỏi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
