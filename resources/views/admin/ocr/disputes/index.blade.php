@extends('layouts.app')

@section('title', 'Những trận đấu có tranh chấp OCR')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Những trận đấu có tranh chấp OCR</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    @if($disputes->isEmpty())
                        <p class="text-muted text-center py-4">Không có trận đấu nào có tranh chấp vào lúc này.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mã số</th>
                                    <th>Loại</th>
                                    <th>Người thách đấu</th>
                                    <th>Đối thủ</th>
                                    <th>Điểm số</th>
                                    <th>Lý do tranh chấp</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disputes as $match)
                                    <tr>
                                        <td>#{{ $match->id }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($match->match_type) }}</span></td>
                                        <td>
                                            {{ $match->challenger->name ?? 'Không xác định' }}
                                            @if($match->challengerPartner)
                                                <br><small class="text-muted">+ {{ $match->challengerPartner->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $match->opponent->name ?? 'Không xác định' }}
                                            @if($match->opponentPartner)
                                                <br><small class="text-muted">+ {{ $match->opponentPartner->name }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $match->challenger_score }} - {{ $match->opponent_score }}</td>
                                        <td>
                                            <small>{{ Str::limit($match->disputed_reason, 50) }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ocr.disputes.show', $match) }}" class="btn btn-sm btn-primary">
                                                Xem xét
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{ $disputes->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
