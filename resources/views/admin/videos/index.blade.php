@extends('layouts.app')
@section('title', 'Quản lý video Pickleball')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="mb-3 text-end">
                    <a href="{{ route('admin.videos.create') }}" class="btn btn-primary">Thêm</a>
                </div>

                <!-- Table Responsive -->
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tên Video</th>
                                <th class="text-center">Ảnh</th>
                                <th>Danh mục</th>
                                <th>Mô Tả</th>
                                <th>Link Video</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($videos as $video)
                                <tr>
                                    <td>{{ $video->name }}</td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        @if ($video->image)
                                            <div style="display:flex; justify-content:center; align-items:center; height:80px;">
                                                <img src="{{ asset('storage/' . $video->image) }}" alt="image"
                                                    style="height:80px; width:120px; object-fit:cover;">
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $video->category->name ?? 'N/A' }}</td>
                                    <td>
                                        <small>{{ Str::limit($video->description, 50) }}</small>
                                    </td>
                                    <td>
                                        @if($video->video_link)
                                            <a href="{{ $video->video_link }}" target="_blank" class="btn btn-sm btn-info">Xem</a>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.videos.edit', $video->id) }}"
                                                class="btn btn-sm btn-warning mb-1">Sửa</a>
                                            <form action="{{ route('admin.videos.destroy', $video->id) }}" method="POST"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger mb-1"
                                                    onclick="return confirm('Xóa video này?')">Xóa</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có video nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav class="d-flex justify-content-center mt-5 mb-4">
                    {{ $videos->links('pagination::bootstrap-4') }}
                </nav>
            </div>
        </div>
    </div>
@endsection
