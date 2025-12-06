@props(['level', 'size' => 'md', 'showName' => true])

@php
$levels = [
    '1.0' => ['name' => 'Mới Bắt Đầu', 'class' => 'level-beginner'],
    '2.0' => ['name' => 'Nghiệp Dư', 'class' => 'level-novice'],
    '3.0' => ['name' => 'Trung Bình', 'class' => 'level-intermediate'],
    '3.5' => ['name' => 'Khá', 'class' => 'level-upper-intermediate'],
    '4.0' => ['name' => 'Nâng Cao', 'class' => 'level-advanced'],
    '4.5' => ['name' => 'Chuyên Nghiệp', 'class' => 'level-pro'],
    '5.0+' => ['name' => 'Tinh Anh', 'class' => 'level-elite'],
];
$info = $levels[$level] ?? $levels['1.0'];
@endphp

<span class="opr-level-badge {{ $info['class'] }} size-{{ $size }}">
    <span class="level-number">{{ $level }}</span>
    @if($showName)
        <span class="level-name">{{ $info['name'] }}</span>
    @endif
</span>

<style>
.opr-level-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 9999px;
    font-weight: 500;
    border: 1px solid;
}

.opr-level-badge.size-sm {
    padding: 0.125rem 0.5rem;
    font-size: 0.75rem;
}

.opr-level-badge.size-md {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

.opr-level-badge.size-lg {
    padding: 0.5rem 1rem;
    font-size: 1rem;
}

.opr-level-badge .level-number {
    font-weight: 700;
}

.opr-level-badge .level-name {
    margin-left: 0.25rem;
}

/* Level Colors */
.level-beginner {
    background-color: #f3f4f6;
    color: #374151;
    border-color: #d1d5db;
}

.level-novice {
    background-color: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.level-intermediate {
    background-color: #dbeafe;
    color: #1d4ed8;
    border-color: #93c5fd;
}

.level-upper-intermediate {
    background-color: #e0e7ff;
    color: #4338ca;
    border-color: #a5b4fc;
}

.level-advanced {
    background-color: #f3e8ff;
    color: #7c3aed;
    border-color: #c4b5fd;
}

.level-pro {
    background-color: #ffedd5;
    color: #c2410c;
    border-color: #fdba74;
}

.level-elite {
    background-color: #fee2e2;
    color: #dc2626;
    border-color: #fca5a5;
}
</style>
