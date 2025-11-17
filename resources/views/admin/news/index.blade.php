@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Tin tức</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="mb-3 text-end">
                <a href="{{ route('admin.news.create') }}" class="btn btn-primary">Thêm</a>
            </div>

            <!-- Table Responsive -->
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th>Slug</th>
                            <th>Người tạo</th>
                            <th class="text-center">Ảnh</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $item)
                            <tr>
                                <td>{{ $item->title }}</td>
                                <td>{{ $item->slug }}</td>
                                <td>{{ $item->author }}</td>
                                <td class="text-center" style="vertical-align: middle;">
                                    @if($item->image)
                                        <div style="display:flex; justify-content:center; align-items:center; height:80px;">
                                            <img src="{{ asset('storage/'.$item->image) }}" alt="image" style="height:80px; width:120px; object-fit:cover;">
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.news.edit', $item->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>
                                    <form action="{{ route('admin.news.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger mb-1" onclick="return confirm('Delete this news?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có tin tức nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $news->links() }}
        </div>
    </div>
</div>
@endsection
