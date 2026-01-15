@props(['elo' => null, 'level' => null, 'gender' => 'male', 'size' => 'md', 'showName' => true])

@php
/**
 * Skill Level Badge Component
 * Based on ELO rating from Skill Quiz (Gender-aware)
 *
 * Usage:
 *   <x-oprs.skill-level-badge :elo="1200" />
 *   <x-oprs.skill-level-badge :elo="1200" gender="female" />
 *   <x-oprs.skill-level-badge level="4.5" />
 *   <x-oprs.skill-level-badge :elo="$user->elo_rating" :gender="$user->gender" size="lg" :showName="false" />
 */

use App\Services\SkillQuizService;

// Level descriptions and CSS classes
$levelInfo = [
    '2.0'  => ['desc' => 'Mới chơi, chưa kiểm soát bóng',           'class' => 'skill-beginner'],
    '2.5'  => ['desc' => 'Biết luật, đánh qua lưới ổn',             'class' => 'skill-novice'],
    '3.0'  => ['desc' => 'Đánh rally ngắn, bắt đầu có chiến thuật', 'class' => 'skill-intermediate'],
    '3.5'  => ['desc' => 'Phổ biến nhất ở giải social',             'class' => 'skill-upper-int'],
    '4.0'  => ['desc' => 'Đánh ổn định, có dink, reset',            'class' => 'skill-advanced'],
    '4.5'  => ['desc' => 'Trình giải phong trào mạnh',              'class' => 'skill-semi-pro'],
    '5.0'  => ['desc' => 'VĐV mạnh, chiến thuật cao',               'class' => 'skill-pro'],
    '5.5'  => ['desc' => 'Chuyên nghiệp quốc gia',                  'class' => 'skill-elite'],
    '5.5+' => ['desc' => 'Elite / Pro quốc tế',                     'class' => 'skill-elite'],
];

// Calculate level from ELO using service constants
if ($level === null && $elo !== null) {
    $thresholds = ($gender === 'female')
        ? SkillQuizService::ELO_THRESHOLDS_FEMALE
        : SkillQuizService::ELO_THRESHOLDS_MALE;

    $level = '5.5+'; // Default for highest ELO
    foreach ($thresholds as $threshold => $lvl) {
        if ($elo < $threshold) {
            $level = $lvl;
            break;
        }
    }
}

// Get level name from service constant
$levelName = SkillQuizService::SKILL_LEVEL_NAMES[$level]['vi'] ?? 'Mới chơi';
$info = $levelInfo[$level] ?? $levelInfo['2.0'];
@endphp

<span class="skill-level-badge {{ $info['class'] }} size-{{ $size }}" title="{{ $info['desc'] }}">
    <span class="level-number">{{ $level }}</span>
    @if($showName)
        <span class="level-name">{{ $levelName }}</span>
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

/* Skill Level Colors - 8 levels */
.skill-beginner {
    background-color: #f3f4f6;
    color: #374151;
    border-color: #d1d5db;
}

.skill-novice {
    background-color: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
}

.skill-intermediate {
    background-color: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.skill-upper-int {
    background-color: #dbeafe;
    color: #1d4ed8;
    border-color: #93c5fd;
}

.skill-advanced {
    background-color: #e0e7ff;
    color: #4338ca;
    border-color: #a5b4fc;
}

.skill-semi-pro {
    background-color: #f3e8ff;
    color: #7c3aed;
    border-color: #c4b5fd;
}

.skill-pro {
    background-color: #ffedd5;
    color: #c2410c;
    border-color: #fdba74;
}

.skill-elite {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fcd34d 100%);
    color: #92400e;
    border-color: #f59e0b;
    box-shadow: 0 0 8px rgba(245, 158, 11, 0.4);
}
</style>
