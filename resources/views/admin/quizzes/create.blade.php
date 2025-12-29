@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Thêm Câu Hỏi Quiz Mới</h2>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.quizzes.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="title">Tiêu Đề Câu Hỏi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Mô Tả</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="2">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="question">Nội Dung Câu Hỏi <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('question') is-invalid @enderror" 
                              id="question" name="question" rows="3" required>{{ old('question') }}</textarea>
                    @error('question')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="option_a">Lựa Chọn A <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('option_a') is-invalid @enderror" 
                                   id="option_a" name="option_a" value="{{ old('option_a') }}" required>
                            @error('option_a')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="option_b">Lựa Chọn B <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('option_b') is-invalid @enderror" 
                                   id="option_b" name="option_b" value="{{ old('option_b') }}" required>
                            @error('option_b')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="option_c">Lựa Chọn C <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('option_c') is-invalid @enderror" 
                                   id="option_c" name="option_c" value="{{ old('option_c') }}" required>
                            @error('option_c')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="option_d">Lựa Chọn D <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('option_d') is-invalid @enderror" 
                                   id="option_d" name="option_d" value="{{ old('option_d') }}" required>
                            @error('option_d')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="correct_answer">Đáp Án Đúng <span class="text-danger">*</span></label>
                    <select class="form-control @error('correct_answer') is-invalid @enderror" 
                            id="correct_answer" name="correct_answer" required>
                        <option value="">-- Chọn đáp án --</option>
                        <option value="a" {{ old('correct_answer') == 'a' ? 'selected' : '' }}>A</option>
                        <option value="b" {{ old('correct_answer') == 'b' ? 'selected' : '' }}>B</option>
                        <option value="c" {{ old('correct_answer') == 'c' ? 'selected' : '' }}>C</option>
                        <option value="d" {{ old('correct_answer') == 'd' ? 'selected' : '' }}>D</option>
                    </select>
                    @error('correct_answer')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="explanation">Giải Thích Đáp Án</label>
                    <textarea class="form-control @error('explanation') is-invalid @enderror" 
                              id="explanation" name="explanation" rows="3">{{ old('explanation') }}</textarea>
                    @error('explanation')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category">Danh Mục</label>
                            <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                   id="category" name="category" value="{{ old('category') }}" 
                                   placeholder="Nhập danh mục mới hoặc chọn có sẵn" list="categories">
                            <datalist id="categories">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                            @error('category')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="difficulty">Độ Khó <span class="text-danger">*</span></label>
                            <select class="form-control @error('difficulty') is-invalid @enderror" 
                                    id="difficulty" name="difficulty" required>
                                <option value="">-- Chọn độ khó --</option>
                                <option value="1" {{ old('difficulty') == '1' ? 'selected' : '' }}>1 - Dễ</option>
                                <option value="2" {{ old('difficulty') == '2' ? 'selected' : '' }}>2 - Trung Bình</option>
                                <option value="3" {{ old('difficulty') == '3' ? 'selected' : '' }}>3 - Khó</option>
                            </select>
                            @error('difficulty')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_active" 
                               name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">
                            Kích Hoạt Câu Hỏi
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu Câu Hỏi
                    </button>
                    <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
