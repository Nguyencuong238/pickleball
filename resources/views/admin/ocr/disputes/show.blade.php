@extends('layouts.app')

@section('title', 'Xem xét Tranh chấp #' . $match->id)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Xem xét Tranh chấp #{{ $match->id }}</h2>
                <a href="{{ route('admin.ocr.disputes.index') }}" class="btn btn-outline-secondary">
                    Quay lại Tranh chấp
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    {{-- Chi tiết Trận đấu --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Chi tiết Trận đấu</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Loại:</th>
                                    <td><span class="badge bg-info">{{ ucfirst($match->match_type) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Thời gian lên lịch:</th>
                                    <td>{{ $match->scheduled_date?->format('Y-m-d') }} {{ $match->scheduled_time }}</td>
                                </tr>
                                <tr>
                                    <th>Địa điểm:</th>
                                    <td>{{ $match->location ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Điểm số được gửi:</th>
                                    <td class="fs-4">{{ $match->challenger_score }} - {{ $match->opponent_score }}</td>
                                </tr>
                                <tr>
                                    <th>Đội thắng:</th>
                                    <td>Đội {{ ucfirst($match->winner_team) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Lý do tranh chấp --}}
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Lý do tranh chấp</h5>
                        </div>
                        <div class="card-body">
                            <p>{{ $match->disputed_reason }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    {{-- Đội chơi --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Người tham gia</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <h6>Đội người thách đấu</h6>
                                    <div class="mb-2">
                                        <strong>{{ $match->challenger->name ?? 'Không xác định' }}</strong>
                                        <br><small class="text-muted">Elo: {{ $match->challenger->elo_rating ?? '-' }}</small>
                                    </div>
                                    @if($match->challengerPartner)
                                        <div>
                                            {{ $match->challengerPartner->name }}
                                            <br><small class="text-muted">Elo: {{ $match->challengerPartner->elo_rating }}</small>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-6">
                                    <h6>Đội đối thủ</h6>
                                    <div class="mb-2">
                                        <strong>{{ $match->opponent->name ?? 'Không xác định' }}</strong>
                                        <br><small class="text-muted">Elo: {{ $match->opponent->elo_rating ?? '-' }}</small>
                                    </div>
                                    @if($match->opponentPartner)
                                        <div>
                                            {{ $match->opponentPartner->name }}
                                            <br><small class="text-muted">Elo: {{ $match->opponentPartner->elo_rating }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bằng chứng --}}
                    @if($match->media->isNotEmpty())
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Bằng chứng ({{ $match->media->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($match->media as $media)
                                        <div class="col-4">
                                            @if(Str::startsWith($media->mime_type, 'image'))
                                                <a href="{{ $media->getUrl() }}" target="_blank">
                                                    <img src="{{ $media->getUrl() }}" class="img-fluid rounded" alt="Bằng chứng">
                                                </a>
                                            @else
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-outline-secondary w-100">
                                                    [VIDEO] Xem
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Các tùy chọn giải quyết --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tùy chọn giải quyết</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Xác nhận kết quả ban đầu --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <h6>Xác nhận kết quả ban đầu</h6>
                                    <p class="small text-muted">Chấp nhận điểm số được gửi và cập nhật xếp hạng Elo</p>
                                    <form action="{{ route('admin.ocr.disputes.confirm', $match) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Xác nhận kết quả ban đầu?')">
                                            Xác nhận kết quả
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Ghi đè kết quả --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <h6>Ghi đè kết quả</h6>
                                    <p class="small text-muted">Đặt một điểm số khác và cập nhật Elo</p>
                                    <form action="{{ route('admin.ocr.disputes.override', $match) }}" method="POST">
                                        @csrf
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <input type="number" name="challenger_score" class="form-control" placeholder="Người thách đấu" min="0" max="99" required>
                                            </div>
                                            <div class="col-6">
                                                <input type="number" name="opponent_score" class="form-control" placeholder="Đối thủ" min="0" max="99" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-warning" onclick="return confirm('Ghi đè bằng điểm số mới?')">
                                            Ghi đè
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Hủy trận đấu --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-danger">
                                <div class="card-body text-center">
                                    <h6>Hủy trận đấu</h6>
                                    <p class="small text-muted">Vô hiệu hóa trận đấu hoàn toàn, không thay đổi Elo</p>
                                    <form action="{{ route('admin.ocr.disputes.cancel', $match) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Hủy trận đấu này?')">
                                            Hủy trận đấu
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
