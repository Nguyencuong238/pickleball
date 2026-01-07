@props(['elo' => null, 'level' => null, 'size' => 'md', 'showName' => true])

@php
/**
 * Skill Level Badge Component
 * Based on ELO rating from Skill Quiz
 *
 * Usage:
 *   <x-oprs.skill-level-badge :elo="1200" />
 *   <x-oprs.skill-level-badge level="4.5" />
 *   <x-oprs.skill-level-badge :elo="$user->elo_rating" size="lg" :showName="false" />
 */

$levels = [
    '2.0-2.5' => ['name' => 'Newbie', 'desc' => 'Mới chơi, chưa kiểm soát bóng', 'class' => 'skill-newbie'],
    '2.5' => ['name' => 'Nghiệp dư', 'desc' => 'Biết luật, đánh qua lưới ổn', 'class' => 'skill-beginner'],
    '2.8-3.0' => ['name' => 'Cơ bản', 'desc' => 'Đánh rally ngắn, bắt đầu có chiến thuật', 'class' => 'skill-basic'],
    '3.2-3.5' => ['name' => 'Trung bình', 'desc' => 'Phổ biến nhất ở giải social', 'class' => 'skill-intermediate'],
    '3.8-4.0' => ['name' => 'Khá', 'desc' => 'Đánh ổn định, có dink, reset', 'class' => 'skill-upper-intermediate'],
    '4.3-4.5' => ['name' => 'Nâng cao', 'desc' => 'Trình giải phong trào mạnh', 'class' => 'skill-advanced'],
    '4.8-5.0' => ['name' => 'Bán chuyên', 'desc' => 'Bán chuyên, thi đấu thường xuyên', 'class' => 'skill-semi-pro'],
    '5.3-5.5' => ['name' => 'Chuyên nghiệp', 'desc' => 'VĐV mạnh, chiến thuật cao', 'class' => 'skill-pro'],
    '5.8-6.0' => ['name' => 'Cao thủ', 'desc' => 'Chuyên nghiệp quốc gia', 'class' => 'skill-expert'],
    '6.0+' => ['name' => 'Elite', 'desc' => 'Elite / Pro quốc tế', 'class' => 'skill-elite'],
];

// Calculate level from ELO if not provided
if ($level === null && $elo !== null) {
    $level = match (true) {
        $elo < 800 => '2.0-2.5',
        $elo < 900 => '2.5',
        $elo < 1000 => '2.8-3.0',
        $elo < 1100 => '3.2-3.5',
        $elo < 1200 => '3.8-4.0',
        $elo < 1300 => '4.3-4.5',
        $elo < 1400 => '4.8-5.0',
        $elo < 1500 => '5.3-5.5',
        $elo < 1600 => '5.8-6.0',
        default => '6.0+',
    };
}

$info = $levels[$level] ?? $levels['2.0-2.5'];
@endphp

<span class="skill-level-badge {{ $info['class'] }} size-{{ $size }}" title="{{ $info['desc'] }}">
    <span class="level-number">{{ $level }}</span>
    @if($showName)
        <span class="level-name">{{ $info['name'] }}</span>
    @endif
</span>

<style>
.skill-level-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 9999px;
    font-weight: 500;
    border: 1px solid;
    cursor: help;
}

.skill-level-badge.size-sm {
    padding: 0.125rem 0.5rem;
    font-size: 0.75rem;
}

.skill-level-badge.size-md {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

.skill-level-badge.size-lg {
    padding: 0.5rem 1rem;
    font-size: 1rem;
}

.skill-level-badge .level-number {
    font-weight: 700;
}

.skill-level-badge .level-name {
    margin-left: 0.25rem;
}

/* Skill Level Colors - 10 levels from gray to gold */
.skill-newbie {
    background-color: #f3f4f6;
    color: #374151;
    border-color: #d1d5db;
}

.skill-beginner {
    background-color: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
}

.skill-basic {
    background-color: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.skill-intermediate {
    background-color: #dbeafe;
    color: #1d4ed8;
    border-color: #93c5fd;
}

.skill-upper-intermediate {
    background-color: #e0e7ff;
    color: #4338ca;
    border-color: #a5b4fc;
}

.skill-advanced {
    background-color: #f3e8ff;
    color: #7c3aed;
    border-color: #c4b5fd;
}

.skill-semi-pro {
    background-color: #fce7f3;
    color: #be185d;
    border-color: #f9a8d4;
}

.skill-pro {
    background-color: #ffedd5;
    color: #c2410c;
    border-color: #fdba74;
}

.skill-expert {
    background-color: #fef3c7;
    color: #b45309;
    border-color: #fcd34d;
}

.skill-elite {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fcd34d 100%);
    color: #92400e;
    border-color: #f59e0b;
    box-shadow: 0 0 8px rgba(245, 158, 11, 0.4);
}
</style>
