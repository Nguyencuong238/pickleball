@extends('layouts.app')

@section('title', 'Những trận đấu OCR')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Những trận đấu OCR</h2>
                <div class="btn-group">
                    <a href="{{ route('admin.ocr.disputes.index') }}" class="btn btn-outline-danger">
                        Xem Tranh chấp
                    </a>
                    <a href="{{ route('admin.ocr.badges.index') }}" class="btn btn-outline-warning">
                        Quản lý Huy hiệu
                    </a>
                </div>
            </div>

            {{-- Bộ lọc trạng thái --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">Lọc theo Trạng thái:</label>
                        </div>
                        <div class="col-auto">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Tất cả Trạng thái</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    @if($matches->isEmpty())
                        <p class="text-muted text-center py-4">Không tìm thấy trận đấu nào.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mã số</th>
                                    <th>Loại</th>
                                    <th>Người thách đấu</th>
                                    <th>Đối thủ</th>
                                    <th>Điểm số</th>
                                    <th>Trạng thái</th>
                                    <th>Thay đổi Elo</th>
                                    <th>Ngày</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($matches as $match)
                                    <tr>
                                        <td>#{{ $match->id }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($match->match_type) }}</span></td>
                                        <td>{{ $match->challenger->name ?? 'Không xác định' }}</td>
                                        <td>{{ $match->opponent->name ?? 'Không xác định' }}</td>
                                        <td>
                                            @if($match->challenger_score || $match->opponent_score)
                                                {{ $match->challenger_score }} - {{ $match->opponent_score }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'secondary',
                                                    'accepted' => 'primary',
                                                    'in_progress' => 'info',
                                                    'result_submitted' => 'warning',
                                                    'confirmed' => 'success',
                                                    'disputed' => 'danger',
                                                    'cancelled' => 'dark',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$match->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($match->elo_change)
                                                <span class="text-{{ $match->winner_team === 'challenger' ? 'success' : 'danger' }}">
                                                    {{ $match->winner_team === 'challenger' ? '+' : '-' }}{{ $match->elo_change }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $match->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{ $matches->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
