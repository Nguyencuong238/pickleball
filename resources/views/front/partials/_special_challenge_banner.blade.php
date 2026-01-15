@if(isset($specialChallenges) && $specialChallenges->count() > 0)
<div class="special-challenge-banner mb-4">
    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger pulse-animation">🔥 HOT</span>
                    <div>
                        <h6 class="mb-0 text-dark fw-bold">
                            {{ $specialChallenges->first()->title }}
                        </h6>
                        <small class="text-dark opacity-75">
                            +{{ $specialChallenges->first()->points }} điểm |
                            Kết thúc {{ $specialChallenges->first()->end_date->format('d/m') }}
                        </small>
                    </div>
                </div>
                @auth
                    <a href="{{ route('user.points.index') }}" class="btn btn-dark btn-sm">
                        Tham Gia →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-dark btn-sm">
                        Đăng nhập để tham gia →
                    </a>
                @endauth
            </div>
            @if($specialChallenges->count() > 1)
                <div class="mt-2">
                    <small class="text-dark opacity-75">
                        +{{ $specialChallenges->count() - 1 }} thách thức khác đang chờ
                    </small>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.pulse-animation {
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
@endif
