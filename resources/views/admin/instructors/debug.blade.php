@extends('layouts.app')
@section('title', 'Debug Instructor Form')

@section('content')
    <div class="container mt-5">
        <h2>Debug Form Data</h2>
        
        <div class="alert alert-info">
            <p>Dữ liệu form gửi lên server sẽ hiển thị ở đây</p>
        </div>

        <form action="{{ route('admin.instructors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- NAME FIELD ONLY FOR QUICK TEST -->
            <div class="mb-3">
                <label class="form-label">Tên giảng viên</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">SUBMIT TEST</button>
            <a href="{{ route('admin.instructors.create') }}" class="btn btn-secondary">Back to Full Form</a>
        </form>
    </div>
@endsection
