@extends('layouts.app')

@section('title', $info['name'] . ' Huy hiệu - Người dùng')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <div class="badge-icon me-3 p-3 rounded-circle bg-warning text-white">
                        {{ $info['icon'] }}
                    </div>
                    <div>
                        <h2 class="mb-0">{{ $info['name'] }}</h2>
                        <p class="text-muted mb-0">{{ $info['description'] }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.ocr.badges.index') }}" class="btn btn-outline-secondary">
                    Quay lại Huy hiệu
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Người dùng có huy hiệu này ({{ $users->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($users->isEmpty())
                        <p class="text-muted">Chưa có người dùng nào kiếm được huy hiệu này.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mã số</th>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Xếp hạng Elo</th>
                                    <th>Kiếm được lúc</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $user->elo_rating }}</span>
                                            <small class="text-muted">({{ $user->elo_rank }})</small>
                                        </td>
                                        <td>
                                            @php
                                                $badge = $user->badges->first();
                                            @endphp
                                            {{ $badge ? $badge->earned_at->format('Y-m-d H:i') : '-' }}
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.ocr.badges.revoke') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <input type="hidden" name="badge_type" value="{{ $badgeType }}">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hủy huy hiệu từ {{ $user->name }}?')">
                                                    Hủy
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
